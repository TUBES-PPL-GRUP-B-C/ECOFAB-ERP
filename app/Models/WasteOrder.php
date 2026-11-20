<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteOrder extends Model
{
    public const STATUSES = [
        'pending' => 'Menunggu persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = ['waste_listing_id', 'buyer_name', 'buyer_contact', 'requested_weight', 'price_per_kg'];

    protected function casts(): array
    {
        return [
            'requested_weight' => 'decimal:3',
            'price_per_kg' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(WasteListing::class, 'waste_listing_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
