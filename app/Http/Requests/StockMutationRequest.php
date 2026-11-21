<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'type' => ['required', 'in:transfer,adjustment'],
            'material_id' => ['required', 'exists:materials,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($type === 'transfer') {
            $rules['from_warehouse_id'] = ['required', 'exists:warehouses,id', 'different:to_warehouse_id'];
            $rules['to_warehouse_id'] = ['required', 'exists:warehouses,id'];
            $rules['qty'] = ['required', 'numeric', 'min:0.001'];
        } elseif ($type === 'adjustment') {
            $rules['warehouse_id'] = ['required', 'exists:warehouses,id'];
            $rules['actual_qty'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'from_warehouse_id.different' => 'Gudang asal dan gudang tujuan transfer tidak boleh sama.',
            'qty.min' => 'Jumlah barang yang dipindahkan minimal 0.001.',
            'actual_qty.min' => 'Jumlah stok fisik aktual tidak boleh bernilai negatif.',
        ];
    }
}
