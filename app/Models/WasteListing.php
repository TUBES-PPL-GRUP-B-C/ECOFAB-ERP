<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteListing extends Model
{
    use Searchable;

    protected $fillable = [
        'waste_record_id',
        'photo_path',
        'category',
        'price_per_kg',
        'available_weight',
    ];

    protected function casts(): array
    {
        return [
            'price_per_kg' => 'decimal:2',
            'available_weight' => 'decimal:3',
        ];
    }

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('available_weight', '>', 0)
            ->whereHas('wasteRecord', fn ($source) => $source->whereIn('status', ['available', 'listed']));
    }

    // FR-22 supplies the measured production record; private source fields stay internal.
    public function wasteRecord(): BelongsTo
    {
        return $this->belongsTo(WasteRecord::class);
    }
}
