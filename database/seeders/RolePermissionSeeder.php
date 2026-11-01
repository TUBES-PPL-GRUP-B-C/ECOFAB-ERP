<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Daftar seluruh permission berdasarkan modul dan aksi
        $permissions = [
            // Dashboard
            'dashboard.view',
            'dashboard.emisi',
            'dashboard.wo',
            'dashboard.stok',
            'dashboard.limbah',
            'dashboard.order_limbah',
            'dashboard.approval',

            // Modul HR
            'hr.view',
            'hr.create',
            'hr.update',
            'hr.delete',

            // Modul Pembelian
            'pembelian.view',
            'pembelian.create',
            'pembelian.update',
            'pembelian.delete',
            'po.approve',

            // Modul Gudang
            'gudang.view',
            'gudang.create',
            'gudang.update',
            'gudang.delete',

            // Modul Produksi
            'produksi.view',
            'produksi.create',
            'produksi.update',
            'produksi.delete',
            'wo.start',
            'wo.finish',

            // Modul Penjualan
            'penjualan.view',
            'penjualan.create',
            'penjualan.update',
            'penjualan.delete',

            // Modul Green (Faktor Emisi)
            'green.view',
            'green.create',
            'green.update',
            'green.delete',
            'emisi.manage',

            // Modul Bursa Limbah
            'bursa.view',
            'bursa.create',
            'bursa.update',
            'bursa.delete',
            'order-limbah.approve',

            // Modul Eco-Passport
            'passport.view',
            'passport.create',
            'passport.update',
            'passport.delete',
            'passport.publish',

            // Manajemen Pengguna (Admin)
            'users.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Buat 6 Peran Internal Pabrik
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $hrRole = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']);
        $pembelianRole = Role::firstOrCreate(['name' => 'pembelian', 'guard_name' => 'web']);
        $gudangRole = Role::firstOrCreate(['name' => 'gudang', 'guard_name' => 'web']);
        $produksiRole = Role::firstOrCreate(['name' => 'produksi', 'guard_name' => 'web']);
        $penjualanRole = Role::firstOrCreate(['name' => 'penjualan', 'guard_name' => 'web']);

        // 3. Admin memiliki SEMUA permission
        $adminRole->syncPermissions(Permission::all());

        // 4. Staf HR
        $hrRole->syncPermissions([
            'dashboard.view',
            'hr.view',
            'hr.create',
            'hr.update',
            'hr.delete',
        ]);

        // 5. Staf Pembelian (Pengadaan)
        $pembelianRole->syncPermissions([
            'dashboard.view',
            'pembelian.view',
            'pembelian.create',
            'pembelian.update',
            'pembelian.delete',
            'gudang.view',
        ]);

        // 6. Staf Gudang & Material
        $gudangRole->syncPermissions([
            'dashboard.view',
            'dashboard.stok',
            'gudang.view',
            'gudang.create',
            'gudang.update',
            'gudang.delete',
        ]);

        // 7. Staf Produksi Sirkular
        $produksiRole->syncPermissions([
            'dashboard.view',
            'dashboard.emisi',
            'dashboard.wo',
            'dashboard.stok',
            'dashboard.limbah',
            'produksi.view',
            'produksi.create',
            'produksi.update',
            'produksi.delete',
            'wo.start',
            'wo.finish',
            'green.view',
            'passport.view',
            'passport.create',
            'passport.update',
            'passport.delete',
            'passport.publish',
        ]);

        // 8. Staf Penjualan (Komersial)
        $penjualanRole->syncPermissions([
            'dashboard.view',
            'dashboard.limbah',
            'dashboard.order_limbah',
            'dashboard.wo',
            'penjualan.view',
            'penjualan.create',
            'penjualan.update',
            'penjualan.delete',
            'bursa.view',
            'bursa.create',
            'bursa.update',
            'bursa.delete',
        ]);
    }
}
