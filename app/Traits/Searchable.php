<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Scope query untuk melakukan pencarian di banyak kolom.
     *
     * Contoh pemakaian di Controller:
     * Product::search($request->cari, ['sku', 'name', 'category'])
     *     ->filter($request->only(['status', 'kategori']))
     *     ->latest()
     *     ->paginate(15)
     *     ->withQueryString();
     *
     * @param Builder $query
     * @param string|null $term
     * @param array $columns
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $term, array $columns = []): Builder
    {
        if (empty($term) || empty($columns)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $subQuery) use ($term, $columns) {
            foreach ($columns as $index => $column) {
                // Mendukung pencarian relasi: 'supplier.name'
                if (str_contains($column, '.')) {
                    [$relation, $relationColumn] = explode('.', $column, 2);
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $subQuery->$method($relation, function (Builder $relQuery) use ($relationColumn, $term) {
                        $relQuery->where($relationColumn, 'LIKE', "%{$term}%");
                    });
                } else {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $subQuery->$method($column, 'LIKE', "%{$term}%");
                }
            }
        });
    }

    /**
     * Scope query untuk filter nilai eksak kolom tertentu.
     *
     * @param Builder $query
     * @param array $filters Key-value array parameter filter
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        return $query;
    }
}
