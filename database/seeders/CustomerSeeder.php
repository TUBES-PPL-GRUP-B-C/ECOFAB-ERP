<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'code' => 'CUST-001',
                'name' => 'PT Mebel Hijau Nusantara',
                'address' => 'Jl. Raya Kemang No. 48, Jakarta Selatan, DKI Jakarta',
                'phone' => '021-7890123',
                'email' => 'purchasing@mebelhijau.co.id',
            ],
            [
                'code' => 'CUST-002',
                'name' => 'CV Kriya Bambu Lestari',
                'address' => 'Jl. Dago Pakar Barat No. 15, Bandung, Jawa Barat',
                'phone' => '022-2504321',
                'email' => 'order@kriyabambu.id',
            ],
            [
                'code' => 'CUST-003',
                'name' => 'PT Resort Ekowisata Bali',
                'address' => 'Jl. Raya Ubud KM 4, Gianyar, Bali',
                'phone' => '0361-975888',
                'email' => 'procurement@ekowisatabali.com',
            ],
            [
                'code' => 'CUST-004',
                'name' => 'UD Interior Rotan Cirebon',
                'address' => 'Jl. Plered Tengah No. 22, Cirebon, Jawa Barat',
                'phone' => '0231-321456',
                'email' => 'admin@interiorrotan.id',
            ],
            [
                'code' => 'CUST-005',
                'name' => 'PT Ruang Kerja Sirkular',
                'address' => 'Kawasan BSD Green Office Park Kav. 7, Tangerang Selatan',
                'phone' => '021-5550987',
                'email' => 'supply@ruangkerjasirkular.co.id',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['code' => $customer['code']], $customer);
        }
    }
}
