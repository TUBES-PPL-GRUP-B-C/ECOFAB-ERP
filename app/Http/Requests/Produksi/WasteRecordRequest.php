<?php

namespace App\Http\Requests\Produksi;

use App\Models\WasteRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WasteRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware enforces module and action permissions.
    }

    public function rules(): array
    {
        return [
            'work_order_id' => [
                'nullable', 'integer', 'min:1',
                Rule::exists('work_orders', 'id')->where('status', 'completed'),
            ],
            'waste_type' => ['required', 'string', 'max:100'],
            'weight_kg' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:999999999.999'],
            'condition' => ['required', 'string', 'max:50'],
            'status' => ['sometimes', 'required', Rule::in(array_keys(WasteRecord::MANUAL_STATUSES))],
            'notes' => ['nullable', 'string', 'max:5000'],
            'created_by' => ['prohibited'],
            'id' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'work_order_id' => 'Work Order selesai',
            'waste_type' => 'jenis limbah',
            'weight_kg' => 'berat limbah',
            'condition' => 'kondisi limbah',
            'status' => 'status limbah',
            'notes' => 'catatan',
        ];
    }
}
