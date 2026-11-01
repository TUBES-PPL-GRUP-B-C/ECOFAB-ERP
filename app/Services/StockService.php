<?php

namespace App\Services;

/**
 * Service untuk membungkus seluruh pergerakan stok gudang (tambah, kurang, transfer)
 * serta validasi stok tidak boleh minus (NFR-04).
 *
 * Dipakai oleh: GRN (Penerimaan), Work Order, Mutasi Stok, dan Pengiriman Penjualan.
 */
class StockService
{
    /**
     * Tambah stok bahan atau produk ke gudang tertentu (GRN, WO Selesai).
     *
     * @param int $warehouseId
     * @param int|null $materialId
     * @param int|null $productId
     * @param float $quantity
     * @param string $referenceType
     * @param int|null $referenceId
     * @param string|null $notes
     * @return bool
     */
    public function addStock(
        int $warehouseId,
        ?int $materialId,
        ?int $productId,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        ?string $notes = null
    ): bool {
        // Implementasi logika penambahan stok dan pencatatan stock_movements
        return true;
    }

    /**
     * Kurangi stok dari gudang dengan validasi stok tidak boleh negatif.
     *
     * @param int $warehouseId
     * @param int|null $materialId
     * @param int|null $productId
     * @param float $quantity
     * @param string $referenceType
     * @param int|null $referenceId
     * @param string|null $notes
     * @return bool
     * @throws \Exception Jika stok tidak mencukupi
     */
    public function deductStock(
        int $warehouseId,
        ?int $materialId,
        ?int $productId,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        ?string $notes = null
    ): bool {
        // Implementasi logika pemotongan stok dan pengecekan saldo
        return true;
    }

    /**
     * Transfer stok antar gudang.
     */
    public function transferStock(
        int $fromWarehouseId,
        int $toWarehouseId,
        ?int $materialId,
        ?int $productId,
        float $quantity,
        ?string $notes = null
    ): bool {
        return true;
    }
}
