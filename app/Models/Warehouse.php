<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'code',
        'name',
        'address',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
        ];
    }

    public function materialStocks(): HasMany
    {
        return $this->hasMany(MaterialStock::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function movementsFrom(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'from_warehouse_id');
    }

    public function movementsTo(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'to_warehouse_id');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
