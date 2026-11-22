<?php

namespace App\Http\Requests\Penjualan;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = $customer instanceof Customer ? $customer->id : $customer;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'code')->ignore($customerId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'kode customer',
            'name' => 'nama customer',
            'address' => 'alamat',
            'phone' => 'nomor telepon',
            'email' => 'email',
        ];
    }
}
