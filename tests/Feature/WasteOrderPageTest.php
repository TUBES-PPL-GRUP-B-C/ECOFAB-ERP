<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteListing;
use App\Models\WasteOrder;
use App\Models\WasteRecord;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasteOrderPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_cannot_access_internal_orders_or_decisions(): void
    {
        $order = $this->orderFixture();
        foreach (['/bursa/order', "/bursa/order/{$order->id}", "/bursa/order/{$order->id}/edit"] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        $this->put("/bursa/order/{$order->id}", [])->assertRedirect(route('login'));
        $this->delete("/bursa/order/{$order->id}")->assertRedirect(route('login'));
        $this->post("/bursa/order/{$order->id}/approve", ['action' => 'approve'])
            ->assertRedirect(route('login'));
        $this->assertDatabaseHas('waste_orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_internal_order_pages_respect_existing_role_permissions(): void
    {
        $order = $this->orderFixture();
        foreach (['admin', 'penjualan', 'hr', 'pembelian', 'gudang', 'produksi', null] as $role) {
            $user = User::factory()->create();
            if ($role !== null) {
                $user->assignRole($role);
            }
            $this->actingAs($user);
            foreach (['/bursa/order', "/bursa/order/{$order->id}"] as $url) {
                $response = $this->get($url);
                if (in_array($role, ['admin', 'penjualan'], true)) {
                    $response->assertOk()->assertSee($order->buyer_name);
                } else {
                    $response->assertForbidden()->assertDontSee($order->buyer_contact);
                }
            }
        }
    }

    public function test_index_uses_shared_layout_and_renders_an_actual_empty_list(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('bursa.view');
        $response = $this->actingAs($viewer)->get(route('bursa.order.index'));
        $response->assertOk()->assertViewIs('bursa.order.index')
            ->assertSee('id="sidebar"', false)
            ->assertDontSee('Daftar pesanan belum tersedia')
            ->assertDontSee('Detail pesanan belum tersedia');
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $this->assertSame(1, (new \DOMXPath($document))->query('//main')->length);
    }

    public function test_view_update_delete_and_approval_permissions_are_independent(): void
    {
        $order = $this->orderFixture();
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('bursa.view');
        $this->actingAs($viewer)->get("/bursa/order/{$order->id}")->assertOk()
            ->assertDontSee('action="'.url("/bursa/order/{$order->id}/approve").'"', false);
        $this->get("/bursa/order/{$order->id}/edit")->assertForbidden();
        $this->put("/bursa/order/{$order->id}", [])->assertForbidden();
        $this->delete("/bursa/order/{$order->id}")->assertForbidden();
        $this->post("/bursa/order/{$order->id}/approve", ['action' => 'approve'])->assertForbidden();

        $approver = User::factory()->create();
        $approver->givePermissionTo('order-limbah.approve');
        $this->actingAs($approver)->get('/bursa/order')->assertForbidden();
        $this->post("/bursa/order/{$order->id}/approve", ['action' => 'approve'])->assertForbidden();
        $approver->givePermissionTo('bursa.view');
        $this->get("/bursa/order/{$order->id}")->assertOk()
            ->assertSee('action="'.url("/bursa/order/{$order->id}/approve").'"', false);
        $this->post("/bursa/order/{$order->id}/approve", ['action' => 'reject'])->assertRedirect();
        $this->assertDatabaseHas('waste_orders', ['id' => $order->id, 'status' => 'rejected']);
    }

    public function test_sales_staff_can_manage_pending_orders_but_cannot_approve_or_reject(): void
    {
        $order = $this->orderFixture();
        $user = User::factory()->create();
        $user->assignRole('penjualan');
        $this->actingAs($user)->get("/bursa/order/{$order->id}/edit")->assertOk();
        foreach (['approve', 'reject'] as $action) {
            $this->post("/bursa/order/{$order->id}/approve", compact('action'))->assertForbidden();
        }
        $this->assertDatabaseHas('waste_orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('waste_listings', ['id' => $order->waste_listing_id, 'available_weight' => 5]);
    }

    private function orderFixture(): WasteOrder
    {
        $record = WasteRecord::create([
            'waste_type' => 'Sisa kayu', 'weight_kg' => 5, 'condition' => 'Bagus', 'status' => 'available',
        ]);
        $listing = WasteListing::create([
            'waste_record_id' => $record->id, 'category' => 'Kayu', 'price_per_kg' => 1000, 'available_weight' => 5,
        ]);
        $listing->forceFill(['status' => 'published'])->save();

        return WasteOrder::forceCreate([
            'waste_listing_id' => $listing->id, 'buyer_name' => 'Pembeli uji', 'buyer_contact' => 'buyer@example.test',
            'requested_weight' => 2, 'price_per_kg' => 1000, 'status' => 'pending',
        ]);
    }
}
