<?php

namespace App\Models;

use App\Traits\Searchable;
use Carbon\Carbon;
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
