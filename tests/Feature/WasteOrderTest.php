<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteListing;
use App\Models\WasteOrder;
use App\Models\WasteRecord;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class WasteOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_can_open_public_order_form_from_a_published_listing(): void
    {
        $listing = $this->listing();
        $url = route('public.bursa.order.create', ['listing' => $listing->id]);
        $this->get(route('public.bursa.show', $listing))->assertOk()->assertSee($url);
        $this->get($url)->assertOk()
            ->assertSee('action="'.route('public.bursa.order.store').'"', false)
            ->assertSee('name="buyer_name"', false)
            ->assertSee('name="buyer_contact"', false)
            ->assertSee('name="requested_weight"', false)
            ->assertSee($listing->category)
            ->assertDontSee('id="sidebar"', false);
        $this->assertGuest();
        $this->assertDatabaseCount('waste_orders', 0);
    }

    public function test_guest_order_is_pending_uses_server_price_and_does_not_reserve_stock(): void
    {
        $listing = $this->listing();
        $sourceBefore = $listing->wasteRecord->getRawOriginal();
        $listingBefore = $listing->fresh()->getRawOriginal();
        $forgedActor = User::factory()->create();
        $this->post(route('public.bursa.order.store'), array_replace($this->payload($listing), [
            'price_per_kg' => '0.01', 'status' => 'approved', 'approved_by' => $forgedActor->id,
            'approved_at' => now()->toDateTimeString(), 'decision_reason' => 'forged',
            'available_weight' => 0,
        ]))->assertRedirect(route('public.bursa.order.success'))
            ->assertSessionHasNoErrors()->assertSessionHas('order_received', true);
        $order = WasteOrder::sole();
        $this->assertSame('pending', $order->status);
        $this->assertSame('1250.50', $order->price_per_kg);
        $this->assertSame('2.125', $order->requested_weight);
        $this->assertNull($order->approved_by);
        $this->assertNull($order->approved_at);
        $this->assertNull($order->decision_reason);
        $this->assertSame($listingBefore, $listing->fresh()->getRawOriginal());
        $this->assertSame($sourceBefore, $listing->wasteRecord->fresh()->getRawOriginal());
        $this->assertGuest();
    }

    public function test_public_receipt_is_generic_single_use_and_does_not_expose_order_data(): void
    {
        $listing = $this->listing();
        $this->get(route('public.bursa.order.success'))->assertRedirect(route('public.bursa.index'));
        $response = $this->post(route('public.bursa.order.store'), $this->payload($listing))
            ->assertRedirect(route('public.bursa.order.success'));
        $this->assertSame(route('public.bursa.order.success'), $response->headers->get('Location'));
        $order = WasteOrder::sole();
        $this->get(route('public.bursa.order.success'))->assertOk()
            ->assertDontSee($order->buyer_name)->assertDontSee($order->buyer_contact)
            ->assertDontSee(url("/bursa/order/{$order->id}"), false)
            ->assertDontSee('name="order_id"', false);
        $this->get(route('public.bursa.order.success'))->assertRedirect(route('public.bursa.index'));
        $this->get(route('public.bursa.show', $listing))->assertOk()
            ->assertDontSee($order->buyer_name)->assertDontSee($order->buyer_contact);
    }

    public function test_public_order_rejects_missing_or_invalid_listing_identifiers(): void
    {
        $listing = $this->listing();
        foreach ([null, 'invalid', -1, '1.5'] as $id) {
            $query = $id === null ? [] : ['listing' => $id];
            $this->getJson(route('public.bursa.order.create', $query))
                ->assertUnprocessable()->assertJsonValidationErrors('listing');
            $this->postJson(route('public.bursa.order.store'), [
                ...$this->payload($listing), 'waste_listing_id' => $id,
            ])->assertUnprocessable()->assertJsonValidationErrors('waste_listing_id');
        }
        $this->get(route('public.bursa.order.create', ['listing' => 999]))->assertNotFound();
        $this->post(route('public.bursa.order.store'), [
            ...$this->payload($listing), 'waste_listing_id' => 999,
        ])->assertNotFound();
        $this->assertDatabaseCount('waste_orders', 0);
    }

    public function test_public_order_rechecks_listing_publication_stock_and_source(): void
    {
        foreach (['draft', 'withdrawn', 'empty', 'sold', 'unknown_source', 'missing_source'] as $condition) {
            $listing = $this->listing();
            if (in_array($condition, ['draft', 'withdrawn'], true)) {
                $listing->forceFill(['status' => $condition])->save();
            } elseif ($condition === 'empty') {
                $listing->update(['available_weight' => 0]);
            } elseif ($condition === 'missing_source') {
                $listing->wasteRecord->delete();
            } else {
                $listing->wasteRecord->update(['status' => $condition === 'sold' ? 'sold' : 'unknown']);
            }
            $this->get(route('public.bursa.order.create', ['listing' => $listing->id]))->assertNotFound();
            $this->post(route('public.bursa.order.store'), $this->payload($listing))->assertNotFound();
        }
        $this->assertDatabaseCount('waste_orders', 0);
    }

    public function test_order_fields_are_required_and_have_safe_length_and_precision_limits(): void
    {
        $listing = $this->listing();
        $this->postJson(route('public.bursa.order.store'), ['waste_listing_id' => $listing->id])
            ->assertUnprocessable()->assertJsonValidationErrors(['buyer_name', 'buyer_contact', 'requested_weight']);
        foreach ([
            ['buyer_name', str_repeat('a', 101)], ['buyer_contact', str_repeat('a', 101)],
            ['buyer_name', ['invalid']], ['buyer_contact', ['invalid']],
            ['requested_weight', 0], ['requested_weight', -1], ['requested_weight', '1.2345'],
            ['requested_weight', '5.126'], ['requested_weight', 'not-a-number'],
        ] as [$field, $value]) {
            $this->postJson(route('public.bursa.order.store'), array_replace($this->payload($listing), [$field => $value]))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertDatabaseCount('waste_orders', 0);
        $this->assertSame('5.125', $listing->fresh()->available_weight);
    }

    public function test_order_validation_keeps_old_values_for_public_form(): void
    {
        $listing = $this->listing();
        $form = route('public.bursa.order.create', ['listing' => $listing->id]);
        $this->from($form)->post(route('public.bursa.order.store'), array_replace($this->payload($listing), [
            'requested_weight' => '9.000',
        ]))->assertRedirect($form)->assertSessionHasErrors('requested_weight');
        $this->get($form)->assertOk()->assertSee('value="Pembeli pengujian"', false)
            ->assertSee('value="buyer@example.test"', false);
    }

    public function test_array_input_validation_can_redirect_back_to_public_and_internal_forms_safely(): void
    {
        $listing = $this->listing();
        $form = route('public.bursa.order.create', ['listing' => $listing->id]);
        foreach (['buyer_name', 'buyer_contact', 'requested_weight'] as $field) {
            $this->from($form)->post(route('public.bursa.order.store'), array_replace($this->payload($listing), [
                $field => ['invalid'],
            ]))->assertRedirect($form)->assertSessionHasErrors($field);
            $this->get($form)->assertOk();
        }
        $this->assertDatabaseCount('waste_orders', 0);
        $order = $this->order($listing);
        $edit = route('bursa.order.edit', $order);
        $this->actingAs($this->user('penjualan'));
        foreach (['buyer_name', 'buyer_contact', 'requested_weight'] as $field) {
            $this->from($edit)->put(route('bursa.order.update', $order), array_replace($this->payload($listing), [
                $field => ['invalid'],
            ]))->assertRedirect($edit)->assertSessionHasErrors($field);
            $this->get($edit)->assertOk();
        }
        $this->assertSame('5.125', $listing->fresh()->available_weight);
        $this->assertSame('Pembeli pengujian', $order->fresh()->buyer_name);
    }

    public function test_array_decision_reason_redirects_to_detail_without_breaking_the_form(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing);
        $detail = route('bursa.order.show', $order);
        $this->actingAs($this->user('admin'))->from($detail)->post($this->decisionUrl($order), [
            'action' => 'approve', 'reason' => ['invalid'],
        ])->assertRedirect($detail)->assertSessionHasErrors('reason');
        $this->get($detail)->assertOk();
        $this->assertDatabaseHas('waste_orders', ['id' => $order->id, 'status' => 'pending', 'approved_by' => null]);
        $this->assertSame('5.125', $listing->fresh()->available_weight);
    }

    public function test_legacy_listed_source_is_orderable_and_smallest_weight_is_supported(): void
    {
        $listing = $this->listing();
        $listing->wasteRecord->update(['status' => 'listed']);
        $this->post(route('public.bursa.order.store'), array_replace($this->payload($listing), [
            'requested_weight' => '0.001',
        ]))->assertRedirect(route('public.bursa.order.success'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('waste_orders', ['requested_weight' => '0.001', 'status' => 'pending']);
        $this->assertSame('5.125', $listing->fresh()->available_weight);
    }

    public function test_approval_decrements_only_marketplace_balance_and_records_actor_once(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing);
        $admin = $this->user('admin');
        $sourceBefore = $listing->wasteRecord->getRawOriginal();
        $listing->update(['price_per_kg' => '1900.00']);
        $this->freezeTime();
        $this->actingAs($admin)->post($this->decisionUrl($order), [
            'action' => 'approve', 'reason' => 'Sudah diverifikasi',
        ])->assertRedirect();
        $order->refresh();
        $this->assertSame('approved', $order->status);
        $this->assertSame($admin->id, $order->approved_by);
        $this->assertSame(now()->toDateTimeString(), $order->approved_at->toDateTimeString());
        $this->assertSame('Sudah diverifikasi', $order->decision_reason);
        $this->assertSame('1250.50', $order->price_per_kg);
        $this->assertSame('3.000', $listing->fresh()->available_weight);
        $this->assertSame($sourceBefore, $listing->wasteRecord->fresh()->getRawOriginal());
        $approvedBefore = $order->getRawOriginal();
        foreach (['approve', 'reject'] as $action) {
            $this->post($this->decisionUrl($order), compact('action'))->assertConflict();
        }
        $this->assertSame($approvedBefore, $order->fresh()->getRawOriginal());
        $this->assertSame('3.000', $listing->fresh()->available_weight);
    }

    public function test_approving_exact_remaining_balance_never_makes_it_negative(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing, ['requested_weight' => '5.125']);
        $this->actingAs($this->user('admin'))->post($this->decisionUrl($order), ['action' => 'approve'])
            ->assertRedirect();
        $this->assertSame('0.000', $listing->fresh()->available_weight);
        $this->assertSame('5.125', $listing->wasteRecord->fresh()->weight_kg);
        $this->post($this->decisionUrl($order), ['action' => 'approve'])->assertConflict();
        $this->get(route('public.bursa.order.create', ['listing' => $listing->id]))->assertNotFound();
    }

    public function test_two_pending_orders_do_not_reserve_stock_and_second_approval_rechecks_balance(): void
    {
        $listing = $this->listing();
        foreach (['Pembeli pertama', 'Pembeli kedua'] as $buyer) {
            $this->post(route('public.bursa.order.store'), array_replace($this->payload($listing), [
                'buyer_name' => $buyer, 'requested_weight' => '4.000',
            ]))->assertRedirect(route('public.bursa.order.success'))->assertSessionHasNoErrors();
        }
        [$first, $second] = WasteOrder::orderBy('id')->get()->all();
        $this->assertSame('5.125', $listing->fresh()->available_weight);
        $secondBefore = $second->getRawOriginal();
        $this->actingAs($this->user('admin'))->post($this->decisionUrl($first), ['action' => 'approve'])
            ->assertRedirect();
        $this->post($this->decisionUrl($second), ['action' => 'approve'])->assertConflict();
        $this->assertSame('1.125', $listing->fresh()->available_weight);
        $this->assertSame($secondBefore, $second->fresh()->getRawOriginal());
        $this->assertSame('5.125', $listing->wasteRecord->fresh()->weight_kg);
    }

    public function test_stale_listing_or_source_blocks_approval_without_mutating_pending_order(): void
    {
        $this->actingAs($this->user('admin'));
        foreach (['withdrawn', 'sold', 'missing'] as $condition) {
            $listing = $this->listing();
            $order = $this->order($listing);
            if ($condition === 'withdrawn') {
                $listing->forceFill(['status' => 'withdrawn'])->save();
            } elseif ($condition === 'sold') {
                $listing->wasteRecord->update(['status' => 'sold']);
            } else {
                $listing->wasteRecord->delete();
            }
            $before = $order->getRawOriginal();
            $this->post($this->decisionUrl($order), ['action' => 'approve'])->assertConflict();
            $this->assertSame($before, $order->fresh()->getRawOriginal());
            $this->assertSame('5.125', $listing->fresh()->available_weight);
        }
    }

    public function test_database_failure_after_decrement_rolls_back_stock_and_decision_together(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing);
        $before = $order->getRawOriginal();
        $eventName = 'eloquent.updating: '.WasteOrder::class;
        $decrementWasVisible = false;
        Event::listen($eventName, function (WasteOrder $updating) use ($listing, &$decrementWasVisible): void {
            if ($updating->status === 'approved') {
                $decrementWasVisible = $listing->fresh()->available_weight === '3.000';
                throw new RuntimeException('Simulasi gagal menyimpan keputusan');
            }
        });
        $this->withoutExceptionHandling();
        try {
            try {
                $this->actingAs($this->user('admin'))->post($this->decisionUrl($order), ['action' => 'approve']);
                $this->fail('Expected decision persistence to fail after the stock decrement.');
            } catch (RuntimeException $exception) {
                $this->assertSame('Simulasi gagal menyimpan keputusan', $exception->getMessage());
            }
        } finally {
            Event::forget($eventName);
        }
        $this->assertTrue($decrementWasVisible, 'Failure must occur after the stock decrement inside the transaction.');
        $this->assertSame('5.125', $listing->fresh()->available_weight);
        $this->assertSame($before, $order->fresh()->getRawOriginal());
    }

    public function test_rejection_preserves_balance_and_decision_history(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing);
        $admin = $this->user('admin');
        $this->actingAs($admin)->post($this->decisionUrl($order), [
            'action' => 'reject', 'reason' => 'Kontak belum dapat diverifikasi',
        ])->assertRedirect();
        $order->refresh();
        $this->assertSame('rejected', $order->status);
        $this->assertSame($admin->id, $order->approved_by);
        $this->assertNotNull($order->approved_at);
        $this->assertSame('Kontak belum dapat diverifikasi', $order->decision_reason);
        $this->assertSame('5.125', $listing->fresh()->available_weight);
        foreach (['approve', 'reject'] as $action) {
            $this->post($this->decisionUrl($order), compact('action'))->assertConflict();
        }
        $this->assertDatabaseHas('waste_orders', ['id' => $order->id, 'status' => 'rejected']);
    }

    public function test_decision_action_and_reason_are_validated_before_any_stock_change(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing);
        $this->actingAs($this->user('admin'));
        foreach ([[], ['action' => 'cancel'], ['action' => 'approve', 'reason' => str_repeat('x', 501)]] as $payload) {
            $this->postJson($this->decisionUrl($order), $payload)->assertUnprocessable();
        }
        $this->assertDatabaseHas('waste_orders', ['id' => $order->id, 'status' => 'pending', 'approved_by' => null]);
        $this->assertSame('5.125', $listing->fresh()->available_weight);
    }

    public function test_staff_can_update_pending_buyer_and_weight_but_cannot_forge_protected_fields(): void
    {
        $listing = $this->listing();
        $other = $this->listing();
        $order = $this->order($listing);
        $listing->update(['price_per_kg' => '1900.00']);
        $this->actingAs($this->user('penjualan'))->put("/bursa/order/{$order->id}", [
            'buyer_name' => 'Pembeli baru', 'buyer_contact' => 'new@example.test', 'requested_weight' => '3.125',
            'waste_listing_id' => $other->id, 'price_per_kg' => '0.01', 'status' => 'approved',
            'approved_by' => $this->user('admin')->id, 'approved_at' => now()->toDateTimeString(),
            'decision_reason' => 'forged', 'available_weight' => 0,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $order->refresh();
        $this->assertSame('Pembeli baru', $order->buyer_name);
        $this->assertSame('new@example.test', $order->buyer_contact);
        $this->assertSame('3.125', $order->requested_weight);
        $this->assertSame($listing->id, $order->waste_listing_id);
        $this->assertSame('1250.50', $order->price_per_kg);
        $this->assertSame('pending', $order->status);
        $this->assertNull($order->approved_by);
        $this->assertNull($order->approved_at);
        $this->assertNull($order->decision_reason);
        $this->assertSame('5.125', $listing->fresh()->available_weight);
        $this->assertSame('5.125', $other->fresh()->available_weight);
    }

    public function test_invalid_pending_update_does_not_mutate_order_or_balance(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing);
        $before = $order->getRawOriginal();
        $this->actingAs($this->user('penjualan'))->putJson("/bursa/order/{$order->id}", [
            'buyer_name' => 'Perubahan invalid', 'buyer_contact' => 'buyer@example.test', 'requested_weight' => '9.000',
        ])->assertUnprocessable()->assertJsonValidationErrors('requested_weight');
        $this->assertSame($before, $order->fresh()->getRawOriginal());
        $this->assertSame('5.125', $listing->fresh()->available_weight);
    }

    public function test_delete_cancels_pending_order_preserves_history_and_blocks_listing_deletion(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing);
        $this->actingAs($this->user('penjualan'))->delete("/bursa/order/{$order->id}")->assertRedirect();
        $this->assertDatabaseHas('waste_orders', [
            'id' => $order->id, 'status' => 'cancelled', 'buyer_contact' => 'buyer@example.test',
        ]);
        $this->assertSame('5.125', $listing->fresh()->available_weight);
        $this->delete("/bursa/order/{$order->id}")->assertConflict();
        $this->delete("/bursa/listing/{$listing->id}")->assertConflict();
        $this->assertDatabaseHas('waste_listings', ['id' => $listing->id]);
        $this->actingAs($this->user('admin'))->post($this->decisionUrl($order), ['action' => 'approve'])->assertConflict();
    }

    public function test_processed_orders_cannot_be_edited_updated_or_deleted(): void
    {
        $this->actingAs($this->user('admin'));
        foreach (['approved', 'rejected', 'cancelled'] as $status) {
            $listing = $this->listing();
            $order = $this->order($listing, ['status' => $status]);
            $before = $order->getRawOriginal();
            $this->get("/bursa/order/{$order->id}/edit")->assertConflict();
            $this->put("/bursa/order/{$order->id}", $this->payload($listing))->assertConflict();
            $this->delete("/bursa/order/{$order->id}")->assertConflict();
            $this->assertSame($before, $order->fresh()->getRawOriginal());
            $this->assertSame('5.125', $listing->fresh()->available_weight);
        }
    }

    public function test_approved_history_prevents_reallocation_of_sold_weight_across_listings(): void
    {
        $listing = $this->listing();
        $order = $this->order($listing, ['requested_weight' => '4.000']);
        $this->actingAs($this->user('admin'))->post($this->decisionUrl($order), ['action' => 'approve'])
            ->assertRedirect();
        $payload = [
            'waste_record_id' => $listing->waste_record_id, 'category' => 'Kayu alokasi baru',
            'price_per_kg' => '1250.50', 'available_weight' => '0.001',
        ];
        $this->post('/bursa/listing', $payload)->assertSessionHasErrors('available_weight');
        $this->put("/bursa/listing/{$listing->id}", array_replace($payload, ['available_weight' => '1.126']))
            ->assertSessionHasErrors('available_weight');
        $this->assertDatabaseCount('waste_listings', 1);
        $this->assertSame('1.125', $listing->fresh()->available_weight);
        $this->assertSame('5.125', $listing->wasteRecord->fresh()->weight_kg);
    }

    public function test_one_source_can_be_split_but_combined_listing_allocations_cannot_exceed_measured_weight(): void
    {
        $listing = $this->listing();
        $listing->update(['available_weight' => '3.000']);
        $this->actingAs($this->user('penjualan'))->post('/bursa/listing', [
            'waste_record_id' => $listing->waste_record_id, 'category' => 'Alokasi kedua',
            'price_per_kg' => '1250.50', 'available_weight' => '2.125',
        ])->assertRedirect('/bursa/listing')->assertSessionHasNoErrors();
        $second = WasteListing::where('id', '!=', $listing->id)->sole();
        $this->put("/bursa/listing/{$second->id}", [
            'waste_record_id' => $listing->waste_record_id, 'category' => 'Alokasi kedua',
            'price_per_kg' => '1250.50', 'available_weight' => '2.126',
        ])->assertSessionHasErrors('available_weight');
        $this->assertSame('2.125', $second->fresh()->available_weight);
        $this->assertSame('5.125', $listing->wasteRecord->fresh()->weight_kg);
    }

    public function test_legacy_overallocation_blocks_approval_before_any_stock_or_history_change(): void
    {
        $listing = $this->listing();
        // Historical rows may predate the aggregate source allocation guard.
        $other = WasteListing::create([
            'waste_record_id' => $listing->waste_record_id, 'category' => 'Legacy duplicate allocation',
            'price_per_kg' => '1250.50', 'available_weight' => '5.125',
        ]);
        $order = $this->order($listing);
        $before = $order->getRawOriginal();
        $this->actingAs($this->user('admin'))->postJson($this->decisionUrl($order), ['action' => 'approve'])
            ->assertUnprocessable()->assertJsonValidationErrors('available_weight');
        $this->assertSame($before, $order->fresh()->getRawOriginal());
        $this->assertSame('5.125', $listing->fresh()->available_weight);
        $this->assertSame('5.125', $other->fresh()->available_weight);
        $this->assertSame('5.125', $listing->wasteRecord->fresh()->weight_kg);
    }

    public function test_unknown_order_returns_not_found_without_mutating_stock(): void
    {
        $listing = $this->listing();
        $this->actingAs($this->user('admin'));
        $this->get('/bursa/order/999')->assertNotFound();
        $this->get('/bursa/order/999/edit')->assertNotFound();
        $this->put('/bursa/order/999', $this->payload($listing))->assertNotFound();
        $this->delete('/bursa/order/999')->assertNotFound();
        $this->post('/bursa/order/999/approve', ['action' => 'approve'])->assertNotFound();
        $this->assertSame('5.125', $listing->fresh()->available_weight);
        $this->assertDatabaseCount('waste_orders', 0);
    }

    private function listing(): WasteListing
    {
        $record = WasteRecord::create([
            'waste_type' => 'Sisa kayu', 'weight_kg' => '5.125', 'condition' => 'Bagus', 'status' => 'available',
        ]);
        $listing = WasteListing::create([
            'waste_record_id' => $record->id, 'category' => 'Kayu uji '.$record->id,
            'price_per_kg' => '1250.50', 'available_weight' => '5.125',
        ]);
        $listing->forceFill(['status' => 'published'])->save();

        return $listing;
    }

    private function payload(WasteListing $listing): array
    {
        return [
            'waste_listing_id' => $listing->id, 'buyer_name' => 'Pembeli pengujian',
            'buyer_contact' => 'buyer@example.test', 'requested_weight' => '2.125',
        ];
    }

    private function order(WasteListing $listing, array $overrides = []): WasteOrder
    {
        return WasteOrder::forceCreate(array_replace($this->payload($listing), [
            'price_per_kg' => $listing->price_per_kg, 'status' => 'pending',
        ], $overrides))->fresh();
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function decisionUrl(WasteOrder $order): string
    {
        return "/bursa/order/{$order->id}/approve";
    }
}
