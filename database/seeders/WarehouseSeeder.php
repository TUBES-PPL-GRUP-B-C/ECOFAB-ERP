<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'GDG-UTM-01',
                'name' => 'Gudang Bahan Baku Utama Cirebon',
                'address' => 'Kawasan Industri Rotan Jl. Raya Plered No. 45, Cirebon',
                'capacity' => 1500.00,
                'status' => 'active',
            ],
            [
                'code' => 'GDG-PROD-02',
                'name' => 'Gudang Penyangga Lini Produksi A',
                'address' => 'Fasilitas Pabrik Unit II Lt. 1, Cirebon',
                'capacity' => 500.00,
                'status' => 'active',
            ],
            [
                'code' => 'GDG-TRN-03',
                'name' => 'Gudang Transit dan Logistik Masuk',
                'address' => 'Dermaga Logistik Barat No. 12',
                'capacity' => 800.00,
                'status' => 'active',
            ],
        ];

        foreach ($warehouses as $wh) {
            Warehouse::updateOrCreate(['code' => $wh['code']], $wh);
        }
    }
}
