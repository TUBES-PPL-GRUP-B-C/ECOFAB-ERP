<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $grnId = $this->route('id') ?? $this->route('grn');

        return [
            'grn_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('goods_receipts', 'grn_number')->ignore($grnId),
            ],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'received_date' => ['required', 'date'],
            'purchase_order_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'exists:materials,id'],
            'items.*.qty_received' => ['required', 'numeric', 'min:0.001'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'grn_number.required' => 'Nomor GRN wajib diisi.',
            'grn_number.unique' => 'Nomor GRN sudah ada dalam sistem.',
            'warehouse_id.required' => 'Gudang penerima barang wajib dipilih.',
            'items.required' => 'Minimal 1 item bahan yang diterima harus dicatat.',
            'items.*.qty_received.min' => 'Kuantitas barang diterima minimal 0.001.',
        ];
    }
}
