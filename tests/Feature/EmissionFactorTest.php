<?php

namespace Tests\Feature;

use App\Models\EmissionFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmissionFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_emission_factor_preserves_value_unit_and_reference(): void
    {
        $factor = EmissionFactor::create([
            'name' => 'Electricity test factor',
            'type' => 'electricity',
            'unit' => 'kg CO2e/kWh',
            'value_kg_co2e' => '0.81234',
            'source' => 'Isolated test reference for electricity',
            'year' => 2026,
        ])->fresh();

        $this->assertSame('Electricity test factor', $factor->name);
        $this->assertSame('electricity', $factor->type);
        $this->assertSame('0.81234', $factor->value_kg_co2e);
        $this->assertSame('kg CO2e/kWh', $factor->unit);
        $this->assertSame('Isolated test reference for electricity', $factor->source);
        $this->assertSame(2026, $factor->year);
        $this->assertNotNull($factor->created_at);
        $this->assertNotNull($factor->updated_at);
    }

    public function test_zero_emission_factor_can_be_stored_without_consumer_records(): void
    {
        $factor = EmissionFactor::create([
            'name' => 'Material test factor',
            'type' => 'material',
            'unit' => 'kg CO2e/kg',
            'value_kg_co2e' => '0.00000',
            'source' => 'Isolated test reference for material',
            'year' => 2025,
        ])->fresh();

        $this->assertSame('0.00000', $factor->value_kg_co2e);
        $this->assertSame('kg CO2e/kg', $factor->unit);
        $this->assertSame(2025, $factor->year);
        $this->assertDatabaseCount('emission_factors', 1);
    }

    public function test_source_and_year_are_nullable_as_defined_in_the_erd(): void
    {
        $factor = EmissionFactor::create([
            'name' => 'Test factor awaiting reference',
            'type' => 'material',
            'unit' => 'kg CO2e/kg',
            'value_kg_co2e' => '0.12345',
        ])->fresh();

        $this->assertSame('0.12345', $factor->value_kg_co2e);
        $this->assertNull($factor->source);
        $this->assertNull($factor->year);
    }
}
