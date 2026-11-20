<?php

namespace App\Contracts;

use App\Models\WorkOrder;

interface WorkOrderReadinessChecker
{
    /**
     * Verify the actual BoM, material requirements and available stock before starting.
     * Throw a ValidationException if verification fails or a dependency is unavailable.
     * This check must not deduct stock: production completion belongs to FR-18.
     */
    public function assertCanStart(WorkOrder $workOrder): void;
}
