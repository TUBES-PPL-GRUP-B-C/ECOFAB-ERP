<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $materialId = $this->route('id') ?? $this->route('material');

        return [
            'sku' => [
                'required',
                'string',
                'max:30',
                Rule::unique('materials', 'sku')->ignore($materialId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:20'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'emission_factor_id' => ['nullable', 'exists:emission_factors,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'SKU material wajib diisi.',
            'sku.unique' => 'SKU material sudah digunakan.',
            'name.required' => 'Nama bahan/material wajib diisi.',
            'category.required' => 'Kategori material wajib dipilih/diisi.',
            'unit.required' => 'Satuan material (kg, m, liter, dll) wajib diisi.',
            'min_stock.required' => 'Batas minimum stok wajib diisi.',
            'min_stock.min' => 'Batas stok minimum tidak boleh minus.',
            'unit_cost.required' => 'Biaya satuan (Rp) wajib diisi.',
            'unit_cost.min' => 'Biaya satuan tidak boleh minus.',
        ];
    }
}
