<?php

namespace App\Services;

use App\Contracts\WorkOrderReadinessChecker;
use App\Models\WorkOrder;
use Illuminate\Validation\ValidationException;

class PendingWorkOrderReadinessChecker implements WorkOrderReadinessChecker
{
    public function assertCanStart(WorkOrder $workOrder): void
    {
        // No BoM or inventory schema exists yet. StockService currently contains
        // movement placeholders and cannot prove that material stock is sufficient.
        throw ValidationException::withMessages([
            'readiness' => 'Work Order belum dapat dimulai. Integrasi resep Green BoM dan stok bahan belum tersedia; status tetap Draft.',
        ]);
    }
}
