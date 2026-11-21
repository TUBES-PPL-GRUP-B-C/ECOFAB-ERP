<?php

namespace App\Services;

use App\Models\User;
use App\Models\WasteListing;
use App\Models\WasteOrder;
use App\Models\WasteRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        app(WasteRecordService::class)->recordFromCompletedWorkOrder($workOrderId, $type, $weightKg, $condition);

        return true;
    }

    /**
     * Publikasikan listing limbah ke katalog bursa limbah publik.
     */
    public function publishListing(int $wasteRecordId, string $title, float $availableWeight, float $pricePerKg): bool
    {
        $data = Validator::make([
            'category' => trim($title), 'available_weight' => $availableWeight, 'price_per_kg' => $pricePerKg,
        ], [
            'category' => ['required', 'string', 'max:255'],
            'available_weight' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:999999999.999'],
            'price_per_kg' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999.99'],
        ])->validate();

        return DB::transaction(function () use ($wasteRecordId, $data) {
            $source = WasteRecord::query()->lockForUpdate()->findOrFail($wasteRecordId);
            abort_unless(in_array($source->status, ['available', 'listed'], true), 409, 'Sumber limbah tidak tersedia.');
            app(WasteListingInventory::class)->assertAllocation($source, (string) $data['available_weight']);
            $listing = new WasteListing([...$data, 'waste_record_id' => $source->id]);
            $listing->status = 'published';
            $listing->save();

            return true;
        }, 3);
    }

    /**
     * Setujui pesanan limbah oleh Admin dan potong saldo limbah yang tersedia.
     */
    public function approveOrder(int $orderId, int $adminUserId): bool
    {
        app(WasteOrderService::class)->decide(WasteOrder::findOrFail($orderId), User::findOrFail($adminUserId), 'approve', null);

        return true;
    }

    /**
     * Tolak pesanan limbah.
     */
    public function rejectOrder(int $orderId, int $adminUserId, ?string $reason = null): bool
    {
        Validator::make(['reason' => $reason], ['reason' => ['nullable', 'string', 'max:500']])->validate();
        app(WasteOrderService::class)->decide(WasteOrder::findOrFail($orderId), User::findOrFail($adminUserId), 'reject', $reason);

        return true;
    }
}
