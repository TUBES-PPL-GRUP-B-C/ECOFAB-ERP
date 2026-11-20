<?php

namespace App\Services;

use App\Contracts\WorkOrderReadinessChecker;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    public function __construct(private readonly WorkOrderReadinessChecker $readinessChecker)
    {
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
}
