<?php

namespace Tests\Unit;

use App\Services\CarbonCalculatorService;
use PHPUnit\Framework\TestCase;

class CarbonCalculatorServiceTest extends TestCase
{
    public function test_calculate_bom_emissions_calculates_materials_and_machines_correctly(): void
    {
        $service = new CarbonCalculatorService();

        // 10 kg rotan (faktor 0.5 kg CO2/kg, harga 15.000)
        // 2 liter lem (faktor 1.2 kg CO2/liter, harga 50.000)
        $materials = [
            ['quantity' => 10, 'factor' => 0.5, 'cost' => 15000],
            ['quantity' => 2, 'factor' => 1.2, 'cost' => 50000],
        ];

        // Mesin 1.5 jam, daya 2.0 kWh, faktor listrik 0.8 kg CO2/kWh, tarif 20.000/jam
        $machines = [
            ['hours' => 1.5, 'power_kwh' => 2.0, 'factor' => 0.8, 'cost' => 20000],
        ];

        // Rumus emisi bahan: (10 * 0.5) + (2 * 1.2) = 5.0 + 2.4 = 7.4
        // Rumus emisi mesin: (1.5 * 2.0 * 0.8) = 2.4
        // Total emisi: 7.4 + 2.4 = 9.8 kg CO2e
        // Biaya bahan: (10 * 15000) + (2 * 50000) = 150.000 + 100.000 = 250.000
        // Biaya mesin: 1.5 * 20000 = 30.000
        // Total biaya: 280.000

        $result = $service->calculateBomEmissions($materials, $machines, 1.0);

        $this->assertEquals(9.8, $result['total_emission']);
        $this->assertEquals(9.8, $result['emission_per_unit']);
        $this->assertEquals(280000.0, $result['total_cost']);
        $this->assertEquals(280000.0, $result['cost_per_unit']);
    }

    public function test_calculate_bom_emissions_with_multiple_output_units(): void
    {
        $service = new CarbonCalculatorService();

        $materials = [
            ['quantity' => 20, 'factor' => 1.0, 'cost' => 10000],
        ];
        $machines = [];

        // Total emisi 20 kg untuk 4 unit output -> 5.0 kg / unit
        $result = $service->calculateBomEmissions($materials, $machines, 4.0);

        $this->assertEquals(20.0, $result['total_emission']);
        $this->assertEquals(5.0, $result['emission_per_unit']);
        $this->assertEquals(200000.0, $result['total_cost']);
        $this->assertEquals(50000.0, $result['cost_per_unit']);
    }
}
