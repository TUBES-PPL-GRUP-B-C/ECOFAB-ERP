<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);

        $this->customer = Customer::create(['code' => 'CUST-001', 'name' => 'PT Mebel Hijau']);
    }

    public function test_guest_and_unauthorized_users_cannot_access_sales_orders(): void
    {
        $this->get('/penjualan/so')->assertRedirect('/login');
        $this->post('/penjualan/so', [])->assertRedirect('/login');

        $gudang = User::factory()->create();
        $gudang->assignRole('gudang');
        $this->actingAs($gudang);
        $this->get('/penjualan/so')->assertForbidden();
        $this->get('/penjualan/so/create')->assertForbidden();
        $this->post('/penjualan/so', $this->payload())->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('penjualan.view');
        $this->actingAs($viewer);
        $this->get('/penjualan/so')->assertOk();
        $this->get('/penjualan/so/create')->assertForbidden();
        $this->post('/penjualan/so', $this->payload())->assertForbidden();
    }

    public function test_store_creates_header_and_multiple_items_with_server_side_totals(): void
    {
        $user = $this->salesUser();
        $this->actingAs($user);

        $this->get('/penjualan/so/create')->assertOk()->assertSee('Buat Sales Order');

        $payload = $this->payload([
            ['product_name' => 'Kursi Rotan', 'qty' => 2, 'unit_price' => 150000],
            ['product_name' => 'Meja Jati', 'qty' => 1.5, 'unit_price' => 1000000],
        ]);
        // Total manipulasi dari client harus diabaikan
        $payload['total_amount'] = 1;

        $response = $this->post('/penjualan/so', $payload);

        $so = SalesOrder::with('items')->firstOrFail();
        $response->assertRedirect("/penjualan/so/{$so->id}");

        $this->assertSame('1800000.00', $so->total_amount);
        $this->assertCount(2, $so->items);
        $this->assertSame($user->id, $so->created_by);
        $this->assertMatchesRegularExpression('/^SO-\d{8}-001$/', $so->so_number);
        $this->assertDatabaseHas('sales_order_items', [
            'sales_order_id' => $so->id,
            'product_name' => 'Kursi Rotan',
            'subtotal' => 300000,
        ]);
        $this->assertDatabaseHas('sales_order_items', [
            'sales_order_id' => $so->id,
            'product_name' => 'Meja Jati',
            'subtotal' => 1500000,
        ]);

        $this->get("/penjualan/so/{$so->id}")->assertOk()
            ->assertSee($so->so_number)
            ->assertSee('Kursi Rotan')
            ->assertSee('Rp 1.800.000');

        $this->get('/penjualan/so')->assertOk()->assertSee($so->so_number);
    }

    public function test_so_numbers_are_sequential(): void
    {
        $this->actingAs($this->salesUser());

        $this->post('/penjualan/so', $this->payload());
        $this->post('/penjualan/so', $this->payload());

        $numbers = SalesOrder::orderBy('id')->pluck('so_number')->all();
        $this->assertStringEndsWith('-001', $numbers[0]);
        $this->assertStringEndsWith('-002', $numbers[1]);
    }

    public function test_validation_rejects_invalid_payloads_without_saving(): void
    {
        $this->actingAs($this->salesUser());

        $this->from('/penjualan/so/create')
            ->post('/penjualan/so', [
                'customer_id' => 99999,
                'order_date' => 'bukan-tanggal',
                'status' => 'ngawur',
                'items' => [],
            ])
            ->assertRedirect('/penjualan/so/create')
            ->assertSessionHasErrors(['customer_id', 'order_date', 'status', 'items']);

        $this->post('/penjualan/so', $this->payload([
            ['product_name' => '', 'qty' => 0, 'unit_price' => -5],
        ]))->assertSessionHasErrors(['items.0.product_name', 'items.0.qty', 'items.0.unit_price']);

        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertDatabaseCount('sales_order_items', 0);
    }

    public function test_update_replaces_items_and_recalculates_total(): void
    {
        $this->actingAs($this->salesUser());
        $this->post('/penjualan/so', $this->payload());
        $so = SalesOrder::firstOrFail();

        $this->get("/penjualan/so/{$so->id}/edit")->assertOk()->assertSee('Ubah Sales Order');

        $this->put("/penjualan/so/{$so->id}", $this->payload([
            ['product_name' => 'Lemari Bambu', 'qty' => 3, 'unit_price' => 200000],
        ], 'confirmed'))->assertRedirect("/penjualan/so/{$so->id}");

        $so->refresh();
        $this->assertSame('confirmed', $so->status);
        $this->assertSame('600000.00', $so->total_amount);
        $this->assertCount(1, $so->items);
        $this->assertSame('Lemari Bambu', $so->items->first()->product_name);
    }

    public function test_cancelled_order_cannot_be_edited_and_confirmed_order_cannot_be_deleted(): void
    {
        $this->actingAs($this->salesUser());

        $this->post('/penjualan/so', $this->payload(null, 'confirmed'));
        $confirmed = SalesOrder::firstOrFail();

        $this->delete("/penjualan/so/{$confirmed->id}")
            ->assertRedirect('/penjualan/so')
            ->assertSessionHas('error');
        $this->assertDatabaseHas('sales_orders', ['id' => $confirmed->id]);

        $confirmed->update(['status' => 'cancelled']);

        $this->get("/penjualan/so/{$confirmed->id}/edit")->assertRedirect("/penjualan/so/{$confirmed->id}");
        $this->put("/penjualan/so/{$confirmed->id}", $this->payload())
            ->assertRedirect("/penjualan/so/{$confirmed->id}")
            ->assertSessionHas('error');
        $this->assertSame('cancelled', $confirmed->fresh()->status);
    }

    public function test_delete_draft_order_removes_items(): void
    {
        $this->actingAs($this->salesUser());
        $this->post('/penjualan/so', $this->payload());
        $so = SalesOrder::firstOrFail();

        $this->delete("/penjualan/so/{$so->id}")->assertRedirect('/penjualan/so');

        $this->assertDatabaseMissing('sales_orders', ['id' => $so->id]);
        $this->assertDatabaseCount('sales_order_items', 0);
    }

    public function test_index_search_and_filter(): void
    {
        $this->actingAs($this->salesUser());
        $other = Customer::create(['code' => 'CUST-002', 'name' => 'CV Kriya Bambu']);

        $this->post('/penjualan/so', $this->payload());
        $payload = $this->payload(null, 'confirmed');
        $payload['customer_id'] = $other->id;
        $this->post('/penjualan/so', $payload);

        [$first, $second] = SalesOrder::orderBy('id')->get()->all();

        $this->get('/penjualan/so?cari=Kriya')->assertOk()
            ->assertSee($second->so_number)
            ->assertDontSee($first->so_number);

        $this->get('/penjualan/so?status=draft')->assertOk()
            ->assertSee($first->so_number)
            ->assertDontSee($second->so_number);
    }

    public function test_missing_sales_order_returns_404(): void
    {
        $this->actingAs($this->salesUser());

        $this->get('/penjualan/so/99999')->assertNotFound();
    }

    public function test_penjualan_module_dashboard_shows_summary_stats(): void
    {
        $this->actingAs($this->salesUser());
        $this->post('/penjualan/so', $this->payload(null, 'confirmed'));
        $so = SalesOrder::firstOrFail();

        $this->get('/penjualan')->assertOk()
            ->assertSee('Total Customer')
            ->assertSee('Total Sales Order')
            ->assertSee('SO Dikonfirmasi')
            ->assertSee($so->so_number)
            ->assertSee('Rp 100.000');

        $gudang = User::factory()->create();
        $gudang->assignRole('gudang');
        $this->actingAs($gudang)->get('/penjualan')->assertForbidden();
    }

    public function test_view_only_user_cannot_edit_update_or_delete_sales_order(): void
    {
        $this->actingAs($this->salesUser());
        $this->post('/penjualan/so', $this->payload());
        $so = SalesOrder::firstOrFail();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('penjualan.view');
        $this->actingAs($viewer);

        $this->get("/penjualan/so/{$so->id}")->assertOk();
        $this->get("/penjualan/so/{$so->id}/edit")->assertForbidden();
        $this->put("/penjualan/so/{$so->id}", $this->payload())->assertForbidden();
        $this->delete("/penjualan/so/{$so->id}")->assertForbidden();
    }

    public function test_sales_order_stock_check_rejects_insufficient_stock_and_never_deducts_stock(): void
    {
        $this->actingAs($this->salesUser());

        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('qty', 15, 3)->default(0);
        });

        DB::table('product_stocks')->insert([
            'product_id' => 10,
            'warehouse_id' => 1,
            'qty' => 5.000,
        ]);

        $this->from('/penjualan/so/create')
            ->post('/penjualan/so', $this->payload([
                ['product_id' => 10, 'product_name' => 'Kursi Rotan', 'qty' => 3, 'unit_price' => 100000],
                ['product_id' => 10, 'product_name' => 'Kursi Rotan', 'qty' => 3, 'unit_price' => 100000],
            ]))
            ->assertRedirect('/penjualan/so/create')
            ->assertSessionHasErrors(['items.1.qty']);

        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertDatabaseCount('sales_order_items', 0);
        $this->assertSame(5.0, (float) DB::table('product_stocks')->where('product_id', 10)->sum('qty'));

        $response = $this->post('/penjualan/so', $this->payload([
            ['product_id' => 10, 'product_name' => 'Kursi Rotan', 'qty' => 4, 'unit_price' => 100000],
        ], 'confirmed'));

        $so = SalesOrder::firstOrFail();
        $response->assertRedirect("/penjualan/so/{$so->id}");
        $this->assertDatabaseCount('sales_orders', 1);
        $this->assertDatabaseCount('sales_order_items', 1);
        $this->assertSame(5.0, (float) DB::table('product_stocks')->where('product_id', 10)->sum('qty'));
    }

    public function test_sales_order_with_deliveries_cannot_be_edited_or_deleted(): void
    {
        $this->actingAs($this->salesUser());
        $this->post('/penjualan/so', $this->payload(null, 'draft'));
        $so = SalesOrder::firstOrFail();

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id');
        });

        DB::table('deliveries')->insert([
            'sales_order_id' => $so->id,
        ]);

        $this->get("/penjualan/so/{$so->id}/edit")
            ->assertRedirect("/penjualan/so/{$so->id}")
            ->assertSessionHas('error');

        $this->put("/penjualan/so/{$so->id}", $this->payload([
            ['product_name' => 'Produk Berubah', 'qty' => 2, 'unit_price' => 50000],
        ], 'confirmed'))
            ->assertRedirect("/penjualan/so/{$so->id}")
            ->assertSessionHas('error');

        $this->delete("/penjualan/so/{$so->id}")
            ->assertRedirect('/penjualan/so')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sales_orders', [
            'id' => $so->id,
            'status' => 'draft',
            'total_amount' => 100000.00,
        ]);
    }

    /**
     * @param array<int, array<string, mixed>>|null $items
     */
    private function payload(?array $items = null, string $status = 'draft'): array
    {
        return [
            'customer_id' => $this->customer->id,
            'order_date' => '2026-11-22',
            'status' => $status,
            'notes' => 'Pesanan uji',
            'items' => $items ?? [
                ['product_name' => 'Kursi Rotan', 'qty' => 1, 'unit_price' => 100000],
            ],
        ];
    }

    private function salesUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('penjualan');

        return $user;
    }
}
