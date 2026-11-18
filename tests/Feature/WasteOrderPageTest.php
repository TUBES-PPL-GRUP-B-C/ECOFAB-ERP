<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasteOrderPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_cannot_access_internal_orders(): void
    {
        $this->get(route('bursa.order.index'))->assertRedirect(route('login'));
    }

    public function test_internal_order_page_respects_existing_role_permissions(): void
    {
        foreach (['admin', 'penjualan', 'hr', 'pembelian', 'gudang', 'produksi', null] as $role) {
            $user = User::factory()->create();
            if ($role !== null) {
                $user->assignRole($role);
            }

            $response = $this->actingAs($user)->get(route('bursa.order.index'));

            if (in_array($role, ['admin', 'penjualan'], true)) {
                $response->assertOk()->assertViewIs('bursa.order.index');
            } else {
                $response->assertForbidden()->assertDontSee('Detail pesanan belum tersedia');
            }
        }
    }

    public function test_unavailable_orders_render_without_order_or_waste_record_tables_or_write_actions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('bursa.order.index'));

        $response->assertOk()
            ->assertSee('Daftar pesanan belum tersedia')
            ->assertSee('Detail pesanan belum tersedia')
            ->assertSee('Persetujuan pesanan belum tersedia')
            ->assertSee('aria-label="Daftar pesanan limbah"', false)
            ->assertSee(route('bursa.listing.index'), false)
            ->assertDontSee('Belum ada pesanan')
            ->assertDontSee('Pesanan berhasil disetujui');

        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);

        $this->assertSame(1, $xpath->query('//main')->length);
        $this->assertSame(0, $xpath->query('//main//form | //main//button | //main//input')->length);
        $this->assertSame(1, $xpath->query('//main//table[@aria-label="Daftar pesanan limbah"]')->length);
    }

    public function test_approval_availability_notice_requires_existing_approval_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('penjualan');

        $this->actingAs($user)->get(route('bursa.order.index'))
            ->assertOk()
            ->assertSee('Detail pesanan belum tersedia')
            ->assertDontSee('Persetujuan Admin')
            ->assertDontSee('Persetujuan pesanan belum tersedia');

        $user->syncRoles(['admin']);

        $this->get(route('bursa.order.index'))
            ->assertOk()
            ->assertSee('Persetujuan Admin')
            ->assertSee('Persetujuan pesanan belum tersedia');
    }
}
