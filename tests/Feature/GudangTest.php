<?php

namespace Tests\Feature;

use App\Models\EmissionFactor;
use App\Models\GoodsReceipt;
use App\Models\Material;
use App\Models\MaterialStock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GudangTest extends TestCase
{
    use RefreshDatabase;

    protected User $gudangUser;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);

        $this->gudangUser = User::factory()->create();
        $this->gudangUser->assignRole('gudang');
    }

    public function test_warehouses_table_schema_and_crud(): void
    {
        $this->assertTrue(Schema::hasTable('warehouses'));
        $this->assertTrue(Schema::hasColumns('warehouses', [
            'id', 'code', 'name', 'address', 'capacity', 'status', 'created_at', 'updated_at',
        ]));

        $response = $this->actingAs($this->gudangUser)->post(route('gudang.gudang.store'), [
            'code'     => 'WH-CRB-01',
            'name'     => 'Gudang Utama Cirebon',
            'address'  => 'Jl. Industri No. 12',
            'capacity' => 1000.00,
            'status'   => 'active',
        ]);

        $response->assertRedirect(route('gudang.gudang.index'));
        $this->assertDatabaseHas('warehouses', ['code' => 'WH-CRB-01', 'name' => 'Gudang Utama Cirebon']);

        $warehouse = Warehouse::where('code', 'WH-CRB-01')->first();
        $this->actingAs($this->gudangUser)->get(route('gudang.gudang.show', $warehouse->id))->assertOk();
    }

    public function test_materials_table_schema_and_low_stock_detection(): void
    {
        $this->assertTrue(Schema::hasTable('materials'));
        $this->assertTrue(Schema::hasColumns('materials', [
            'id', 'sku', 'name', 'category', 'unit', 'min_stock', 'unit_cost', 'emission_factor_id',
        ]));

        $material = Material::create([
            'sku'       => 'MAT-ROT-001',
            'name'      => 'Rotan Sega Anyaman',
            'category'  => 'Rotan',
            'unit'      => 'kg',
            'min_stock' => 50.00,
            'unit_cost' => 45000.00,
        ]);

        // Saldo awal 0 kg -> harus berstatus is_low_stock = true
        $this->assertTrue($material->is_low_stock);

        $warehouse = Warehouse::create([
            'code'   => 'WH-01',
            'name'   => 'Gudang 1',
            'status' => 'active',
        ]);

        $stockService = app(StockService::class);
        $stockService->addStock(
            warehouseId: $warehouse->id,
            materialId: $material->id,
            productId: null,
            quantity: 100.00,
            referenceType: 'INITIAL'
        );

        $material->refresh();
        $this->assertEquals(100.00, $material->total_stock);
        $this->assertFalse($material->is_low_stock);
    }

    public function test_stock_mutation_transfer_and_adjustment(): void
    {
        $wh1 = Warehouse::create(['code' => 'WH-A', 'name' => 'Gudang A', 'status' => 'active']);
        $wh2 = Warehouse::create(['code' => 'WH-B', 'name' => 'Gudang B', 'status' => 'active']);

        $material = Material::create([
            'sku'       => 'MAT-KAYU-01',
            'name'      => 'Kayu Jati FSC',
            'category'  => 'Kayu',
            'unit'      => 'kg',
            'min_stock' => 20.00,
            'unit_cost' => 120000.00,
        ]);

        $stockService = app(StockService::class);
        $stockService->addStock($wh1->id, $material->id, null, 80.00, 'INITIAL');

        // Transfer 30 kg dari WH-A ke WH-B
        $response = $this->actingAs($this->gudangUser)->post(route('gudang.mutasi.store'), [
            'type'              => 'transfer',
            'material_id'       => $material->id,
            'from_warehouse_id' => $wh1->id,
            'to_warehouse_id'   => $wh2->id,
            'qty'               => 30.00,
            'notes'             => 'Transfer kebutuhan lini 1',
        ]);

        $response->assertRedirect(route('gudang.mutasi.index'));

        $this->assertEquals(50.00, MaterialStock::where('material_id', $material->id)->where('warehouse_id', $wh1->id)->value('qty'));
        $this->assertEquals(30.00, MaterialStock::where('material_id', $material->id)->where('warehouse_id', $wh2->id)->value('qty'));
    }

    public function test_goods_receipt_automatically_increases_stock(): void
    {
        $wh = Warehouse::create(['code' => 'WH-IN', 'name' => 'Gudang Penerimaan', 'status' => 'active']);
        $material = Material::create([
            'sku'       => 'MAT-LEM-01',
            'name'      => 'Lem Waterbased',
            'category'  => 'Perekat',
            'unit'      => 'liter',
            'min_stock' => 10.00,
            'unit_cost' => 60000.00,
        ]);

        $response = $this->actingAs($this->gudangUser)->post(route('gudang.grn.store'), [
            'grn_number'    => 'GRN-TEST-999',
            'warehouse_id'  => $wh->id,
            'received_date' => date('Y-m-d'),
            'notes'         => 'Test pengiriman vendor',
            'items'         => [
                [
                    'material_id'  => $material->id,
                    'qty_received' => 25.00,
                    'notes'        => 'Kondisi baik',
                ],
            ],
        ]);

        $response->assertRedirect(route('gudang.grn.index'));
        $this->assertDatabaseHas('goods_receipts', ['grn_number' => 'GRN-TEST-999']);
        $this->assertDatabaseHas('material_stocks', [
            'material_id'  => $material->id,
            'warehouse_id' => $wh->id,
            'qty'          => 25.00,
        ]);
    }
}
