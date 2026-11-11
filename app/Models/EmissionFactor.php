<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmissionFactor extends Model
{
    protected $fillable = [
        'name',
        'type',
        'unit',
        'value_kg_co2e',
        'source',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'value_kg_co2e' => 'decimal:5',
            'year' => 'integer',
        ];
    }
}
