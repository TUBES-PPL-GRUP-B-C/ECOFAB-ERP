<?php

namespace App\Services;

use App\Models\WasteListing;
use App\Models\WasteOrder;
use App\Models\WasteRecord;
use Illuminate\Validation\ValidationException;

/**
 * FR-22 weight_kg is the measured production quantity, not a mutable stock balance.
 * Its row serializes marketplace allocations and decisions with production edits.
 * Available marketplace stock plus approved history may never exceed that quantity.
 */
class WasteListingInventory
{
    public function lockListing(int $listingId): WasteListing
    {
        $reference = WasteListing::query()->findOrFail($listingId);
        $source = WasteRecord::query()->lockForUpdate()->find($reference->waste_record_id);
        abort_if($source === null, 409, 'Sumber limbah produksi tidak tersedia.');
        $listing = WasteListing::query()->lockForUpdate()->findOrFail($listingId);
        abort_if($listing->waste_record_id !== $source->id, 409, 'Sumber listing berubah. Muat ulang halaman dan coba kembali.');
        $listing->setRelation('wasteRecord', $source);

        return $listing;
    }

    public function assertAllocation(WasteRecord $source, string $weight, ?int $exceptListingId = null): void
    {
        $allocated = '0.000';
        // Locking reads use the latest committed rows even under MySQL REPEATABLE READ.
        // A plain aggregate after waiting for the source lock could use an older snapshot.
        $listings = WasteListing::query()->where('waste_record_id', $source->id)
            ->orderBy('id')->lockForUpdate()->get(['id', 'available_weight']);
        foreach ($listings as $listing) {
            if ($listing->id !== $exceptListingId) {
                $allocated = bcadd($allocated, $listing->available_weight, 3);
            }
        }
        $approvedOrders = WasteOrder::query()->where('status', 'approved')
            ->whereIn('waste_listing_id', $listings->modelKeys())
            ->orderBy('id')->lockForUpdate()->get(['requested_weight']);
        foreach ($approvedOrders as $order) {
            $allocated = bcadd($allocated, $order->requested_weight, 3);
        }
        if (bccomp(bcadd($allocated, $weight, 3), $source->weight_kg, 3) > 0) {
            throw ValidationException::withMessages([
                'available_weight' => 'Total alokasi listing dan limbah yang sudah terjual tidak boleh melebihi berat limbah produksi.',
            ]);
        }
    }

    public function assertOrderable(WasteListing $listing): void
    {
        abort_unless(
            $listing->status === 'published'
            && bccomp($listing->available_weight, '0', 3) > 0
            && in_array($listing->wasteRecord?->status, ['available', 'listed'], true),
            409,
            'Listing ini tidak tersedia untuk pemesanan.'
        );
    }
}
