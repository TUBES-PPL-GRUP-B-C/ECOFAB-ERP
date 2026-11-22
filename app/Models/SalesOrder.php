<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesOrder extends Model
{
    use Searchable;

    public const STATUSES = [
        'draft' => 'Draft',
        'confirmed' => 'Dikonfirmasi',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'so_number',
        'customer_id',
        'order_date',
        'status',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Status badge yang dikenali komponen x-badge.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => 'disetujui',
            'cancelled' => 'batal',
            default => 'draft',
        };
    }

    public function hasDeliveries(): bool
    {
        return Schema::hasTable('deliveries')
            && DB::table('deliveries')->where('sales_order_id', $this->id)->exists();
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'confirmed'], true) && ! $this->hasDeliveries();
    }

    public function isDeletable(): bool
    {
        return in_array($this->status, ['draft', 'cancelled'], true) && ! $this->hasDeliveries();
    }

    /**
     * Nomor SO berurutan per hari: SO-YYYYMMDD-001.
     */
    public static function generateNumber(): string
    {
        $prefix = 'SO-' . now()->format('Ymd') . '-';

        $last = static::where('so_number', 'like', $prefix . '%')
            ->orderByDesc('so_number')
            ->lockForUpdate()
            ->value('so_number');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
