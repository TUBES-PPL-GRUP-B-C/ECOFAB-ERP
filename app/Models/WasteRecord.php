<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WasteRecord extends Model
{
    use Searchable;

    public const MANUAL_STATUSES = [
        'available' => 'Tersedia',
        'sold' => 'Terjual',
    ];

    public const STATUSES = [
        'available' => 'Tersedia',
        'listed' => 'Terdaftar di Bursa',
        'sold' => 'Terjual',
    ];

    protected $fillable = [
        'work_order_id',
        'waste_type',
        'weight_kg',
        'condition',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:3',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function wasteListing(): HasOne
    {
        return $this->hasOne(WasteListing::class);
    }

    public function wasteListings(): HasMany
    {
        return $this->hasMany(WasteListing::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isListed(): bool
    {
        return $this->status === 'listed';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }
}
