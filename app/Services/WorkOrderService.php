<?php

namespace App\Services;

use App\Contracts\WorkOrderReadinessChecker;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    public function __construct(
        private readonly WorkOrderReadinessChecker $readinessChecker,
        private readonly CarbonCalculatorService $carbonCalculator,
        private readonly WasteRecordService $wasteRecordService,
    ) {
    }

    public function create(array $data, User $creator): WorkOrder
    {
        return WorkOrder::create([
            ...$data,
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);
    }

    public function update(WorkOrder $workOrder, array $data): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data) {
            $locked = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            abort_unless($locked->isDraft(), 409, 'Hanya Work Order berstatus Draft yang dapat diubah.');

            $locked->update($data);

            return $locked;
        });
    }

    public function delete(WorkOrder $workOrder): void
    {
        DB::transaction(function () use ($workOrder) {
            $locked = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            abort_unless($locked->isDraft(), 409, 'Hanya Work Order berstatus Draft yang dapat dihapus.');
            abort_if(
                $locked->materials()->exists() || $locked->wasteRecords()->exists(),
                409,
                'Work Order yang memiliki catatan material atau limbah tidak dapat dihapus.'
            );

            $locked->delete();
        });
    }

    public function start(WorkOrder $workOrder): WorkOrder
    {
        return DB::transaction(function () use ($workOrder) {
            // Recheck the persisted state under lock, including repeated/stale requests.
            $locked = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            abort_unless($locked->canBeStarted(), 409, 'Hanya Work Order berstatus Draft yang dapat dimulai.');

            $this->readinessChecker->assertCanStart($locked);

            $locked->update([
                'status' => 'in_progress',
                'start_date' => $locked->start_date ?? today(),
            ]);

            return $locked;
        });
    }
    // FR-18 WO Otomatis
    public function finish(WorkOrder $workOrder, array $data): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data) {
            $locked = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            abort_unless($locked->canBeCompleted(), 409, 'Hanya Work Order berstatus In Progress yang dapat diselesaikan.');

            $actualQuantity = (float) $data['actual_quantity'];
            $actualEmission = isset($data['actual_emission']) && $data['actual_emission'] !== null
                ? (float) $data['actual_emission']
                : null;

            if ($actualEmission === null && ! empty($data['materials']) && ! empty($data['machines'])) {
                $calc = $this->carbonCalculator->calculateWorkOrderActual(
                    $data['materials'],
                    $data['machines'],
                    $actualQuantity
                );
                $actualEmission = $calc['total_emission'];
            }

            $locked->update([
                'status' => 'completed',
                'actual_quantity' => $actualQuantity,
                'actual_emission' => $actualEmission,
                'completed_at' => now(),
            ]);

            if (! empty($data['waste_type']) && isset($data['waste_weight_kg'])) {
                $this->wasteRecordService->recordFromCompletedWorkOrder(
                    $locked->id,
                    $data['waste_type'],
                    (float) $data['waste_weight_kg'],
                    $data['waste_condition'] ?? 'Bagus'
                );
            }

            return $locked->fresh(['wasteRecords']);
        });
    }
}
