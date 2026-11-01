<?php

namespace App\Services;

/**
 * Service untuk seluruh kalkulasi jejak karbon (kg CO₂e) dan biaya produksi.
 *
 * Mengacu pada SDG 12 & GHG Protocol. Satu-satunya tempat rumus emisi sistem:
 * Emisi = Σ(jumlah bahan × faktor emisi) + Σ(jam mesin × kWh × faktor listrik)
 *
 * Digunakan oleh GreenBoM, WorkOrder, dan EcoPassport.
 */
class CarbonCalculatorService
{
    /**
     * Hitung total emisi dan biaya untuk resep Green BoM per unit.
     *
     * @param array $materials Daftar bahan: [['quantity' => float, 'factor' => float, 'cost' => float]]
     * @param array $machines Daftar mesin: [['hours' => float, 'power_kwh' => float, 'factor' => float, 'cost' => float]]
     * @param float $outputQty Jumlah output unit (default 1)
     * @return array ['emission_per_unit' => float, 'cost_per_unit' => float, 'total_emission' => float, 'total_cost' => float]
     */
    public function calculateBomEmissions(array $materials, array $machines, float $outputQty = 1.0): array
    {
        $materialEmission = 0.0;
        $materialCost = 0.0;

        foreach ($materials as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $factor = (float) ($item['factor'] ?? 0);
            $unitCost = (float) ($item['cost'] ?? 0);

            $materialEmission += ($qty * $factor);
            $materialCost += ($qty * $unitCost);
        }

        $machineEmission = 0.0;
        $machineCost = 0.0;

        foreach ($machines as $mach) {
            $hours = (float) ($mach['hours'] ?? 0);
            $kwh = (float) ($mach['power_kwh'] ?? 0);
            $factor = (float) ($mach['factor'] ?? 0);
            $rate = (float) ($mach['cost'] ?? 0);

            $machineEmission += ($hours * $kwh * $factor);
            $machineCost += ($hours * $rate);
        }

        $totalEmission = $materialEmission + $machineEmission;
        $totalCost = $materialCost + $machineCost;
        $divisor = $outputQty > 0 ? $outputQty : 1.0;

        return [
            'emission_per_unit' => round($totalEmission / $divisor, 4),
            'cost_per_unit' => round($totalCost / $divisor, 2),
            'total_emission' => round($totalEmission, 4),
            'total_cost' => round($totalCost, 2),
        ];
    }

    /**
     * Hitung emisi aktual untuk Work Order yang selesai.
     *
     * @param array $actualMaterials
     * @param array $actualMachines
     * @param float $actualOutput
     * @return array
     */
    public function calculateWorkOrderActual(array $actualMaterials, array $actualMachines, float $actualOutput): array
    {
        return $this->calculateBomEmissions($actualMaterials, $actualMachines, $actualOutput);
    }
}
