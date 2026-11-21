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
     * Tambah stok bahan ke gudang tertentu (GRN, WO Selesai).
     *
     * @throws Exception
     */
    public function addStock(
        ?int $materialId,
        int $warehouseId,
        float $qty,
        string $referenceType = 'MANUAL',
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        if ($qty <= 0) {
            throw new Exception('Kuantitas penambahan stok harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($materialId, $warehouseId, $qty, $referenceType, $referenceId, $notes, $userId) {
            if ($materialId) {
                $stock = MaterialStock::lockForUpdate()->firstOrCreate(
                    ['material_id' => $materialId, 'warehouse_id' => $warehouseId],
                    ['qty' => 0]
                );
                $stock->qty += $qty;
                $stock->save();
            }

            return StockMovement::create([
                'movement_date'   => now(),
                'material_id'     => $materialId,
                'product_id'      => null,
                'to_warehouse_id' => $warehouseId,
                'type'            => 'in',
                'qty'             => $qty,
                'reference_type'  => $referenceType,
                'reference_id'    => $referenceId,
                'notes'           => $notes ?? "Penambahan stok ke gudang #{$warehouseId}",
                'created_by'      => $userId ?? auth()->id(),
            ]);
        });
    }

    /**
     * Kurangi stok dari gudang dengan validasi stok tidak boleh negatif (NFR-04).
     *
     * @throws Exception Jika stok tidak mencukupi
     */
    public function reduceStock(
        ?int $materialId,
        int $warehouseId,
        float $qty,
        string $referenceType = 'MANUAL',
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        if ($qty <= 0) {
            throw new Exception('Kuantitas pengurangan stok harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($materialId, $warehouseId, $qty, $referenceType, $referenceId, $notes, $userId) {
            if ($materialId) {
                $stock = MaterialStock::lockForUpdate()
                    ->where('material_id', $materialId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                $currentQty = $stock ? (float) $stock->qty : 0;
                if ($currentQty < $qty) {
                    $material = Material::find($materialId);
                    $name = $material ? $material->name : "ID {$materialId}";
                    throw new Exception("Stok tidak mencukupi untuk bahan '{$name}'. Saldo saat ini: {$currentQty}, dibutuhkan: {$qty}.");
                }

                $stock->qty -= $qty;
                $stock->save();
            }

            return StockMovement::create([
                'movement_date'     => now(),
                'material_id'       => $materialId,
                'product_id'        => null,
                'from_warehouse_id' => $warehouseId,
                'type'              => 'out',
                'qty'               => $qty,
                'reference_type'    => $referenceType,
                'reference_id'      => $referenceId,
                'notes'             => $notes ?? "Pengeluaran stok dari gudang #{$warehouseId}",
                'created_by'        => $userId ?? auth()->id(),
            ]);
        });
    }

    /**
     * Alias untuk kompatibilitas — memanggil reduceStock().
     *
     * @throws Exception
     */
    public function deductStock(
        ?int $materialId,
        int $warehouseId,
        float $qty,
        string $referenceType = 'MANUAL',
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        return $this->reduceStock($materialId, $warehouseId, $qty, $referenceType, $referenceId, $notes, $userId);
    }

    /**
     * Transfer stok antar gudang.
     *
     * @throws Exception
     */
    public function transferStock(
        ?int $materialId,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $qty,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new Exception('Gudang asal dan gudang tujuan transfer tidak boleh sama.');
        }

        if ($qty <= 0) {
            throw new Exception('Kuantitas transfer harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($materialId, $fromWarehouseId, $toWarehouseId, $qty, $notes, $userId) {
            if ($materialId) {
                $fromStock = MaterialStock::lockForUpdate()
                    ->where('material_id', $materialId)
                    ->where('warehouse_id', $fromWarehouseId)
                    ->first();

                $currentQty = $fromStock ? (float) $fromStock->qty : 0;
                if ($currentQty < $qty) {
                    $material = Material::find($materialId);
                    $name = $material ? $material->name : "ID {$materialId}";
                    throw new Exception("Transfer gagal: Stok '{$name}' di gudang asal tidak mencukupi ({$currentQty} < {$qty}).");
                }

                $fromStock->qty -= $qty;
                $fromStock->save();

                $toStock = MaterialStock::lockForUpdate()->firstOrCreate(
                    ['material_id' => $materialId, 'warehouse_id' => $toWarehouseId],
                    ['qty' => 0]
                );
                $toStock->qty += $qty;
                $toStock->save();
            }

            return StockMovement::create([
                'movement_date'     => now(),
                'material_id'       => $materialId,
                'product_id'        => null,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id'   => $toWarehouseId,
                'type'              => 'transfer',
                'qty'               => $qty,
                'reference_type'    => 'TRANSFER',
                'notes'             => $notes ?? "Transfer stok bahan dari gudang #{$fromWarehouseId} ke #{$toWarehouseId}",
                'created_by'        => $userId ?? auth()->id(),
            ]);
        });
    }

    /**
     * Cek apakah stok di gudang tertentu mencukupi.
     */
    public function checkStock(int $warehouseId, ?int $materialId, float $qty): bool
    {
        if ($qty <= 0) {
            return false;
        }

        $stock = MaterialStock::where('material_id', $materialId)
            ->where('warehouse_id', $warehouseId)
            ->value('qty') ?? 0;

        return (float) $stock >= $qty;
    }

    /**
     * Penyesuaian stok opname fisik.
     *
     * @throws Exception
     */
    public function adjustStock(
        ?int $materialId,
        int $warehouseId,
        float $actualQty,
        ?string $notes = null,
        ?int $userId = null
    ): StockMovement {
        if ($actualQty < 0) {
            throw new Exception('Jumlah stok fisik aktual tidak boleh bernilai negatif.');
        }

        return DB::transaction(function () use ($materialId, $warehouseId, $actualQty, $notes, $userId) {
            $stock = MaterialStock::lockForUpdate()->firstOrCreate(
                ['material_id' => $materialId, 'warehouse_id' => $warehouseId],
                ['qty' => 0]
            );

            $diff = $actualQty - (float) $stock->qty;
            $stock->qty = $actualQty;
            $stock->save();

            return StockMovement::create([
                'movement_date'     => now(),
                'material_id'       => $materialId,
                'from_warehouse_id' => $diff < 0 ? $warehouseId : null,
                'to_warehouse_id'   => $diff > 0 ? $warehouseId : null,
                'type'              => 'adjustment',
                'qty'               => abs($diff),
                'reference_type'    => 'ADJUSTMENT',
                'notes'             => $notes ?? "Penyesuaian stok menjadi {$actualQty} (Selisih: {$diff})",
                'created_by'        => $userId ?? auth()->id(),
            ]);
        });
    }
}
