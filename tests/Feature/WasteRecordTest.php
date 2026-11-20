<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteListing;
use App\Models\WasteRecord;
use App\Models\WorkOrder;
use App\Services\WasteMarketService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WasteRecordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_every_waste_record_action_requires_authentication(): void
    {
        $record = $this->record();
        foreach ($this->actions($record) as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertRedirect('/login');
        }
        $this->assertDatabaseCount('waste_records', 1);
    }

    #[DataProvider('unrelatedRoles')]
    public function test_unrelated_roles_and_users_without_permissions_cannot_access_any_action(?string $role): void
    {
        $user = User::factory()->create();
        if ($role !== null) {
            $user->assignRole($role);
        }
        $this->actingAs($user);
        $record = $this->record();
        foreach ($this->actions($record) as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertForbidden();
        }
        $this->assertDatabaseCount('waste_records', 1);
    }

    public static function unrelatedRoles(): array
    {
        return ['no role' => [null], 'sales' => ['penjualan'], 'warehouse' => ['gudang'], 'purchasing' => ['pembelian'], 'human resources' => ['hr']];
    }

    public function test_viewer_can_read_records_but_cannot_create_edit_update_or_delete(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('produksi.view');
        $this->actingAs($viewer);
        $record = $this->record();
        $this->get('/produksi/limbah')->assertOk()->assertSee($record->waste_type);
        $this->get("/produksi/limbah/{$record->id}")->assertOk()->assertSee($record->notes)
            ->assertDontSee(route('produksi.limbah.edit', $record));
        foreach (array_slice($this->actions($record), 2) as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertForbidden();
        }
    }

    public function test_create_permission_allows_creation_but_does_not_grant_update_or_delete(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['produksi.view', 'produksi.create']);
        $this->actingAs($user);
        $this->get('/produksi/limbah/create')->assertOk();
        $this->post('/produksi/limbah', $this->payload())->assertRedirect()->assertSessionHasNoErrors();
        $record = WasteRecord::firstOrFail();
        $this->get("/produksi/limbah/{$record->id}/edit")->assertForbidden();
        $this->put("/produksi/limbah/{$record->id}", $this->payload())->assertForbidden();
        $this->delete("/produksi/limbah/{$record->id}")->assertForbidden();
        $this->assertDatabaseHas('waste_records', ['id' => $record->id, 'created_by' => $user->id]);
    }

    public function test_mutation_permissions_always_require_the_module_view_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['produksi.create', 'produksi.update', 'produksi.delete']);
        $this->actingAs($user);
        $record = $this->record();
        foreach ($this->actions($record) as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertForbidden();
        }
    }

    #[DataProvider('productionRoles')]
    public function test_authorized_roles_can_manage_manual_available_and_sold_records(string $role): void
    {
        $creator = $this->productionUser($role);
        $this->actingAs($creator);
        $this->get(route('produksi.limbah.create'))->assertOk()->assertViewIs('produksi.limbah.create')
            ->assertSee('value="Bagus"', false);
        $payload = $this->payload();
        unset($payload['status']);
        $this->post(route('produksi.limbah.store'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $record = WasteRecord::firstOrFail();
        $this->assertDatabaseHas('waste_records', [
            'id' => $record->id, 'status' => 'available', 'created_by' => $creator->id, 'work_order_id' => null,
        ]);
        $this->get(route('produksi.limbah.index'))->assertOk()->assertViewIs('produksi.limbah.index')->assertSee('Serbuk Kayu');
        $this->get(route('produksi.limbah.show', $record))->assertOk()->assertViewIs('produksi.limbah.show')
            ->assertSee('Catatan limbah produksi.');
        $this->get(route('produksi.limbah.edit', $record))->assertOk()->assertViewIs('produksi.limbah.edit');

        $editor = $this->productionUser($role);
        $this->actingAs($editor)->put(route('produksi.limbah.update', $record), $this->payload([
            'waste_type' => 'Potongan Rotan', 'weight_kg' => '3.125', 'condition' => 'Perlu sortir', 'status' => 'sold',
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('waste_records', [
            'id' => $record->id, 'waste_type' => 'Potongan Rotan', 'weight_kg' => 3.125,
            'condition' => 'Perlu sortir', 'status' => 'sold', 'created_by' => $creator->id,
        ]);
        // A manual sold record has no invented marketplace order history and remains manageable.
        $this->get(route('produksi.limbah.edit', $record))->assertOk();
        $this->delete(route('produksi.limbah.destroy', $record))->assertRedirect();
        $this->assertDatabaseMissing('waste_records', ['id' => $record->id]);
    }

    public static function productionRoles(): array
    {
        return ['administrator' => ['admin'], 'production' => ['produksi']];
    }

    public function test_completed_work_order_is_optional_and_forms_offer_only_completed_orders(): void
    {
        $this->actingAs($this->productionUser());
        $completed = $this->workOrder();
        $draft = $this->workOrder('draft');
        $this->get('/produksi/limbah/create')->assertOk()
            ->assertViewHas('workOrders', fn ($orders) => $orders->pluck('id')->all() === [$completed->id])
            ->assertSee($completed->wo_number)->assertDontSee($draft->wo_number);
        $this->post('/produksi/limbah', $this->payload(['work_order_id' => $completed->id, 'status' => 'sold']))
            ->assertRedirect()->assertSessionHasNoErrors();
        $record = WasteRecord::firstOrFail();
        $this->assertDatabaseHas('waste_records', ['id' => $record->id, 'work_order_id' => $completed->id, 'status' => 'sold']);
        $this->get("/produksi/limbah/{$record->id}")->assertOk()->assertSee($completed->wo_number);
        $this->put("/produksi/limbah/{$record->id}", $this->payload(['work_order_id' => null]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('waste_records', ['id' => $record->id, 'work_order_id' => null]);
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_create_and_update_payloads_are_rejected_without_mutation(array $changes, array $errors): void
    {
        $this->actingAs($this->productionUser());
        $record = $this->record();
        $before = $record->getRawOriginal();
        $this->post('/produksi/limbah', $this->payload($changes))->assertSessionHasErrors($errors);
        $this->put("/produksi/limbah/{$record->id}", $this->payload($changes))->assertSessionHasErrors($errors);
        $this->assertSame($before, $record->fresh()->getRawOriginal());
        $this->assertDatabaseCount('waste_records', 1);
    }

    public static function invalidPayloads(): array
    {
        return [
            'required fields' => [['waste_type' => null, 'weight_kg' => null, 'condition' => null], ['waste_type', 'weight_kg', 'condition']],
            'blank required text' => [['waste_type' => '   ', 'condition' => " \t "], ['waste_type', 'condition']],
            'text limits' => [['waste_type' => str_repeat('W', 101), 'condition' => str_repeat('C', 51), 'notes' => str_repeat('N', 5001)], ['waste_type', 'condition', 'notes']],
            'zero weight' => [['weight_kg' => 0], ['weight_kg']],
            'negative weight' => [['weight_kg' => -1], ['weight_kg']],
            'weight precision' => [['weight_kg' => '1.0001'], ['weight_kg']],
            'weight overflow' => [['weight_kg' => '1000000000.000'], ['weight_kg']],
            'weight not numeric' => [['weight_kg' => 'berat'], ['weight_kg']],
            'legacy listed cannot be assigned' => [['status' => 'listed'], ['status']],
            'unknown status' => [['status' => 'unknown'], ['status']],
            'explicit empty status' => [['status' => null], ['status']],
            'missing work order' => [['work_order_id' => 999999], ['work_order_id']],
            'work order must be an integer' => [['work_order_id' => '1.5'], ['work_order_id']],
            'work order must be positive' => [['work_order_id' => 0], ['work_order_id']],
        ];
    }

    #[DataProvider('incompleteStatuses')]
    public function test_manual_records_cannot_reference_an_uncompleted_work_order(string $status): void
    {
        $this->actingAs($this->productionUser());
        $order = $this->workOrder($status);
        $record = $this->record();
        $this->post('/produksi/limbah', $this->payload(['work_order_id' => $order->id]))->assertSessionHasErrors('work_order_id');
        $this->put("/produksi/limbah/{$record->id}", $this->payload(['work_order_id' => $order->id]))->assertSessionHasErrors('work_order_id');
        $this->assertDatabaseHas('waste_records', ['id' => $record->id, 'work_order_id' => null]);
        $this->assertDatabaseCount('waste_records', 1);
    }

    public static function incompleteStatuses(): array
    {
        return ['draft' => ['draft'], 'in progress' => ['in_progress'], 'cancelled' => ['cancelled']];
    }

    public function test_valid_field_boundaries_and_whitespace_are_supported(): void
    {
        $this->actingAs($this->productionUser());
        $this->post('/produksi/limbah', $this->payload([
            'waste_type' => '  '.str_repeat('W', 100).'  ', 'weight_kg' => '999999999.999',
            'condition' => str_repeat('C', 50), 'notes' => str_repeat('N', 5000),
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $record = WasteRecord::firstOrFail();
        $this->assertDatabaseHas('waste_records', ['id' => $record->id, 'waste_type' => str_repeat('W', 100), 'weight_kg' => '999999999.999']);
        $this->put("/produksi/limbah/{$record->id}", $this->payload([
            'waste_type' => '  Potongan kecil  ', 'weight_kg' => '0.001', 'work_order_id' => '', 'notes' => '   ',
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('waste_records', ['id' => $record->id, 'waste_type' => 'Potongan kecil', 'weight_kg' => '0.001', 'work_order_id' => null, 'notes' => null]);
    }

    public function test_creator_and_record_identity_cannot_be_forged(): void
    {
        $this->actingAs($this->productionUser());
        $record = $this->record();
        $before = $record->getRawOriginal();
        $payload = $this->payload(['created_by' => User::factory()->create()->id, 'id' => 500]);
        $this->post('/produksi/limbah', $payload)->assertSessionHasErrors(['created_by', 'id']);
        $this->put("/produksi/limbah/{$record->id}", $payload)->assertSessionHasErrors(['created_by', 'id']);
        $this->assertSame($before, $record->fresh()->getRawOriginal());
        $this->assertDatabaseCount('waste_records', 1);
    }

    public function test_invalid_array_inputs_redisplay_safely_and_text_is_escaped(): void
    {
        $this->actingAs($this->productionUser());
        $this->from('/produksi/limbah/create')->post('/produksi/limbah', $this->payload([
            'waste_type' => ['bad'], 'condition' => ['bad'], 'notes' => ['bad'], 'work_order_id' => ['bad'],
        ]))->assertRedirect('/produksi/limbah/create')->assertSessionHasErrors(['waste_type', 'condition', 'notes', 'work_order_id']);
        $this->get('/produksi/limbah/create')->assertOk()->assertViewHas('errors', fn ($errors) => $errors->has('waste_type'));
        $type = '<script>alert("type")</script>';
        $notes = '<img src=x onerror=alert("notes")>';
        $record = $this->record(['waste_type' => $type, 'notes' => $notes]);
        $this->get('/produksi/limbah')->assertOk()->assertSee($type)->assertDontSee($type, false);
        foreach (["/produksi/limbah/{$record->id}", "/produksi/limbah/{$record->id}/edit"] as $url) {
            $this->get($url)->assertOk()->assertSee($type)->assertSee($notes)
                ->assertDontSee($type, false)->assertDontSee($notes, false);
        }
    }

    public function test_search_filters_and_pagination_keep_the_query_and_support_zero_and_work_order_number(): void
    {
        $this->actingAs($this->productionUser());
        $this->get('/produksi/limbah')->assertOk()->assertViewHas('wasteRecords', fn ($rows) => $rows->total() === 0);
        foreach (range(1, 17) as $number) {
            $this->record(['waste_type' => 'Rotan '.chr(64 + $number)]);
        }
        $this->record(['waste_type' => 'Rotan terjual', 'status' => 'sold']);
        $this->record(['waste_type' => 'Kayu lain', 'status' => 'available']);
        $response = $this->get('/produksi/limbah?cari=Rotan&status=available');
        $response->assertOk()->assertViewHas('wasteRecords', fn ($rows) => $rows->total() === 17 && $rows->count() === 15 && $rows->perPage() === 15)
            ->assertDontSee('Rotan terjual')->assertDontSee('Kayu lain');
        $next = $response->viewData('wasteRecords')->nextPageUrl();
        parse_str(parse_url($next, PHP_URL_QUERY), $query);
        $this->assertSame('Rotan', $query['cari']);
        $this->assertSame('available', $query['status']);
        $this->get($next)->assertOk()->assertViewHas('wasteRecords', fn ($rows) => $rows->count() === 2);
        $order = $this->workOrder();
        $record = $this->record(['waste_type' => 'Kode 0', 'notes' => 'Catatan unik pencarian', 'work_order_id' => $order->id]);
        foreach (['0', 'Catatan unik pencarian', $order->wo_number] as $term) {
            $this->get('/produksi/limbah?'.http_build_query(['cari' => $term]))->assertOk()
                ->assertViewHas('wasteRecords', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $record->id);
        }
        $this->get('/produksi/limbah?cari=tidak-ditemukan')->assertOk()->assertViewHas('wasteRecords', fn ($rows) => $rows->total() === 0);
        $this->get('/produksi/limbah?cari[]=bad')->assertSessionHasErrors('cari');
        $this->get('/produksi/limbah?status=unknown')->assertSessionHasErrors('status');
    }

    public function test_legacy_listed_record_remains_readable_but_cannot_be_mutated_without_a_listing(): void
    {
        $this->actingAs($this->productionUser());
        $record = $this->record(['status' => 'listed']);
        $before = $record->getRawOriginal();
        $this->get('/produksi/limbah?status=listed')->assertOk()->assertSee($record->waste_type);
        $this->get("/produksi/limbah/{$record->id}")->assertOk()->assertDontSee(route('produksi.limbah.edit', $record));
        $this->get("/produksi/limbah/{$record->id}/edit")->assertStatus(409);
        $this->put("/produksi/limbah/{$record->id}", $this->payload())->assertStatus(409);
        $this->delete("/produksi/limbah/{$record->id}")->assertStatus(409);
        $this->assertSame($before, $record->fresh()->getRawOriginal());
    }

    public function test_any_existing_listing_protects_the_source_even_when_multiple_listings_exist(): void
    {
        $this->actingAs($this->productionUser());
        $record = $this->record();
        $listings = [];
        foreach (['Kayu pertama', 'Kayu kedua'] as $category) {
            $listings[] = WasteListing::create(['waste_record_id' => $record->id, 'category' => $category, 'price_per_kg' => 1000, 'available_weight' => 1]);
        }
        $before = $record->getRawOriginal();
        $this->get("/produksi/limbah/{$record->id}")->assertOk()->assertDontSee(route('produksi.limbah.edit', $record));
        $this->get("/produksi/limbah/{$record->id}/edit")->assertStatus(409);
        $this->put("/produksi/limbah/{$record->id}", $this->payload(['status' => 'sold']))->assertStatus(409);
        $this->delete("/produksi/limbah/{$record->id}")->assertStatus(409);
        $listings[0]->delete();
        $this->delete("/produksi/limbah/{$record->id}")->assertStatus(409);
        $this->assertSame($before, $record->fresh()->getRawOriginal());
        $this->assertDatabaseHas('waste_listings', ['id' => $listings[1]->id, 'waste_record_id' => $record->id]);
    }

    public function test_missing_records_return_not_found_for_all_record_actions(): void
    {
        $this->actingAs($this->productionUser());
        $this->get('/produksi/limbah/999999')->assertNotFound();
        $this->get('/produksi/limbah/999999/edit')->assertNotFound();
        $this->put('/produksi/limbah/999999', $this->payload())->assertNotFound();
        $this->delete('/produksi/limbah/999999')->assertNotFound();
    }

    public function test_completed_work_order_hook_records_real_rows_without_claiming_an_automatic_finish_workflow(): void
    {
        // Invoke the service hook explicitly: FR18 has no completed-order event trigger in this scope.
        $creator = User::factory()->create();
        foreach ([$creator->id, null] as $creatorId) {
            $order = $this->workOrder('completed', ['created_by' => $creatorId]);
            $before = $order->getRawOriginal();
            $this->assertTrue(app(WasteMarketService::class)->recordWasteFromWorkOrder($order->id, 'Sisa dari hook', 1.125));
            $this->assertDatabaseHas('waste_records', [
                'work_order_id' => $order->id, 'waste_type' => 'Sisa dari hook', 'weight_kg' => 1.125,
                'condition' => 'Bagus', 'status' => 'available', 'created_by' => $creatorId,
            ]);
            $this->assertSame($before, $order->fresh()->getRawOriginal());
        }
        $this->assertDatabaseCount('waste_records', 2);
        $this->assertDatabaseCount('waste_listings', 0);
    }

    public function test_completed_work_order_hook_rejects_invalid_fields_and_incomplete_or_missing_orders_without_inserting(): void
    {
        $cases = [];
        foreach (['draft', 'in_progress', 'cancelled'] as $status) {
            $cases[] = [$this->workOrder($status)->id, 'Sisa kayu', 1.0, null, 'work_order_id'];
        }
        $cases[] = [999999, 'Sisa kayu', 1.0, null, 'work_order_id'];
        $completed = $this->workOrder();
        $cases[] = [$completed->id, '   ', 1.0, null, 'waste_type'];
        $cases[] = [$completed->id, 'Sisa kayu', 0.0, null, 'weight_kg'];
        $cases[] = [$completed->id, 'Sisa kayu', -1.0, null, 'weight_kg'];
        $cases[] = [$completed->id, 'Sisa kayu', 1.0001, null, 'weight_kg'];
        $cases[] = [$completed->id, 'Sisa kayu', 1.0, str_repeat('C', 51), 'condition'];
        foreach ($cases as [$id, $type, $weight, $condition, $field]) {
            try {
                app(WasteMarketService::class)->recordWasteFromWorkOrder($id, $type, $weight, $condition);
                $this->fail('An invalid waste recording hook invocation must fail validation.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($field, $exception->errors());
            }
        }
        $this->assertDatabaseCount('waste_records', 0);
        $this->assertSame('completed', $completed->fresh()->status);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'work_order_id' => null, 'waste_type' => 'Serbuk Kayu', 'weight_kg' => '5.250',
            'condition' => 'Bagus', 'status' => 'available', 'notes' => 'Catatan limbah produksi.',
        ], $overrides);
    }

    private function record(array $overrides = []): WasteRecord
    {
        return WasteRecord::create($this->payload($overrides))->fresh();
    }

    private function workOrder(string $status = 'completed', array $overrides = []): WorkOrder
    {
        return WorkOrder::create(array_replace([
            'wo_number' => 'WO-FR22-'.(WorkOrder::count() + 1), 'product_name' => 'Meja Kayu',
            'target_quantity' => 10, 'status' => $status,
        ], $overrides))->fresh();
    }

    private function productionUser(string $role = 'produksi'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function actions(WasteRecord $record): array
    {
        $url = "/produksi/limbah/{$record->id}";

        return [
            ['get', '/produksi/limbah', []], ['get', $url, []], ['get', '/produksi/limbah/create', []],
            ['post', '/produksi/limbah', $this->payload()], ['get', $url.'/edit', []],
            ['put', $url, $this->payload()], ['delete', $url, []],
        ];
    }
}
