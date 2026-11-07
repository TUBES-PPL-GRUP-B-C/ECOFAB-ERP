<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_suppliers_table_schema_model_persistence_and_origin_location(): void
    {
        $this->assertTrue(Schema::hasTable('suppliers'));
        $this->assertTrue(Schema::hasColumns('suppliers', [
            'id',
            'code',
            'name',
            'contact_person',
            'phone',
            'email',
            'address',
            'city',
            'province',
            'country',
            'status',
            'created_at',
            'updated_at',
        ]));

        $supplier = Supplier::create([
            'code' => 'SUP-JPR-01',
            'name' => 'CV Kayu Lestari Jepara',
            'contact_person' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'kontak@kayulestari.co.id',
            'address' => 'Jl. Raya Tahunan KM 5, Jepara',
            'city' => 'Jepara',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'code' => 'SUP-JPR-01',
            'name' => 'CV Kayu Lestari Jepara',
            'city' => 'Jepara',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'active',
        ]);

        $this->assertSame('Jepara, Jawa Tengah, Indonesia', $supplier->origin_location);

        $minimalSupplier = Supplier::create([
            'code' => 'SUP-BDG-02',
            'name' => 'PT Serat Bambu Nusantara',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
        ]);

        $freshMinimal = $minimalSupplier->fresh();
        $this->assertNull($freshMinimal->contact_person);
        $this->assertSame('Indonesia', $freshMinimal->country);
        $this->assertSame('active', $freshMinimal->status);
    }

    public function test_supplier_code_must_be_unique_at_database_level(): void
    {
        Supplier::create([
            'code' => 'SUP-UNQ-01',
            'name' => 'Pemasok Utama',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'country' => 'Indonesia',
        ]);

        $this->expectException(QueryException::class);

        Supplier::create([
            'code' => 'SUP-UNQ-01',
            'name' => 'Pemasok Duplikat',
            'city' => 'Malang',
            'province' => 'Jawa Timur',
            'country' => 'Indonesia',
        ]);
    }

    public function test_supplier_searchable_and_filter_scopes_work(): void
    {
        Supplier::create([
            'code' => 'SUP-JPR-01',
            'name' => 'CV Kayu Lestari Jepara',
            'contact_person' => 'Budi Santoso',
            'city' => 'Jepara',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'active',
        ]);

        Supplier::create([
            'code' => 'SUP-CRB-02',
            'name' => 'UD Rotan Alam Cirebon',
            'contact_person' => 'Siti Aminah',
            'city' => 'Cirebon',
            'province' => 'Jawa Barat',
            'country' => 'Indonesia',
            'status' => 'inactive',
        ]);

        $searchColumns = ['code', 'name', 'contact_person', 'city', 'province', 'country'];
        $searchResults = Supplier::search('Cirebon', $searchColumns)->get();

        $this->assertCount(1, $searchResults);
        $this->assertSame('SUP-CRB-02', $searchResults->first()->code);

        $filteredResults = Supplier::filter(['status' => 'active'])->get();
        $this->assertCount(1, $filteredResults);
        $this->assertSame('SUP-JPR-01', $filteredResults->first()->code);
    }

    public function test_supplier_routes_require_authentication_and_permissions(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-AUTH-01',
            'name' => 'PT Bahan Alami',
            'city' => 'Semarang',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'active',
        ]);

        $this->get('/pembelian/supplier')->assertRedirect('/login');
        $this->get('/pembelian/supplier/create')->assertRedirect('/login');
        $this->post('/pembelian/supplier', [])->assertRedirect('/login');

        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('penjualan');
        $this->actingAs($unauthorizedUser)->get('/pembelian/supplier')->assertForbidden();
        $this->get('/pembelian/supplier/create')->assertForbidden();
        $this->post('/pembelian/supplier', [])->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('pembelian.view');
        $this->actingAs($viewer)->get('/pembelian/supplier')->assertOk();
        $this->get("/pembelian/supplier/{$supplier->id}")->assertOk();
        $this->get('/pembelian/supplier/create')->assertForbidden();
        $this->post('/pembelian/supplier', [
            'code' => 'SUP-NEW',
            'name' => 'Baru',
            'city' => 'Bogor',
            'province' => 'Jawa Barat',
            'country' => 'Indonesia',
            'status' => 'active',
        ])->assertForbidden();
        $this->get("/pembelian/supplier/{$supplier->id}/edit")->assertForbidden();
        $this->put("/pembelian/supplier/{$supplier->id}", [
            'code' => 'SUP-AUTH-01',
            'name' => 'Ubah',
            'city' => 'Semarang',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'active',
        ])->assertForbidden();
        $this->delete("/pembelian/supplier/{$supplier->id}")->assertForbidden();
    }

    public function test_authorized_user_can_create_view_search_filter_update_and_delete_supplier(): void
    {
        $this->actingAs($this->pembelianUser());

        $this->get('/pembelian/supplier/create')->assertOk()
            ->assertSee('Tambah Supplier');

        $this->post('/pembelian/supplier', [
            'code' => 'SUP-JPR-10',
            'name' => 'PT Jati Sirkular Jepara',
            'contact_person' => 'Rina Wijaya',
            'phone' => '081122334455',
            'email' => 'rina@jatisirkular.id',
            'address' => 'Kawasan Industri Mulyoharjo, Jepara',
            'city' => 'Jepara',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'active',
        ])->assertRedirect('/pembelian/supplier');

        $supplier = Supplier::where('code', 'SUP-JPR-10')->firstOrFail();

        $this->get('/pembelian/supplier?cari=Jepara&status=active')->assertOk()
            ->assertSee('SUP-JPR-10')
            ->assertSee('PT Jati Sirkular Jepara')
            ->assertSee('Jepara, Jawa Tengah, Indonesia');

        $this->get("/pembelian/supplier/{$supplier->id}")->assertOk()
            ->assertSee('SUP-JPR-10')
            ->assertSee('Rina Wijaya')
            ->assertSee('Jepara, Jawa Tengah, Indonesia');

        $this->get("/pembelian/supplier/{$supplier->id}/edit")->assertOk()
            ->assertSee('value="SUP-JPR-10"', false);

        $this->put("/pembelian/supplier/{$supplier->id}", [
            'code' => 'SUP-KDS-10',
            'name' => 'PT Jati Sirkular Kudus',
            'contact_person' => 'Rina Wijaya',
            'phone' => '081122334466',
            'email' => 'rina@jatisirkular.id',
            'address' => 'Jl. Lingkar Timur Kudus',
            'city' => 'Kudus',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'inactive',
        ])->assertRedirect('/pembelian/supplier');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'code' => 'SUP-KDS-10',
            'name' => 'PT Jati Sirkular Kudus',
            'city' => 'Kudus',
            'status' => 'inactive',
        ]);

        $this->delete("/pembelian/supplier/{$supplier->id}")
            ->assertRedirect('/pembelian/supplier');

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_store_and_update_validate_required_origin_fields_email_and_unique_code(): void
    {
        $this->actingAs($this->pembelianUser());

        $first = Supplier::create([
            'code' => 'SUP-01',
            'name' => 'Supplier Satu',
            'city' => 'Solo',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'active',
        ]);

        $second = Supplier::create([
            'code' => 'SUP-02',
            'name' => 'Supplier Dua',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'country' => 'Indonesia',
            'status' => 'active',
        ]);

        $this->from('/pembelian/supplier/create')
            ->post('/pembelian/supplier', [
                'code' => '',
                'name' => '',
                'email' => 'format-email-salah',
                'city' => '',
                'province' => '',
                'country' => '',
                'status' => 'invalid_status',
            ])
            ->assertRedirect('/pembelian/supplier/create')
            ->assertSessionHasErrors([
                'code',
                'name',
                'email',
                'city',
                'province',
                'country',
                'status',
            ]);

        $this->from('/pembelian/supplier/create')
            ->post('/pembelian/supplier', [
                'code' => 'SUP-01',
                'name' => 'Duplikat Kode',
                'city' => 'Solo',
                'province' => 'Jawa Tengah',
                'country' => 'Indonesia',
                'status' => 'active',
            ])
            ->assertRedirect('/pembelian/supplier/create')
            ->assertSessionHasErrors(['code']);

        $this->put("/pembelian/supplier/{$second->id}", [
            'code' => 'SUP-01',
            'name' => 'Konflik Kode Supplier',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'country' => 'Indonesia',
            'status' => 'active',
        ])->assertSessionHasErrors(['code']);

        $this->put("/pembelian/supplier/{$first->id}", [
            'code' => 'SUP-01',
            'name' => 'Supplier Satu Diperbarui',
            'city' => 'Surakarta',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'status' => 'active',
        ])->assertRedirect('/pembelian/supplier');

        $this->assertDatabaseHas('suppliers', [
            'id' => $first->id,
            'name' => 'Supplier Satu Diperbarui',
            'city' => 'Surakarta',
        ]);
    }

    public function test_supplier_referenced_by_purchase_orders_cannot_be_deleted(): void
    {
        $this->actingAs($this->pembelianUser());

        $supplier = Supplier::create([
            'code' => 'SUP-PO-01',
            'name' => 'CV Bambu Lestari',
            'city' => 'Tasikmalaya',
            'province' => 'Jawa Barat',
            'country' => 'Indonesia',
            'status' => 'active',
        ]);

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('po_number')->nullable();
            $table->string('order_date')->nullable();
            $table->string('status')->nullable();
        });

        DB::table('purchase_orders')->insert([
            'id' => 1,
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-202611-001',
            'order_date' => '2026-11-16',
            'status' => 'draft',
        ]);

        $this->get("/pembelian/supplier/{$supplier->id}")
            ->assertOk()
            ->assertSee('PO-202611-001');

        $this->delete("/pembelian/supplier/{$supplier->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    private function pembelianUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('pembelian');

        return $user;
    }
}
