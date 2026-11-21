<?php

namespace Tests\Unit;

use App\Models\Material;
use App\Models\MaterialStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;
    protected Warehouse $warehouseA;
    protected Warehouse $warehouseB;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();

        $this->warehouseA = Warehouse::create([
            'code' => 'WH-A',
            'name' => 'Gudang Utama A',
            'capacity' => 1000,
        ]);

        $this->warehouseB = Warehouse::create([
            'code' => 'WH-B',
            'name' => 'Gudang Produksi B',
            'capacity' => 500,
        ]);

        $this->material = Material::create([
            'sku' => 'MAT-ROTAN-01',
            'name' => 'Rotan Sega Anyaman',
            'category' => 'Rotan',
            'unit' => 'kg',
            'min_stock' => 50,
            'unit_cost' => 45000,
        ]);
    }

    public function test_can_add_stock_and_record_movement(): void
    {
        $movement = $this->stockService->addStock(
            materialId: $this->material->id,
            warehouseId: $this->warehouseA->id,
            qty: 150.0,
            referenceType: 'GRN',
            referenceId: 1
        );

        $this->assertInstanceOf(StockMovement::class, $movement);
        $this->assertEquals(150.0, MaterialStock::where('material_id', $this->material->id)->where('warehouse_id', $this->warehouseA->id)->value('qty'));
        $this->assertDatabaseHas('stock_movements', [
            'material_id' => $this->material->id,
            'to_warehouse_id' => $this->warehouseA->id,
            'type' => 'in',
            'qty' => 150.0,
            'reference_type' => 'GRN',
        ]);
    }

    public function test_can_transfer_stock_between_warehouses(): void
    {
        // Beri stok awal di WH-A
        $this->stockService->addStock($this->material->id, $this->warehouseA->id, 100.0);

        // Transfer 40 kg ke WH-B
        $this->stockService->transferStock(
            materialId: $this->material->id,
            fromWarehouseId: $this->warehouseA->id,
            toWarehouseId: $this->warehouseB->id,
            qty: 40.0
        );

        $this->assertEquals(60.0, MaterialStock::where('material_id', $this->material->id)->where('warehouse_id', $this->warehouseA->id)->value('qty'));
        $this->assertEquals(40.0, MaterialStock::where('material_id', $this->material->id)->where('warehouse_id', $this->warehouseB->id)->value('qty'));
    }

    public function test_cannot_reduce_stock_below_zero(): void
    {
        $this->stockService->addStock($this->material->id, $this->warehouseA->id, 20.0);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Stok tidak mencukupi/i');

        // Mencoba mengurangi 50 kg padahal saldo hanya 20 kg (NFR-04: stok tidak boleh negatif)
        $this->stockService->reduceStock($this->material->id, $this->warehouseA->id, 50.0);
    }

    public function test_can_adjust_stock_and_record_opname(): void
    {
        $this->stockService->addStock($this->material->id, $this->warehouseA->id, 80.0);

        // Penyesuaian fisik ternyata ada 95 kg (selisih +15 kg)
        $this->stockService->adjustStock(
            materialId: $this->material->id,
            warehouseId: $this->warehouseA->id,
            actualQty: 95.0,
            notes: 'Stock opname fisik bulanan'
        );

        $this->assertEquals(95.0, MaterialStock::where('material_id', $this->material->id)->where('warehouse_id', $this->warehouseA->id)->value('qty'));
        $this->assertDatabaseHas('stock_movements', [
            'material_id' => $this->material->id,
            'type' => 'adjustment',
            'qty' => 15.0,
        ]);
    }
}
