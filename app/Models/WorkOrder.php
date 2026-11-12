<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use Searchable;

    protected $fillable = [
        'wo_number',
        'product_name',
        'product_id',
        'bom_id',
        'target_quantity',
        'actual_quantity',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'actual_emission',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_quantity' => 'decimal:2',
            'actual_quantity' => 'decimal:2',
            'actual_emission' => 'decimal:4',
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(WorkOrderMaterial::class);
    }

    public function wasteRecords(): HasMany
    {
        return $this->hasMany(WasteRecord::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }
}
