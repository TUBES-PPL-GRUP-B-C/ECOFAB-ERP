<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use Searchable;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'country',
        'status',
    ];

    // FR-07 (Sprint 2) supplies the PurchaseOrder model and its table; this relation is ready for integration.
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getOriginLocationAttribute(): string
    {
        return implode(', ', array_filter([
            $this->city,
            $this->province,
            $this->country,
        ]));
    }
}
