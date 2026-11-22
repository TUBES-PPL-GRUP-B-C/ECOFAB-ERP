<?php

namespace App\Services;

use App\Contracts\ProductStockChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ProductStockAvailabilityService implements ProductStockChecker
{
    public function isIntegrated(): bool
    {
        return Schema::hasTable('product_stocks');
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function assertAvailable(array $items): void
    {
        if (! $this->isIntegrated()) {
            return;
        }

        $demands = [];

        foreach ($items as $index => $item) {
            if (! filled($item['product_id'] ?? null)) {
                continue;
            }

            $productId = (int) $item['product_id'];
            $qty = round((float) ($item['qty'] ?? 0), 3);

            if (! isset($demands[$productId])) {
                $demands[$productId] = [
                    'qty' => 0.0,
                    'product_name' => (string) ($item['product_name'] ?? "Produk #{$productId}"),
                    'index' => $index,
                ];
            }

            $demands[$productId]['qty'] = round($demands[$productId]['qty'] + $qty, 3);
            $demands[$productId]['product_name'] = (string) ($item['product_name'] ?? $demands[$productId]['product_name']);
            $demands[$productId]['index'] = $index;
        }

        $errors = [];

        foreach ($demands as $productId => $demand) {
            $available = round((float) DB::table('product_stocks')
                ->where('product_id', $productId)
                ->sum('qty'), 3);

            if ($available < $demand['qty']) {
                $name = $demand['product_name'];
                $index = $demand['index'];
                $errors["items.{$index}.qty"] = "Stok produk jadi '{$name}' tidak mencukupi (tersedia: "
                    . format_angka($available, 3)
                    . ', diminta: '
                    . format_angka($demand['qty'], 3)
                    . ').';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
