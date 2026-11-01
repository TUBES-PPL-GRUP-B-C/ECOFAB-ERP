<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FormatHelperTest extends TestCase
{
    public function test_format_angka_handles_thousands_and_decimals(): void
    {
        $this->assertEquals('12.480', format_angka(12480));
        $this->assertEquals('4,2', format_angka(4.2, 1));
        $this->assertEquals('0', format_angka(null));
        $this->assertEquals('1.000.000', format_angka(1000000));
    }

    public function test_format_rupiah_formats_currency(): void
    {
        $this->assertEquals('Rp 4.250.000', format_rupiah(4250000));
        $this->assertEquals('4.250.000', format_rupiah(4250000, false));
        $this->assertEquals('Rp 0', format_rupiah(null));
    }

    public function test_format_emisi_formats_carbon_footprint(): void
    {
        $this->assertEquals('12.480,0 kg CO₂e', format_emisi(12480, 1));
        $this->assertEquals('4,2 kg CO₂e', format_emisi(4.2, 1));
        $this->assertEquals('0 kg CO₂e', format_emisi(null));
    }

    public function test_format_tanggal_formats_date_in_indonesian(): void
    {
        $date = '2026-11-01 10:00:00';
        $formatted = format_tanggal($date, 'd M Y');

        $this->assertStringContainsString('Nov', $formatted);
        $this->assertStringContainsString('2026', $formatted);
    }
}
