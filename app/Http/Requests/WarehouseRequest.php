<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $warehouseId = $this->route('id') ?? $this->route('gudang');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('warehouses', 'code')->ignore($warehouseId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode gudang wajib diisi.',
            'code.unique' => 'Kode gudang sudah digunakan.',
            'name.required' => 'Nama gudang wajib diisi.',
            'capacity.numeric' => 'Kapasitas harus berupa angka valid.',
            'capacity.min' => 'Kapasitas tidak boleh bernilai negatif.',
        ];
    }
}
