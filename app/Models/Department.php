<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use Searchable;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    // FR-04 supplies the Employee model and its table; this relation is ready for integration.
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}

