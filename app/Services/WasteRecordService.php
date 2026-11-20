<?php

namespace App\Services;

use App\Models\User;
use App\Models\WasteRecord;
use App\Models\WorkOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WasteRecordService
{
    private const EDITABLE_FIELDS = ['work_order_id', 'waste_type', 'weight_kg', 'condition', 'status', 'notes'];

    public function create(array $data, User $creator): WasteRecord
    {
        return DB::transaction(function () use ($data, $creator) {
            $attributes = Arr::only($data, self::EDITABLE_FIELDS);
            $this->assertCompletedWorkOrder($attributes['work_order_id'] ?? null);

            return WasteRecord::create([
                ...$attributes,
                'status' => $attributes['status'] ?? 'available',
                'created_by' => $creator->id,
            ]);
        });
    }

    public function update(WasteRecord $wasteRecord, array $data): WasteRecord
    {
        return DB::transaction(function () use ($wasteRecord, $data) {
            $locked = WasteRecord::query()->lockForUpdate()->findOrFail($wasteRecord->id);
            $this->assertManageable($locked);

            $attributes = Arr::only($data, self::EDITABLE_FIELDS);
            if (array_key_exists('work_order_id', $attributes)) {
                $this->assertCompletedWorkOrder($attributes['work_order_id']);
            }
            $locked->update($attributes);

            return $locked;
        });
    }

    public function delete(WasteRecord $wasteRecord): void
    {
        DB::transaction(function () use ($wasteRecord) {
            $locked = WasteRecord::query()->lockForUpdate()->findOrFail($wasteRecord->id);
            $this->assertManageable($locked);
            $locked->delete();
        });
    }

    public function assertManageable(WasteRecord $wasteRecord): void
    {
        abort_unless(
            array_key_exists($wasteRecord->status, WasteRecord::MANUAL_STATUSES),
            409,
            'Status limbah ini dikelola melalui Bursa Limbah dan tidak dapat diubah atau dihapus dari produksi.'
        );
        abort_if(
            $wasteRecord->wasteListings()->exists(),
            409,
            'Limbah yang sudah digunakan pada listing Bursa Limbah tidak dapat diubah atau dihapus.'
        );
    }

    /**
     * Recording boundary for FR-18. The caller supplies measured waste and must
     * invoke it once within its completion transaction; no waste is inferred.
     */
    public function recordFromCompletedWorkOrder(
        int $workOrderId,
        string $type,
        float $weightKg,
        ?string $condition = null
    ): WasteRecord {
        $data = Validator::make([
            'waste_type' => trim($type),
            'weight_kg' => $weightKg,
            'condition' => $condition === null ? 'Bagus' : trim($condition),
        ], [
            'waste_type' => ['required', 'string', 'max:100'],
            'weight_kg' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:999999999.999'],
            'condition' => ['required', 'string', 'max:50'],
        ])->validate();

        return DB::transaction(function () use ($workOrderId, $data) {
            $workOrder = $this->assertCompletedWorkOrder($workOrderId);

            return WasteRecord::create([
                ...$data,
                'work_order_id' => $workOrder->id,
                'status' => 'available',
                'created_by' => $workOrder->created_by,
            ]);
        });
    }

    private function assertCompletedWorkOrder(?int $workOrderId): ?WorkOrder
    {
        if ($workOrderId === null) {
            return null;
        }

        $workOrder = WorkOrder::query()->lockForUpdate()->find($workOrderId);
        if ($workOrder === null || ! $workOrder->isCompleted()) {
            throw ValidationException::withMessages([
                'work_order_id' => 'Pilih Work Order yang sudah selesai, atau kosongkan untuk pencatatan manual.',
            ]);
        }

        return $workOrder;
    }
}
