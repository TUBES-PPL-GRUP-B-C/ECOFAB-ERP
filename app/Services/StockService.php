<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialStock;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

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
     */
    public function addStock(
        int $warehouseId,
        ?int $materialId,
        ?int $productId,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): bool {
        if ($quantity <= 0) {
            throw new Exception('Kuantitas penambahan stok harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($warehouseId, $materialId, $productId, $quantity, $referenceType, $referenceId, $notes, $userId) {
            if ($materialId) {
                $stock = MaterialStock::lockForUpdate()->firstOrCreate(
                    ['material_id' => $materialId, 'warehouse_id' => $warehouseId],
                    ['qty' => 0]
                );

                $stock->qty += $quantity;
                $stock->save();
            }

            // Catat ke log mutasi audit (NFR-12)
            StockMovement::create([
                'movement_date'   => now(),
                'material_id'     => $materialId,
                'product_id'      => $productId,
                'to_warehouse_id' => $warehouseId,
                'type'            => 'in',
                'qty'             => $quantity,
                'reference_type'  => $referenceType,
                'reference_id'    => $referenceId,
                'notes'           => $notes ?? "Penambahan stok ke gudang #{$warehouseId}",
                'created_by'      => $userId ?? auth()->id(),
            ]);

            return true;
        });
    }

    /**
     * Kurangi stok dari gudang dengan validasi stok tidak boleh negatif (NFR-04).
     *
     * @throws Exception Jika stok tidak mencukupi
     */
    public function deductStock(
        int $warehouseId,
        ?int $materialId,
        ?int $productId,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): bool {
        if ($quantity <= 0) {
            throw new Exception('Kuantitas pengurangan stok harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($warehouseId, $materialId, $productId, $quantity, $referenceType, $referenceId, $notes, $userId) {
            if ($materialId) {
                $stock = MaterialStock::lockForUpdate()
                    ->where('material_id', $materialId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                $currentQty = $stock ? (float) $stock->qty : 0;
                if ($currentQty < $quantity) {
                    $material = Material::find($materialId);
                    $name = $material ? $material->name : "ID {$materialId}";
                    throw new Exception("Stok tidak mencukupi untuk bahan '{$name}'. Saldo saat ini: {$currentQty}, dibutuhkan: {$quantity}.");
                }

                $stock->qty -= $quantity;
                $stock->save();
            }

            // Catat log mutasi pengeluaran
            StockMovement::create([
                'movement_date'     => now(),
                'material_id'       => $materialId,
                'product_id'        => $productId,
                'from_warehouse_id' => $warehouseId,
                'type'              => 'out',
                'qty'               => $quantity,
                'reference_type'    => $referenceType,
                'reference_id'      => $referenceId,
                'notes'             => $notes ?? "Pengeluaran stok dari gudang #{$warehouseId}",
                'created_by'        => $userId ?? auth()->id(),
            ]);

            return true;
        });
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
        ?string $notes = null,
        ?int $userId = null
    ): bool {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new Exception('Gudang asal dan gudang tujuan transfer tidak boleh sama.');
        }

        if ($quantity <= 0) {
            throw new Exception('Kuantitas transfer harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $materialId, $productId, $quantity, $notes, $userId) {
            if ($materialId) {
                $fromStock = MaterialStock::lockForUpdate()
                    ->where('material_id', $materialId)
                    ->where('warehouse_id', $fromWarehouseId)
                    ->first();

                $currentQty = $fromStock ? (float) $fromStock->qty : 0;
                if ($currentQty < $quantity) {
                    $material = Material::find($materialId);
                    $name = $material ? $material->name : "ID {$materialId}";
                    throw new Exception("Transfer gagal: Stok '{$name}' di gudang asal tidak mencukupi ({$currentQty} < {$quantity}).");
                }

                $fromStock->qty -= $quantity;
                $fromStock->save();

                $toStock = MaterialStock::lockForUpdate()->firstOrCreate(
                    ['material_id' => $materialId, 'warehouse_id' => $toWarehouseId],
                    ['qty' => 0]
                );
                $toStock->qty += $quantity;
                $toStock->save();
            }

            StockMovement::create([
                'movement_date'     => now(),
                'material_id'       => $materialId,
                'product_id'        => $productId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id'   => $toWarehouseId,
                'type'              => 'transfer',
                'qty'               => $quantity,
                'reference_type'    => 'TRANSFER',
                'notes'             => $notes ?? "Transfer stok bahan dari gudang #{$fromWarehouseId} ke #{$toWarehouseId}",
                'created_by'        => $userId ?? auth()->id(),
            ]);

            return true;
        });
    }

    /**
     * Cek apakah stok di gudang tertentu mencukupi.
     */
    public function checkStock(int $warehouseId, ?int $materialId, float $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $stock = MaterialStock::where('material_id', $materialId)
            ->where('warehouse_id', $warehouseId)
            ->value('qty') ?? 0;

        return (float) $stock >= $quantity;
    }

    /**
     * Penyesuaian stok opname fisik.
     */
    public function adjustStock(
        int $warehouseId,
        int $materialId,
        float $actualQuantity,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        if ($actualQuantity < 0) {
            throw new Exception('Jumlah stok fisik aktual tidak boleh bernilai negatif.');
        }

        return DB::transaction(function () use ($warehouseId, $materialId, $actualQuantity, $notes, $userId) {
            $stock = MaterialStock::lockForUpdate()->firstOrCreate(
                ['material_id' => $materialId, 'warehouse_id' => $warehouseId],
                ['qty' => 0]
            );

            $diff = $actualQuantity - (float) $stock->qty;
            $stock->qty = $actualQuantity;
            $stock->save();

            return StockMovement::create([
                'movement_date'     => now(),
                'material_id'       => $materialId,
                'from_warehouse_id' => $diff < 0 ? $warehouseId : null,
                'to_warehouse_id'   => $diff > 0 ? $warehouseId : null,
                'type'              => 'adjustment',
                'qty'               => abs($diff),
                'reference_type'    => 'ADJUSTMENT',
                'notes'             => $notes ?? "Penyesuaian stok menjadi {$actualQuantity} (Selisih: {$diff})",
                'created_by'        => $userId ?? auth()->id(),
            ]);
        });
    }
}
