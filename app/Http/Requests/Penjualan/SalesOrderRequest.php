<?php

namespace App\Http\Requests\Penjualan;

use App\Models\SalesOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productIdRules = ['nullable', 'integer', 'min:1'];

        if (Schema::hasTable('products')) {
            $productIdRules[] = Rule::exists('products', 'id');
        }

        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'order_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(array_keys(SalesOrder::STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => $productIdRules,
            'items.*.product_name' => ['required', 'string', 'max:150'],
            'items.*.qty' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:999999999.999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999.99'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'customer',
            'order_date' => 'tanggal order',
            'status' => 'status',
            'notes' => 'catatan',
            'items' => 'item pesanan',
            'items.*.product_name' => 'nama produk',
            'items.*.qty' => 'jumlah',
            'items.*.unit_price' => 'harga satuan',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Sales Order minimal harus memiliki satu item.',
            'items.min' => 'Sales Order minimal harus memiliki satu item.',
            'items.*.qty.gt' => 'Jumlah item harus lebih besar dari 0.',
        ];
    }
}
