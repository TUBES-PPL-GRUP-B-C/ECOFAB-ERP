<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Service untuk pembuatan paspor digital produk (Eco-Passport / FR-26 & FR-27).
 *
 * Menghasilkan kode unik paspor, QRCode QR untuk kemasan fisik,
 * serta menyusun data rantai pasok / asal bahan baku produk ramah lingkungan.
 */
class EcoPassportService
{
    /**
     * Buat kode unik dan rekam paspor produk dari Work Order.
     *
     * @param int $workOrderId
     * @param int $productId
     * @param float $totalCarbonFootprint
     * @return string Kode paspor yang dihasilkan (e.g. ECO-PASS-WO0013)
     */
    public function generatePassport(int $workOrderId, int $productId, float $totalCarbonFootprint): string
    {
        $code = 'ECO-PASS-' . strtoupper(uniqid());

        return $code;
    }

    /**
     * Hasilkan QR Code SVG/PNG untuk kode paspor.
     *
     * @param string $passportCode
     * @return string Konten QR code
     */
    public function generateQrCode(string $passportCode): string
    {
        $url = url('/passport/' . $passportCode);

        return QrCode::size(250)->generate($url);
    }

    /**
     * Ambil data publik untuk ditampilkan di halaman hasil pindai QR (tanpa harga/biaya internal).
     */
    public function getPublicPassportData(string $passportCode): ?array
    {
        return null;
    }
}
