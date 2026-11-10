<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_departments_table_schema_and_model_persistence(): void
    {
        $this->assertTrue(Schema::hasTable('departments'));
        $this->assertTrue(Schema::hasColumns('departments', [
            'id',
            'code',
            'name',
            'description',
            'created_at',
            'updated_at',
        ]));

        $department = Department::create([
            'code' => 'DEP-PROD',
            'name' => 'Produksi Sirkular',
            'description' => 'Departemen pengelolaan lini produksi ramah lingkungan.',
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'code' => 'DEP-PROD',
            'name' => 'Produksi Sirkular',
        ]);

        $nullableDept = Department::create([
            'code' => 'DEP-GUD',
            'name' => 'Gudang Material',
        ]);

        $this->assertNull($nullableDept->fresh()->description);
    }

    public function test_department_code_must_be_unique_at_database_level(): void
    {
        Department::create([
            'code' => 'DEP-HR',
            'name' => 'SDM & Organisasi',
        ]);

        $this->expectException(QueryException::class);

        Department::create([
            'code' => 'DEP-HR',
            'name' => 'Duplikat SDM',
        ]);
    }

    public function test_department_searchable_and_filter_scopes_work(): void
    {
        Department::create([
            'code' => 'DEP-HR',
            'name' => 'SDM & Organisasi',
            'description' => 'Mengelola karyawan dan absensi.',
        ]);
        Department::create([
            'code' => 'DEP-PROD',
            'name' => 'Produksi Sirkular',
            'description' => 'Mengelola manufaktur kayu FSC.',
        ]);

        $searchResults = Department::search('Sirkular', ['code', 'name', 'description'])->get();
        $this->assertCount(1, $searchResults);
        $this->assertSame('DEP-PROD', $searchResults->first()->code);

        $filteredResults = Department::filter(['code' => 'DEP-HR'])->get();
        $this->assertCount(1, $filteredResults);
        $this->assertSame('SDM & Organisasi', $filteredResults->first()->name);
    }

    public function test_department_routes_require_authentication_and_permissions(): void
    {
        $department = Department::create([
            'code' => 'DEP-HR',
            'name' => 'SDM & Organisasi',
        ]);

        $this->get('/hr/departemen')->assertRedirect('/login');
        $this->get('/hr/departemen/create')->assertRedirect('/login');
        $this->post('/hr/departemen', [])->assertRedirect('/login');

        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('penjualan');
        $this->actingAs($unauthorizedUser)->get('/hr/departemen')->assertForbidden();
        $this->get('/hr/departemen/create')->assertForbidden();
        $this->post('/hr/departemen', [])->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('hr.view');
        $this->actingAs($viewer)->get('/hr/departemen')->assertOk();
        $this->get("/hr/departemen/{$department->id}")->assertOk();
        $this->get('/hr/departemen/create')->assertForbidden();
        $this->post('/hr/departemen', [
            'code' => 'DEP-NEW',
            'name' => 'Baru',
        ])->assertForbidden();
        $this->get("/hr/departemen/{$department->id}/edit")->assertForbidden();
        $this->put("/hr/departemen/{$department->id}", [
            'code' => 'DEP-HR',
            'name' => 'Ubah',
        ])->assertForbidden();
        $this->delete("/hr/departemen/{$department->id}")->assertForbidden();
    }

    public function test_authorized_user_can_create_view_search_update_and_delete_department(): void
    {
        $hrUser = $this->hrUser();
        $this->actingAs($hrUser);

        $this->get('/hr/departemen/create')->assertOk()
            ->assertSee('Tambah Departemen');

        $this->post('/hr/departemen', [
            'code' => 'DEP-QC',
            'name' => 'Kontrol Kualitas',
            'description' => 'Pemeriksaan mutu produk sirkular.',
        ])->assertRedirect('/hr/departemen');

        $department = Department::where('code', 'DEP-QC')->firstOrFail();

        $this->get('/hr/departemen?cari=Kualitas')->assertOk()
            ->assertSee('DEP-QC')
            ->assertSee('Kontrol Kualitas');

        $this->get("/hr/departemen/{$department->id}")->assertOk()
            ->assertSee('DEP-QC')
            ->assertSee('Pemeriksaan mutu produk sirkular.');

        $this->get("/hr/departemen/{$department->id}/edit")->assertOk()
            ->assertSee('value="DEP-QC"', false);

        $this->put("/hr/departemen/{$department->id}", [
            'code' => 'DEP-QA',
            'name' => 'Jaminan Mutu',
            'description' => 'Audit kualitas dan standar lingkungan.',
        ])->assertRedirect('/hr/departemen');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'code' => 'DEP-QA',
            'name' => 'Jaminan Mutu',
        ]);

        $this->delete("/hr/departemen/{$department->id}")
            ->assertRedirect('/hr/departemen');

        $this->assertDatabaseMissing('departments', [
            'id' => $department->id,
        ]);
    }

    public function test_store_and_update_validate_required_fields_and_unique_code(): void
    {
        $this->actingAs($this->hrUser());

        $first = Department::create([
            'code' => 'DEP-01',
            'name' => 'Departemen Satu',
        ]);
        $second = Department::create([
            'code' => 'DEP-02',
            'name' => 'Departemen Dua',
        ]);

        $this->from('/hr/departemen/create')
            ->post('/hr/departemen', [
                'code' => '',
                'name' => '',
            ])
            ->assertRedirect('/hr/departemen/create')
            ->assertSessionHasErrors(['code', 'name']);

        $this->from('/hr/departemen/create')
            ->post('/hr/departemen', [
                'code' => 'DEP-01',
                'name' => 'Duplikat Kode',
            ])
            ->assertRedirect('/hr/departemen/create')
            ->assertSessionHasErrors(['code']);

        $this->put("/hr/departemen/{$second->id}", [
            'code' => 'DEP-01',
            'name' => 'Konflik Kode',
        ])->assertSessionHasErrors(['code']);

        $this->put("/hr/departemen/{$first->id}", [
            'code' => 'DEP-01',
            'name' => 'Departemen Satu Diperbarui',
            'description' => 'Tetap memakai kode yang sama.',
        ])->assertRedirect('/hr/departemen');

        $this->assertDatabaseHas('departments', [
            'id' => $first->id,
            'name' => 'Departemen Satu Diperbarui',
        ]);
    }

    public function test_department_referenced_by_employees_cannot_be_deleted(): void
    {
        $this->actingAs($this->hrUser());

        $department = Department::create([
            'code' => 'DEP-PROD',
            'name' => 'Produksi Sirkular',
        ]);

        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_id');
                $table->string('employee_code')->nullable();
                $table->string('name')->nullable();
                $table->string('position')->nullable();
                $table->date('join_date')->nullable();
            });
        }

        DB::table('employees')->insert([
            'id' => 1,
            'department_id' => $department->id,
            'employee_code' => 'EMP-UJI-01',
            'name' => 'Karyawan Uji',
            'position' => 'Operator Produksi',
            'join_date' => '2026-11-09',
        ]);

        $this->delete("/hr/departemen/{$department->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
        ]);
    }

    private function hrUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('hr');

        return $user;
    }
}
