<?php

namespace Tests\Feature;

use App\Contracts\WorkOrderReadinessChecker;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\PendingWorkOrderReadinessChecker;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_all_work_order_actions_require_authentication(): void
    {
        $workOrder = $this->workOrder();

        foreach ($this->actions($workOrder) as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertRedirect('/login');
        }

        $this->assertSame('draft', $workOrder->fresh()->status);
        $this->assertDatabaseCount('work_orders', 1);
    }

    #[DataProvider('unrelatedRoles')]
    public function test_users_without_production_view_cannot_access_any_action(?string $role): void
    {
        $user = User::factory()->create();
        if ($role !== null) {
            $user->assignRole($role);
        }
        $this->actingAs($user);
        $workOrder = $this->workOrder();

        foreach ($this->actions($workOrder) as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertForbidden();
        }

        $this->assertSame('draft', $workOrder->fresh()->status);
        $this->assertDatabaseCount('work_orders', 1);
    }

    public static function unrelatedRoles(): array
    {
        return [
            'no role' => [null],
            'human resources' => ['hr'],
            'purchasing' => ['pembelian'],
            'warehouse' => ['gudang'],
            'sales' => ['penjualan'],
        ];
    }

    public function test_viewer_can_read_but_cannot_mutate_work_orders(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('produksi.view');
        $this->actingAs($viewer);
        $workOrder = $this->workOrder();

        $this->get('/produksi/wo')->assertOk()->assertSee($workOrder->wo_number);
        $this->get("/produksi/wo/{$workOrder->id}")->assertOk()->assertSee($workOrder->product_name);

        foreach (array_slice($this->actions($workOrder), 2) as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertForbidden();
        }

        // Updating permission does not grant the dedicated start action.
        $viewer->givePermissionTo('produksi.update');
        $this->post("/produksi/wo/{$workOrder->id}/mulai")->assertForbidden();
        $this->assertSame('draft', $workOrder->fresh()->status);
    }

    public function test_mutation_permissions_still_require_the_module_view_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['produksi.create', 'produksi.update', 'produksi.delete', 'wo.start']);
        $this->actingAs($user);
        $workOrder = $this->workOrder();

        foreach ($this->actions($workOrder) as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertForbidden();
        }
    }

    #[DataProvider('authorizedRoles')]
    public function test_authorized_roles_can_render_create_read_update_and_delete_drafts(string $role): void
    {
        $user = $this->userWithRole($role);
        $this->actingAs($user);

        $this->get(route('produksi.wo.create'))->assertOk()->assertViewIs('produksi.wo.create');
        $this->post(route('produksi.wo.store'), $this->payload())
            ->assertRedirect()->assertSessionHasNoErrors();

        $workOrder = WorkOrder::where('wo_number', 'WO-TEST-001')->firstOrFail();
        $this->assertSame('draft', $workOrder->status);
        $this->assertSame($user->id, $workOrder->created_by);
        $this->assertNull($workOrder->actual_quantity);
        $this->assertNull($workOrder->actual_emission);
        $this->assertNull($workOrder->completed_at);
        $this->assertSame('12.50', $workOrder->target_quantity);
        $this->assertSame('2026-11-20', $workOrder->start_date->toDateString());
        $this->assertSame($user->id, $workOrder->creator->id);

        $this->get(route('produksi.wo.index'))->assertOk()->assertViewIs('produksi.wo.index')
            ->assertSee('WO-TEST-001')->assertSee('Meja Daur Ulang');
        $this->get(route('produksi.wo.show', $workOrder))->assertOk()->assertViewIs('produksi.wo.show')
            ->assertSee('Meja Daur Ulang')->assertSee('Catatan produksi awal.');
        $this->get(route('produksi.wo.edit', $workOrder))->assertOk()->assertViewIs('produksi.wo.edit')
            ->assertSee('value="WO-TEST-001"', false)
            ->assertSee('value="2026-11-20"', false);

        $this->put(route('produksi.wo.update', $workOrder), $this->payload([
            'product_name' => 'Meja Revisi',
            'target_quantity' => '15.25',
            'notes' => 'Revisi jadwal produksi.',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'product_name' => 'Meja Revisi',
            'target_quantity' => 15.25,
            'notes' => 'Revisi jadwal produksi.',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        $this->delete(route('produksi.wo.destroy', $workOrder))->assertRedirect();
        $this->assertDatabaseMissing('work_orders', ['id' => $workOrder->id]);
    }

    public static function authorizedRoles(): array
    {
        return ['administrator' => ['admin'], 'production staff' => ['produksi']];
    }

    public function test_list_search_status_filter_and_pagination_preserve_the_query(): void
    {
        $this->actingAs($this->userWithRole());
        foreach (range(1, 17) as $number) {
            $this->workOrder(['wo_number' => sprintf('WO-FILTER-%02d', $number), 'product_name' => 'Kursi Rotan']);
        }
        $this->workOrder(['wo_number' => 'WO-OTHER', 'product_name' => 'Meja Kayu']);
        $this->workOrder(['wo_number' => 'WO-ACTIVE', 'product_name' => 'Kursi Rotan', 'status' => 'in_progress']);

        $response = $this->get('/produksi/wo?cari=Rotan&status=draft');
        $response->assertOk()->assertViewHas('workOrders', function ($paginator) {
            return $paginator->total() === 17
                && $paginator->perPage() === 15
                && $paginator->count() === 15
                && $paginator->every(fn (WorkOrder $order) => $order->status === 'draft');
        })->assertDontSee('WO-OTHER')->assertDontSee('WO-ACTIVE');

        $nextPage = $response->viewData('workOrders')->nextPageUrl();
        parse_str(parse_url($nextPage, PHP_URL_QUERY), $query);
        $this->assertSame('Rotan', $query['cari']);
        $this->assertSame('draft', $query['status']);
        $this->assertSame('2', $query['page']);
        $this->get($nextPage)->assertOk()->assertViewHas('workOrders', fn ($orders) => $orders->count() === 2);

        $this->get('/produksi/wo?cari=WO-ACTIVE&status=in_progress')->assertOk()
            ->assertSee('WO-ACTIVE')->assertViewHas('workOrders', fn ($orders) => $orders->total() === 1);
    }

    public function test_empty_index_search_results_and_invalid_query_filters_are_handled(): void
    {
        $this->actingAs($this->userWithRole());
        $this->get('/produksi/wo')->assertOk()
            ->assertViewHas('workOrders', fn ($orders) => $orders->total() === 0);
        $this->workOrder(['wo_number' => 'WO-SEARCH-EXISTING']);
        $this->get('/produksi/wo?cari=missing-record')->assertOk()
            ->assertDontSee('WO-SEARCH-EXISTING')
            ->assertViewHas('workOrders', fn ($orders) => $orders->total() === 0);
        $this->from('/produksi/wo')->get('/produksi/wo?cari[]=bad')
            ->assertRedirect('/produksi/wo')->assertSessionHasErrors('cari');
        $this->from('/produksi/wo')->get('/produksi/wo?status=unknown')
            ->assertRedirect('/produksi/wo')->assertSessionHasErrors('status');
        $this->assertDatabaseCount('work_orders', 1);
    }

    public function test_zero_is_a_real_search_term(): void
    {
        $this->actingAs($this->userWithRole());
        $this->workOrder(['wo_number' => 'WO-ZERO-0', 'product_name' => 'Kursi']);
        $this->workOrder(['wo_number' => 'WO-ALPHA', 'product_name' => 'Meja']);

        $this->get('/produksi/wo?cari=0')->assertOk()->assertSee('WO-ZERO-0')
            ->assertDontSee('WO-ALPHA')->assertViewHas('workOrders', fn ($orders) => $orders->total() === 1);
    }

    public function test_invalid_array_inputs_can_be_redisplayed_safely_with_validation_errors(): void
    {
        $this->actingAs($this->userWithRole());
        $this->from('/produksi/wo/create')->post('/produksi/wo', $this->payload([
            'product_name' => ['unexpected' => 'value'],
            'notes' => ['unexpected' => 'value'],
        ]))->assertRedirect('/produksi/wo/create')->assertSessionHasErrors(['product_name', 'notes']);

        $this->get('/produksi/wo/create')->assertOk()->assertViewHas(
            'errors',
            fn ($errors) => $errors->has('product_name') && $errors->has('notes')
        );
        $this->assertDatabaseCount('work_orders', 0);
    }

    public function test_unique_number_allows_self_update_and_rejects_collisions_on_store_and_update(): void
    {
        $this->actingAs($this->userWithRole());
        $first = $this->workOrder(['wo_number' => 'WO-FIRST']);
        $second = $this->workOrder(['wo_number' => 'WO-SECOND']);

        $this->post('/produksi/wo', $this->payload(['wo_number' => $first->wo_number]))
            ->assertSessionHasErrors('wo_number');
        $this->put("/produksi/wo/{$second->id}", $this->payload(['wo_number' => $first->wo_number]))
            ->assertSessionHasErrors('wo_number');
        $this->assertSame('WO-SECOND', $second->fresh()->wo_number);

        $this->put("/produksi/wo/{$first->id}", $this->payload(['wo_number' => $first->wo_number]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('WO-FIRST', $first->fresh()->wo_number);
        $this->assertDatabaseCount('work_orders', 2);
    }

    #[DataProvider('invalidPayloads')]
    public function test_store_and_update_reject_invalid_payloads_without_mutation(array $changes, array $errors): void
    {
        $this->actingAs($this->userWithRole());
        $workOrder = $this->workOrder(['wo_number' => 'WO-EXISTING']);
        $before = $workOrder->getRawOriginal();

        $this->from('/produksi/wo/create')->post('/produksi/wo', $this->payload($changes))
            ->assertRedirect('/produksi/wo/create')->assertSessionHasErrors($errors);
        $this->put("/produksi/wo/{$workOrder->id}", $this->payload($changes))
            ->assertSessionHasErrors($errors);

        $this->assertSame($before, $workOrder->fresh()->getRawOriginal());
        $this->assertDatabaseCount('work_orders', 1);
    }

    public static function invalidPayloads(): array
    {
        return [
            'required fields' => [['wo_number' => null, 'product_name' => null, 'target_quantity' => null], ['wo_number', 'product_name', 'target_quantity']],
            'whitespace required strings' => [['wo_number' => '   ', 'product_name' => " \t "], ['wo_number', 'product_name']],
            'number too long' => [['wo_number' => str_repeat('W', 51)], ['wo_number']],
            'product too long' => [['product_name' => str_repeat('P', 256)], ['product_name']],
            'notes too long' => [['notes' => str_repeat('N', 5001)], ['notes']],
            'zero quantity' => [['target_quantity' => 0], ['target_quantity']],
            'negative quantity' => [['target_quantity' => -1], ['target_quantity']],
            'nonnumeric quantity' => [['target_quantity' => 'dua'], ['target_quantity']],
            'fraction too precise' => [['target_quantity' => '1.001'], ['target_quantity']],
            'quantity overflow' => [['target_quantity' => '10000000000.00'], ['target_quantity']],
            'invalid calendar start' => [['start_date' => '2026-02-30'], ['start_date']],
            'non ISO start' => [['start_date' => '20/11/2026'], ['start_date']],
            'datetime is not a date' => [['due_date' => '2026-11-21 12:00:00'], ['due_date']],
            'invalid due date' => [['due_date' => 'tomorrow'], ['due_date']],
            'due precedes start' => [['start_date' => '2026-11-22', 'due_date' => '2026-11-21'], ['due_date']],
        ];
    }

    public function test_valid_boundaries_and_optional_dates_are_supported_and_input_is_trimmed(): void
    {
        $this->actingAs($this->userWithRole());
        $this->post('/produksi/wo', $this->payload([
            'wo_number' => '  '.str_repeat('W', 50).'  ',
            'product_name' => str_repeat('P', 255),
            'target_quantity' => '9999999999.99',
            'start_date' => '2026-11-20',
            'due_date' => '2026-11-20',
            'notes' => str_repeat('N', 5000),
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $workOrder = WorkOrder::where('wo_number', str_repeat('W', 50))->firstOrFail();
        $this->assertSame('9999999999.99', $workOrder->target_quantity);
        $this->assertSame(5000, strlen($workOrder->notes));

        $this->put("/produksi/wo/{$workOrder->id}", $this->payload([
            'wo_number' => $workOrder->wo_number,
            'product_name' => '  Produk Dirapikan  ',
            'target_quantity' => '0.01',
            'start_date' => '',
            'due_date' => '2026-11-01',
            'notes' => '   ',
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $workOrder->refresh();
        $this->assertSame('Produk Dirapikan', $workOrder->product_name);
        $this->assertSame('0.01', $workOrder->target_quantity);
        $this->assertNull($workOrder->start_date);
        $this->assertSame('2026-11-01', $workOrder->due_date->toDateString());
        $this->assertNull($workOrder->notes);

        $this->post('/produksi/wo', $this->payload([
            'wo_number' => 'WO-NO-DATES', 'target_quantity' => '2', 'start_date' => null, 'due_date' => null, 'notes' => null,
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('work_orders', ['wo_number' => 'WO-NO-DATES', 'start_date' => null, 'due_date' => null]);
    }

    public function test_http_crud_rejects_protected_lifecycle_and_linkage_fields(): void
    {
        $this->actingAs($this->userWithRole());
        $workOrder = $this->workOrder();
        $before = $workOrder->getRawOriginal();
        $protected = [
            'status' => 'completed',
            'created_by' => User::factory()->create()->id,
            'actual_quantity' => 100,
            'actual_emission' => 0,
            'completed_at' => '2026-11-20 12:00:00',
            'product_id' => 123,
            'bom_id' => 456,
        ];

        $this->post('/produksi/wo', $this->payload($protected))->assertSessionHasErrors(array_keys($protected));
        $this->put("/produksi/wo/{$workOrder->id}", $this->payload($protected))
            ->assertSessionHasErrors(array_keys($protected));
        $this->assertSame($before, $workOrder->fresh()->getRawOriginal());
        $this->assertDatabaseCount('work_orders', 1);
    }

    public function test_work_order_text_is_escaped_on_list_detail_and_edit_pages(): void
    {
        $this->actingAs($this->userWithRole());
        $product = '<script>alert("product")</script>';
        $notes = '<img src=x onerror=alert("notes")>';
        $workOrder = $this->workOrder(['product_name' => $product, 'notes' => $notes]);

        $this->get('/produksi/wo')->assertOk()->assertSee($product)->assertDontSee($product, false);
        foreach (["/produksi/wo/{$workOrder->id}", "/produksi/wo/{$workOrder->id}/edit"] as $url) {
            $this->get($url)->assertOk()->assertSee($product)->assertSee($notes)
                ->assertDontSee($product, false)->assertDontSee($notes, false);
        }
    }

    #[DataProvider('nonDraftStatuses')]
    public function test_non_drafts_can_be_read_but_cannot_be_edited_deleted_or_started(string $status): void
    {
        $this->actingAs($this->userWithRole());
        $workOrder = $this->workOrder([
            'status' => $status,
            'actual_quantity' => '8.50',
            'actual_emission' => '4.1234',
            'completed_at' => $status === 'completed' ? '2026-11-20 13:00:00' : null,
        ]);
        $before = $workOrder->getRawOriginal();

        $this->get("/produksi/wo/{$workOrder->id}")->assertOk()->assertSee($workOrder->wo_number);
        $this->get("/produksi/wo/{$workOrder->id}/edit")->assertStatus(409);
        $this->put("/produksi/wo/{$workOrder->id}", $this->payload())->assertStatus(409);
        $this->delete("/produksi/wo/{$workOrder->id}")->assertStatus(409);
        $this->post("/produksi/wo/{$workOrder->id}/mulai")->assertStatus(409);

        $this->assertSame($before, $workOrder->fresh()->getRawOriginal());
        $this->assertSame('8.50', $workOrder->fresh()->actual_quantity);
        $this->assertSame('4.1234', $workOrder->fresh()->actual_emission);
    }

    public static function nonDraftStatuses(): array
    {
        return ['in progress' => ['in_progress'], 'completed' => ['completed'], 'cancelled' => ['cancelled']];
    }

    #[DataProvider('referenceRelations')]
    public function test_draft_deletion_preserves_existing_material_or_waste_references(string $relation, array $attributes): void
    {
        $this->actingAs($this->userWithRole());
        $workOrder = $this->workOrder();
        $reference = $workOrder->{$relation}()->create($attributes);

        $this->delete("/produksi/wo/{$workOrder->id}")->assertStatus(409);
        $this->assertDatabaseHas('work_orders', ['id' => $workOrder->id, 'status' => 'draft']);
        $this->assertDatabaseHas($reference->getTable(), ['id' => $reference->id, 'work_order_id' => $workOrder->id]);
    }

    public static function referenceRelations(): array
    {
        return [
            'material allocation' => ['materials', ['material_name' => 'Kayu', 'required_quantity' => 20, 'unit' => 'kg']],
            'waste record' => ['wasteRecords', ['waste_type' => 'Serbuk Kayu', 'weight_kg' => '1.250']],
        ];
    }

    public function test_missing_work_orders_return_not_found_for_every_record_action(): void
    {
        $this->actingAs($this->userWithRole());
        $this->get('/produksi/wo/999999')->assertNotFound();
        $this->get('/produksi/wo/999999/edit')->assertNotFound();
        $this->put('/produksi/wo/999999', $this->payload())->assertNotFound();
        $this->delete('/produksi/wo/999999')->assertNotFound();
        $this->post('/produksi/wo/999999/mulai')->assertNotFound();
    }

    public function test_default_readiness_checker_blocks_start_until_real_bom_and_stock_checks_exist(): void
    {
        $this->actingAs($this->userWithRole());
        $this->assertInstanceOf(PendingWorkOrderReadinessChecker::class, app(WorkOrderReadinessChecker::class));
        $workOrder = $this->workOrder();
        $before = $workOrder->getRawOriginal();

        $this->from("/produksi/wo/{$workOrder->id}")->post(route('produksi.wo.start', $workOrder))
            ->assertRedirect("/produksi/wo/{$workOrder->id}")->assertSessionHasErrors('readiness');
        $this->get("/produksi/wo/{$workOrder->id}")->assertOk()
            ->assertSee('Integrasi resep Green BoM dan stok bahan belum tersedia');

        $this->assertSame($before, $workOrder->fresh()->getRawOriginal());
        $this->assertDatabaseCount('work_order_materials', 0);
        $this->assertDatabaseCount('waste_records', 0);
    }

    public function test_injected_rejecting_readiness_checker_leaves_the_work_order_unchanged(): void
    {
        $this->actingAs($this->userWithRole());
        $workOrder = $this->workOrder();
        $before = $workOrder->getRawOriginal();
        $checker = new class () implements WorkOrderReadinessChecker {
            public ?int $checkedId = null;

            public function assertCanStart(WorkOrder $workOrder): void
            {
                $this->checkedId = $workOrder->id;
                throw ValidationException::withMessages(['readiness' => 'Material belum tersedia.']);
            }
        };
        $this->app->instance(WorkOrderReadinessChecker::class, $checker);

        $this->post(route('produksi.wo.start', $workOrder))->assertSessionHasErrors('readiness');
        $this->assertSame($workOrder->id, $checker->checkedId);
        $this->assertSame($before, $workOrder->fresh()->getRawOriginal());
    }

    public function test_injected_accepting_checker_proves_only_the_start_transition_contract_and_prevents_repeated_start(): void
    {
        // This fake proves lifecycle orchestration only; it does not verify real BoM or stock integration.
        $user = User::factory()->create();
        $user->givePermissionTo(['produksi.view', 'wo.start']);
        $this->actingAs($user);
        $workOrder = $this->workOrder();
        $checker = new class () implements WorkOrderReadinessChecker {
            public int $calls = 0;

            public function assertCanStart(WorkOrder $workOrder): void
            {
                $this->calls++;
                if (! $workOrder->isDraft()) {
                    throw new \LogicException('The readiness checker must receive a draft.');
                }
            }
        };
        $this->app->instance(WorkOrderReadinessChecker::class, $checker);

        $this->post(route('produksi.wo.start', $workOrder))->assertRedirect()->assertSessionHasNoErrors();
        $workOrder->refresh();
        $this->assertSame('in_progress', $workOrder->status);
        $this->assertNull($workOrder->actual_quantity);
        $this->assertNull($workOrder->actual_emission);
        $this->assertNull($workOrder->completed_at);
        $this->assertSame('2026-11-20', $workOrder->start_date->toDateString());
        $started = $workOrder->getRawOriginal();

        $this->post(route('produksi.wo.start', $workOrder))->assertStatus(409);
        $this->assertSame(1, $checker->calls);
        $this->assertSame($started, $workOrder->fresh()->getRawOriginal());
        $this->assertDatabaseCount('work_order_materials', 0);
    }

    public function test_cancel_endpoint_is_not_exposed_in_this_scope(): void
    {
        $this->assertTrue(Route::has('produksi.wo.finish'));
        $this->assertFalse(Route::has('produksi.wo.cancel'));
        $this->actingAs($this->userWithRole());
        $workOrder = $this->workOrder();
        $this->post("/produksi/wo/{$workOrder->id}/batal")->assertNotFound();
        $this->assertSame('draft', $workOrder->fresh()->status);
    }

    private function actions(WorkOrder $workOrder): array
    {
        $url = "/produksi/wo/{$workOrder->id}";

        return [
            ['get', '/produksi/wo', []],
            ['get', $url, []],
            ['get', '/produksi/wo/create', []],
            ['post', '/produksi/wo', $this->payload()],
            ['get', $url.'/edit', []],
            ['put', $url, $this->payload()],
            ['delete', $url, []],
            ['post', $url.'/mulai', []],
        ];
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'wo_number' => 'WO-TEST-001',
            'product_name' => 'Meja Daur Ulang',
            'target_quantity' => '12.50',
            'start_date' => '2026-11-20',
            'due_date' => '2026-11-25',
            'notes' => 'Catatan produksi awal.',
        ], $overrides);
    }

    private function workOrder(array $overrides = []): WorkOrder
    {
        return WorkOrder::create(array_replace($this->payload(), ['status' => 'draft'], $overrides))->fresh();
    }

    private function userWithRole(string $role = 'produksi'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
