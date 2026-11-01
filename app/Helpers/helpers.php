<?php

use Carbon\Carbon;

if (!function_exists('format_angka')) {
    /**
     * Format angka standar Indonesia (pemisah ribuan titik, pemisah desimal koma).
     */
    function format_angka(float|int|string|null $nilai, int $desimal = 0): string
    {
        if ($nilai === null || $nilai === '') {
            return '0';
        }

        $num = (float) $nilai;

        // Hilangkan trailing zero jika desimal > 0 tapi angka bulat atau nol
        $formatted = number_format($num, $desimal, ',', '.');

        return $formatted;
    }
}

if (!function_exists('format_rupiah')) {
    /**
     * Format mata uang Rupiah (contoh: Rp 4.250.000).
     */
    function format_rupiah(float|int|string|null $nominal, bool $denganSimbol = true): string
    {
        if ($nominal === null || $nominal === '') {
            return $denganSimbol ? 'Rp 0' : '0';
        }

        $formatted = number_format((float) $nominal, 0, ',', '.');

        return $denganSimbol ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('format_emisi')) {
    /**
     * Format jejak karbon dalam satuan kg CO₂e (contoh: 12.480 kg CO₂e atau 4,2 kg CO₂e).
     */
    function format_emisi(float|int|string|null $kgCo2e, int $desimal = 1): string
    {
        if ($kgCo2e === null || $kgCo2e === '') {
            return '0 kg CO₂e';
        }

        return format_angka($kgCo2e, $desimal) . ' kg CO₂e';
    }
}

if (!function_exists('format_tanggal')) {
    /**
     * Format tanggal Indonesia dengan Carbon (contoh: 01 Nov 2026 atau 1 November 2026).
     */
    function format_tanggal(mixed $tanggal, string $format = 'd M Y'): string
    {
        if (empty($tanggal)) {
            return '-';
        }

        return Carbon::parse($tanggal)->locale('id')->translatedFormat($format);
    }
}
