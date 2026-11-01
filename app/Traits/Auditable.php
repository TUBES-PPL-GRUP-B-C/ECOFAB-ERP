<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait Auditable
{
    /**
     * Boot trait untuk otomatis mencatat created_by dan updated_by.
     */
    public static function bootAuditable(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $table = $model->getTable();
                if (Schema::hasColumn($table, 'created_by') && empty($model->created_by)) {
                    $model->created_by = Auth::id();
                }
                if (Schema::hasColumn($table, 'updated_by') && empty($model->updated_by)) {
                    $model->updated_by = Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $table = $model->getTable();
                if (Schema::hasColumn($table, 'updated_by')) {
                    $model->updated_by = Auth::id();
                }
            }
        });
    }

    /**
     * Relasi ke pembuat record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke pengubah record terakhir.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
