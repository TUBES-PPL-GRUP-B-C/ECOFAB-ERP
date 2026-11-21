<?php

namespace Database\Seeders;

use App\Models\EmissionFactor;
use Illuminate\Database\Seeder;

class EmissionFactorSeeder extends Seeder
{
    public function run(): void
    {
        $factors = [
            [
                'name' => 'Rotan Alam Terbarukan',
                'type' => 'material',
                'unit' => 'kg',
                'value_kg_co2e' => 2.10000,
                'source' => 'IPCC Guideline & Ecoinvent 3.8',
                'year' => 2023,
            ],
            [
                'name' => 'Kayu Jati Bersertifikat FSC',
                'type' => 'material',
                'unit' => 'kg',
                'value_kg_co2e' => 0.45000,
                'source' => 'Forest Carbon Standard 2022',
                'year' => 2022,
            ],
            [
                'name' => 'Lem Berbasis Air Ramah Lingkungan (Waterbased)',
                'type' => 'material',
                'unit' => 'liter',
                'value_kg_co2e' => 1.25000,
                'source' => 'Green Chemical Life Cycle Assessment',
                'year' => 2023,
            ],
            [
                'name' => 'Paku Baja Standar Anti Karat',
                'type' => 'material',
                'unit' => 'kg',
                'value_kg_co2e' => 3.20000,
                'source' => 'World Steel Association LCA',
                'year' => 2022,
            ],
            [
                'name' => 'Bambu Laminasi',
                'type' => 'material',
                'unit' => 'kg',
                'value_kg_co2e' => 0.60000,
                'source' => 'INBAR Bamboo Emission Factor',
                'year' => 2023,
            ],
        ];

        foreach ($factors as $factor) {
            EmissionFactor::updateOrCreate(['name' => $factor['name']], $factor);
        }
    }
}
