<?php

namespace App\Contracts;

interface ProductStockChecker
{
    public function isIntegrated(): bool;

    /**
     * Memeriksa ketersediaan stok produk jadi untuk item Sales Order.
     * Method ini hanya memvalidasi dan tidak pernah memotong stok;
     * pemotongan stok adalah tanggung jawab FR-21 Pengiriman.
     *
     * @param array<int, array<string, mixed>> $items
     */
    public function assertAvailable(array $items): void;
}
