<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(UserSeeder::class);
    }

    public function test_user_without_permission_receives_403_when_accessing_restricted_route(): void
    {
        // Staf penjualan tidak punya izin hr.view
        $penjualanUser = User::where('email', 'penjualan@ecofab.local')->first();

        $response = $this->actingAs($penjualanUser)->get('/hr/departemen');

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
    }

    public function test_authorized_user_can_access_their_module(): void
    {
        $hrUser = User::where('email', 'hr@ecofab.local')->first();

        $response = $this->actingAs($hrUser)->get('/hr/departemen');

        $response->assertStatus(200);
        $response->assertSee('Kelola Departemen (FR-03)');
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::where('email', 'admin@ecofab.local')->first();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna & Peran');
        $response->assertSee('admin@ecofab.local');
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $produksiUser = User::where('email', 'produksi@ecofab.local')->first();

        $response = $this->actingAs($produksiUser)->get('/admin/users');

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
    }

    public function test_admin_can_create_new_user_and_assign_role(): void
    {
        $admin = User::where('email', 'admin@ecofab.local')->first();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Karyawan Baru',
            'email' => 'karyawan.baru@ecofab.local',
            'password' => 'password123',
            'role' => 'gudang',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => 'karyawan.baru@ecofab.local']);

        $newUser = User::where('email', 'karyawan.baru@ecofab.local')->first();
        $this->assertTrue($newUser->hasRole('gudang'));
    }

    public function test_sidebar_menu_hides_unauthorized_items(): void
    {
        // Staf HR seharusnya melihat menu Departemen tapi tidak melihat Faktor Emisi
        $hrUser = User::where('email', 'hr@ecofab.local')->first();

        $response = $this->actingAs($hrUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Departemen');
        $response->assertDontSee('Faktor Emisi (CO₂e)');
    }

    public function test_role_simulation_endpoint_switches_active_user(): void
    {
        $admin = User::where('email', 'admin@ecofab.local')->first();

        $response = $this->actingAs($admin)->get('/simulasi-peran/produksi');

        $this->assertAuthenticated();
        $this->assertEquals('produksi@ecofab.local', auth()->user()->email);
    }
}
