<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use Searchable;

    protected $fillable = [
        'department_id',
        'user_id',
        'employee_code',
        'name',
        'position',
        'phone',
        'address',
        'join_date',
        'status',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // FR-05 (Sesi 05) supplies the Attendance model and its table; this relation is ready for integration.
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
