<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Budi Santoso',
                'email' => 'admin@ecofab.local',
                'role' => 'admin',
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'hr@ecofab.local',
                'role' => 'hr',
            ],
            [
                'name' => 'Ahmad Hidayat',
                'email' => 'pembelian@ecofab.local',
                'role' => 'pembelian',
            ],
            [
                'name' => 'Bambang Wijaya',
                'email' => 'gudang@ecofab.local',
                'role' => 'gudang',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'produksi@ecofab.local',
                'role' => 'produksi',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'penjualan@ecofab.local',
                'role' => 'penjualan',
            ],
        ];

        foreach ($accounts as $acc) {
            $user = User::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$acc['role']]);
        }
    }
}
