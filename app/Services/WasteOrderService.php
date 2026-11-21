<?php

namespace App\Services;

use App\Models\User;
use App\Models\WasteOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WasteOrderService
{
    public function __construct(private readonly WasteListingInventory $inventory)
    {
    }

    public function create(array $data): WasteOrder
    {
        return DB::transaction(function () use ($data) {
            $listing = $this->inventory->lockListing((int) $data['waste_listing_id']);
            $this->inventory->assertOrderable($listing);
            $this->assertWeight((string) $data['requested_weight'], $listing->available_weight);

            return WasteOrder::create([
                ...Arr::only($data, ['buyer_name', 'buyer_contact', 'requested_weight']),
                'waste_listing_id' => $listing->id,
                'price_per_kg' => $listing->price_per_kg,
            ]);
        }, 3);
    }

    public function update(WasteOrder $order, array $data): void
    {
        DB::transaction(function () use ($order, $data) {
            $listing = $this->inventory->lockListing($order->waste_listing_id);
            $locked = WasteOrder::query()->lockForUpdate()->findOrFail($order->id);
            $this->assertPending($locked);
            $this->inventory->assertOrderable($listing);
            $this->assertWeight((string) $data['requested_weight'], $listing->available_weight);
            $locked->update(Arr::only($data, ['buyer_name', 'buyer_contact', 'requested_weight']));
        }, 3);
    }

    public function cancel(WasteOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $this->inventory->lockListing($order->waste_listing_id);
            $locked = WasteOrder::query()->lockForUpdate()->findOrFail($order->id);
            $this->assertPending($locked);
            $locked->forceFill(['status' => 'cancelled'])->save();
        }, 3);
    }

    public function decide(WasteOrder $order, User $admin, string $action, ?string $reason): void
    {
        abort_unless($admin->can('order-limbah.approve'), 403);
        abort_unless(in_array($action, ['approve', 'reject'], true), 422);
        DB::transaction(function () use ($order, $admin, $action, $reason) {
            $listing = $this->inventory->lockListing($order->waste_listing_id);
            $locked = WasteOrder::query()->lockForUpdate()->findOrFail($order->id);
            $this->assertPending($locked);
            if ($action === 'approve') {
                $this->inventory->assertOrderable($listing);
                $this->inventory->assertAllocation($listing->wasteRecord, $listing->available_weight, $listing->id);
                abort_if(bccomp($locked->requested_weight, $listing->available_weight, 3) > 0, 409, 'Stok limbah tidak mencukupi.');
                $listing->available_weight = bcsub($listing->available_weight, $locked->requested_weight, 3);
                $listing->save();
            }
            $locked->forceFill([
                'status' => $action === 'approve' ? 'approved' : 'rejected',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'decision_reason' => $reason,
            ])->save();
        }, 3);
    }

    private function assertPending(WasteOrder $order): void
    {
        abort_unless($order->status === 'pending', 409, 'Pesanan sudah diproses dan tidak dapat diubah kembali.');
    }

    private function assertWeight(string $requested, string $available): void
    {
        if (bccomp($requested, '0', 3) <= 0 || bccomp($requested, $available, 3) > 0) {
            throw ValidationException::withMessages(['requested_weight' => 'Berat pesanan harus lebih dari nol dan tidak melebihi stok tersedia.']);
        }
    }
}
