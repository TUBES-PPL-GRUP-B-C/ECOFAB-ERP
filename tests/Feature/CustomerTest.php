<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customers_table_schema_and_unique_code(): void
    {
        $this->assertTrue(Schema::hasColumns('customers', [
            'id', 'code', 'name', 'address', 'phone', 'email', 'created_at', 'updated_at',
        ]));

        Customer::create(['code' => 'CUST-001', 'name' => 'PT Mebel Hijau']);

        $this->expectException(QueryException::class);
        Customer::create(['code' => 'CUST-001', 'name' => 'Duplikat']);
    }

    public function test_guest_is_redirected_and_unauthorized_role_is_forbidden(): void
    {
        $customer = Customer::create(['code' => 'CUST-AUTH', 'name' => 'Customer Auth']);

        $this->get('/penjualan/customer')->assertRedirect('/login');
        $this->post('/penjualan/customer', [])->assertRedirect('/login');

        $gudang = User::factory()->create();
        $gudang->assignRole('gudang');
        $this->actingAs($gudang);
        $this->get('/penjualan/customer')->assertForbidden();
        $this->get("/penjualan/customer/{$customer->id}")->assertForbidden();
        $this->post('/penjualan/customer', [])->assertForbidden();
        $this->delete("/penjualan/customer/{$customer->id}")->assertForbidden();
    }

    public function test_view_only_user_cannot_create_update_or_delete(): void
    {
        $customer = Customer::create(['code' => 'CUST-VIEW', 'name' => 'Customer View']);

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('penjualan.view');
        $this->actingAs($viewer);

        $this->get('/penjualan/customer')->assertOk()->assertSee('CUST-VIEW');
        $this->get("/penjualan/customer/{$customer->id}")->assertOk();
        $this->get('/penjualan/customer/create')->assertForbidden();
        $this->post('/penjualan/customer', ['code' => 'X', 'name' => 'X'])->assertForbidden();
        $this->get("/penjualan/customer/{$customer->id}/edit")->assertForbidden();
        $this->put("/penjualan/customer/{$customer->id}", ['code' => 'X', 'name' => 'X'])->assertForbidden();
        $this->delete("/penjualan/customer/{$customer->id}")->assertForbidden();
    }

    public function test_sales_staff_can_crud_and_search_customer(): void
    {
        $this->actingAs($this->salesUser());

        $this->get('/penjualan/customer/create')->assertOk()->assertSee('Tambah Customer');

        $this->post('/penjualan/customer', [
            'code' => 'CUST-010',
            'name' => 'PT Rumah Kayu Lestari',
            'address' => 'Jl. Merdeka 10, Bandung',
            'phone' => '081200001111',
            'email' => 'order@rumahkayu.id',
        ])->assertRedirect('/penjualan/customer');

        $customer = Customer::where('code', 'CUST-010')->firstOrFail();

        $this->get('/penjualan/customer?cari=Rumah Kayu')->assertOk()
            ->assertSee('CUST-010')
            ->assertSee('PT Rumah Kayu Lestari');

        $this->get('/penjualan/customer?cari=tidakada')->assertOk()
            ->assertDontSee('CUST-010');

        $this->get("/penjualan/customer/{$customer->id}")->assertOk()
            ->assertSee('order@rumahkayu.id');

        $this->get("/penjualan/customer/{$customer->id}/edit")->assertOk()
            ->assertSee('value="CUST-010"', false);

        $this->put("/penjualan/customer/{$customer->id}", [
            'code' => 'CUST-010',
            'name' => 'PT Rumah Kayu Lestari Abadi',
            'address' => 'Jl. Asia Afrika 20, Bandung',
            'phone' => '081200002222',
            'email' => '',
        ])->assertRedirect('/penjualan/customer');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'PT Rumah Kayu Lestari Abadi',
            'address' => 'Jl. Asia Afrika 20, Bandung',
            'phone' => '081200002222',
            'email' => null,
        ]);

        $this->delete("/penjualan/customer/{$customer->id}")->assertRedirect('/penjualan/customer');
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_validation_rejects_invalid_and_duplicate_input(): void
    {
        $this->actingAs($this->salesUser());

        $first = Customer::create(['code' => 'CUST-A', 'name' => 'Customer A']);
        $second = Customer::create(['code' => 'CUST-B', 'name' => 'Customer B']);

        $this->from('/penjualan/customer/create')
            ->post('/penjualan/customer', [
                'code' => '',
                'name' => '',
                'email' => 'bukan-email',
            ])
            ->assertRedirect('/penjualan/customer/create')
            ->assertSessionHasErrors(['code', 'name', 'email']);

        $this->post('/penjualan/customer', [
            'code' => str_repeat('X', 21),
            'name' => 'Kode Kepanjangan',
        ])->assertSessionHasErrors(['code']);

        $this->post('/penjualan/customer', ['code' => 'CUST-A', 'name' => 'Duplikat'])
            ->assertSessionHasErrors(['code']);

        $this->put("/penjualan/customer/{$second->id}", ['code' => 'CUST-A', 'name' => 'Konflik'])
            ->assertSessionHasErrors(['code']);

        // Update dengan kode sendiri tetap valid
        $this->put("/penjualan/customer/{$first->id}", ['code' => 'CUST-A', 'name' => 'Customer A Baru'])
            ->assertSessionHasNoErrors();
    }

    public function test_customer_with_sales_order_cannot_be_deleted(): void
    {
        $user = $this->salesUser();
        $this->actingAs($user);

        $customer = Customer::create(['code' => 'CUST-SO', 'name' => 'Customer Ber-SO']);
        SalesOrder::create([
            'so_number' => 'SO-TEST-001',
            'customer_id' => $customer->id,
            'order_date' => '2026-11-22',
            'status' => 'draft',
            'total_amount' => 0,
            'created_by' => $user->id,
        ]);

        $this->delete("/penjualan/customer/{$customer->id}")
            ->assertRedirect('/penjualan/customer')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);

        $this->get("/penjualan/customer/{$customer->id}")->assertOk()->assertSee('SO-TEST-001');
    }

    public function test_missing_customer_returns_404(): void
    {
        $this->actingAs($this->salesUser());

        $this->get('/penjualan/customer/99999')->assertNotFound();
        $this->delete('/penjualan/customer/99999')->assertNotFound();
    }

    public function test_customer_seeder_is_idempotent(): void
    {
        $this->seed(CustomerSeeder::class);
        $this->seed(CustomerSeeder::class);

        $this->assertDatabaseCount('customers', 5);
        $this->assertDatabaseHas('customers', ['code' => 'CUST-001']);
    }

    private function salesUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('penjualan');

        return $user;
    }
}
