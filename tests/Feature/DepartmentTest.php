<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_department_index_route_requires_hr_view_permission(): void
    {
        $this->get('/hr/departemen')->assertRedirect('/login');

        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('penjualan');
        $this->actingAs($unauthorizedUser)->get('/hr/departemen')->assertForbidden();

        $hrUser = User::factory()->create();
        $hrUser->assignRole('hr');
        $this->actingAs($hrUser)->get('/hr/departemen')->assertOk();
    }
}
