<?php

namespace App\Models;

use App\Traits\Searchable;
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

    // FR-22 supplies the WasteRecord model and its table; this relation is ready for integration.
    public function wasteRecord(): BelongsTo
    {
        return $this->belongsTo(WasteRecord::class);
    }
}
