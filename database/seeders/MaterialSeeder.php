<?php

namespace Database\Seeders;

use App\Models\EmissionFactor;
use App\Models\Material;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $stockService = app(StockService::class);

        $efRotan = EmissionFactor::where('name', 'like', '%Rotan%')->first();
        $efKayu = EmissionFactor::where('name', 'like', '%Kayu Jati%')->first();
        $efLem = EmissionFactor::where('name', 'like', '%Lem%')->first();
        $efPaku = EmissionFactor::where('name', 'like', '%Paku%')->first();
        $efBambu = EmissionFactor::where('name', 'like', '%Bambu%')->first();

        $mainWarehouse = Warehouse::where('code', 'GDG-UTM-01')->first();

        $materials = [
            [
                'sku' => 'MAT-ROT-001',
                'name' => 'Rotan Sega Anyaman Terbarukan (Kadar air <12%)',
                'category' => 'Rotan',
                'unit' => 'kg',
                'min_stock' => 100.000,
                'unit_cost' => 50000.00,
                'emission_factor_id' => $efRotan?->id,
                'initial_stock' => 350.000,
            ],
            [
                'sku' => 'MAT-KAY-002',
                'name' => 'Kayu Jati Solid Bersertifikat Sirkular FSC',
                'category' => 'Kayu',
                'unit' => 'kg',
                'min_stock' => 80.000,
                'unit_cost' => 120000.00,
                'emission_factor_id' => $efKayu?->id,
                'initial_stock' => 120.000,
            ],
            [
                'sku' => 'MAT-LEM-003',
                'name' => 'Lem Ramah Lingkungan Waterbased (Bio-Polymer)',
                'category' => 'Perekat',
                'unit' => 'liter',
                'min_stock' => 25.000,
                'unit_cost' => 80000.00,
                'emission_factor_id' => $efLem?->id,
                // Stok sengaja diset di bawah minimum (8 liter) untuk pengujian alert stok kritis (FR-10 & FR-02)
                'initial_stock' => 8.000,
            ],
            [
                'sku' => 'MAT-PAKU-004',
                'name' => 'Paku Baja 4cm (Standar Anti Karat)',
                'category' => 'Aksesoris & Logam',
                'unit' => 'kg',
                'min_stock' => 50.000,
                'unit_cost' => 25000.00,
                'emission_factor_id' => $efPaku?->id,
                // Stok sengaja diset kritis (12 kg) sesuai skenario mockup Dashboard
                'initial_stock' => 12.000,
            ],
            [
                'sku' => 'MAT-BAM-005',
                'name' => 'Bambu Laminasi Pilihan Ramah Lingkungan',
                'category' => 'Bambu',
                'unit' => 'kg',
                'min_stock' => 40.000,
                'unit_cost' => 35000.00,
                'emission_factor_id' => $efBambu?->id,
                'initial_stock' => 85.000,
            ],
        ];

        foreach ($materials as $item) {
            $initialQty = $item['initial_stock'];
            unset($item['initial_stock']);

            $material = Material::updateOrCreate(['sku' => $item['sku']], $item);

            if ($mainWarehouse && $initialQty > 0) {
                // Gunakan StockService untuk inisialisasi saldo & jejak audit awal
                if ($material->stocks()->where('warehouse_id', $mainWarehouse->id)->doesntExist()) {
                    $stockService->addStock(
                        materialId: $material->id,
                        warehouseId: $mainWarehouse->id,
                        qty: $initialQty,
                        referenceType: 'INITIAL_STOCK',
                        notes: 'Inisialisasi stok awal sistem EcoFab'
                    );
                }
            }
        }
    }
}
