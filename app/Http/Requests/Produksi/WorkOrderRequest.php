<?php

namespace App\Http\Requests\Produksi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware enforces produksi.view and the appropriate CRUD permission.
        return true;
    }

    public function rules(): array
    {
        return [
            'wo_number' => [
                'required', 'string', 'max:50',
                Rule::unique('work_orders', 'wo_number')->ignore($this->route('workOrder')),
            ],
            'product_name' => ['required', 'string', 'max:255'],
            'target_quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:9999999999.99'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'due_date' => [
                'nullable', 'date_format:Y-m-d',
                Rule::when($this->filled('start_date'), ['after_or_equal:start_date']),
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
            // These fields belong to authenticated ownership, lifecycle or future integrations.
            'status' => ['prohibited'],
            'created_by' => ['prohibited'],
            'actual_quantity' => ['prohibited'],
            'actual_emission' => ['prohibited'],
            'completed_at' => ['prohibited'],
            'product_id' => ['prohibited'],
            'bom_id' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'wo_number' => 'nomor Work Order',
            'product_name' => 'nama produk',
            'target_quantity' => 'target produksi',
            'start_date' => 'tanggal mulai',
            'due_date' => 'tenggat produksi',
            'notes' => 'catatan',
        ];
    }
}
