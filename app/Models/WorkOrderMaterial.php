<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderMaterial extends Model
{
    protected $fillable = [
        'work_order_id',
        'material_name',
        'material_id',
        'supplier_id',
        'required_quantity',
        'actual_quantity',
        'unit',
        'unit_cost',
        'emission_factor',
    ];

    protected function casts(): array
    {
        return [
            'required_quantity' => 'decimal:2',
            'actual_quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'emission_factor' => 'decimal:4',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
