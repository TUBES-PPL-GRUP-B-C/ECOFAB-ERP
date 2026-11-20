<?php

namespace App\Models;

use App\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use Searchable;

    public const SHIFTS = [
        'Pagi',
        'Siang',
        'Malam',
    ];

    public const STATUSES = [
        'hadir',
        'izin',
        'sakit',
        'alpa',
    ];

    protected $fillable = [
        'employee_id',
        'work_date',
        'shift',
        'check_in',
        'check_out',
        'status',
        'notes',
    ];

    public function setWorkDateAttribute(mixed $value): void
    {
        $this->attributes['work_date'] = $value !== null && $value !== ''
            ? Carbon::parse($value)->toDateString()
            : null;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeDateRange(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate !== null && $startDate !== '') {
            try {
                $query->where('work_date', '>=', Carbon::parse($startDate)->toDateString());
            } catch (\Throwable) {
                // Ignore malformed date filter input.
            }
        }

        if ($endDate !== null && $endDate !== '') {
            try {
                $query->where('work_date', '<=', Carbon::parse($endDate)->toDateString());
            } catch (\Throwable) {
                // Ignore malformed date filter input.
            }
        }

        return $query;
    }

    public function scopeForMonth(Builder $query, ?string $month): Builder
    {
        if ($month !== null && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1) {
            $start = $month . '-01';
            $end = Carbon::createFromFormat('Y-m-d', $start)->endOfMonth()->toDateString();
            $query->whereBetween('work_date', [$start, $end]);
        }

        return $query;
    }

    public function getBadgeStatusAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'aktif',
            'izin' => 'proses',
            'sakit' => 'warning',
            'alpa' => 'danger',
            default => 'default',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpa' => 'Alpa',
            default => ucfirst((string) $this->status),
        };
    }
}
