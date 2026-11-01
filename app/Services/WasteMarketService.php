<?php

namespace App\Services;

/**
 * Service untuk mengelola siklus limbah manufaktur sirkular:
 * Pencatatan dari Work Order (FR-22), publikasi listing bursa limbah (FR-23),
 * dan persetujuan pesanan limbah oleh Admin (FR-25).
 */
class WasteMarketService
{
    /**
     * Catat limbah hasil dari Work Order yang selesai.
     */
    public function recordWasteFromWorkOrder(int $workOrderId, string $type, float $weightKg, ?string $condition = null): bool
    {
        return true;
    }

    /**
     * Publikasikan listing limbah ke katalog bursa limbah publik.
     */
    public function publishListing(int $wasteRecordId, string $title, float $availableWeight, float $pricePerKg): bool
    {
        return true;
    }

    /**
     * Setujui pesanan limbah oleh Admin dan potong saldo limbah yang tersedia.
     */
    public function approveOrder(int $orderId, int $adminUserId): bool
    {
        return true;
    }

    /**
     * Tolak pesanan limbah.
     */
    public function rejectOrder(int $orderId, int $adminUserId, ?string $reason = null): bool
    {
        return true;
    }
}
