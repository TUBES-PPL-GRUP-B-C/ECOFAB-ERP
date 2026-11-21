<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'unit',
        'min_stock',
        'unit_cost',
        'emission_factor_id',
    ];

    protected function casts(): array
    {
        return [
            'min_stock' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function emissionFactor(): BelongsTo
    {
        return $this->belongsTo(EmissionFactor::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(MaterialStock::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Hitung total kuantitas stok bahan di semua gudang
     */
    public function getTotalStockAttribute(): float
    {
        return (float) $this->stocks()->sum('qty');
    }

    /**
     * Cek apakah stok berada di bawah batas minimum (FR-10 penanda stok di bawah minimum)
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->total_stock <= (float) $this->min_stock;
    }
}
