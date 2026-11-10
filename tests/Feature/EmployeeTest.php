<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_employees_table_schema_model_persistence_and_relations(): void
    {
        $this->assertTrue(Schema::hasTable('employees'));
        $this->assertTrue(Schema::hasColumns('employees', [
            'id',
            'department_id',
            'user_id',
            'employee_code',
            'name',
            'position',
            'phone',
            'address',
            'join_date',
            'status',
            'photo_path',
            'created_at',
            'updated_at',
        ]));

        $department = Department::create([
            'code' => 'DEP-HR',
            'name' => 'SDM & Organisasi',
        ]);
        $user = User::factory()->create();

        $employee = Employee::create([
            'department_id' => $department->id,
            'user_id' => $user->id,
            'employee_code' => 'EMP-2026-001',
            'name' => 'Siti Rahmawati',
            'position' => 'Spesialis SDM',
            'phone' => '081234567890',
            'address' => 'Jl. Industri Hijau No. 10, Bandung',
            'join_date' => '2026-11-05',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'department_id' => $department->id,
            'user_id' => $user->id,
            'employee_code' => 'EMP-2026-001',
            'name' => 'Siti Rahmawati',
            'status' => 'active',
        ]);

        $this->assertSame($department->id, $employee->department->id);
        $this->assertSame($user->id, $employee->user->id);
        $this->assertSame('2026-11-05', $employee->join_date->format('Y-m-d'));
        $this->assertCount(1, $department->employees);
    }

    public function test_employee_code_and_user_id_must_be_unique_at_database_level(): void
    {
        $department = Department::create([
            'code' => 'DEP-PROD',
            'name' => 'Produksi Sirkular',
        ]);
        $user = User::factory()->create();

        Employee::create([
            'department_id' => $department->id,
            'user_id' => $user->id,
            'employee_code' => 'EMP-UNQ-01',
            'name' => 'Karyawan Pertama',
            'position' => 'Operator',
            'join_date' => '2026-11-05',
            'status' => 'active',
        ]);

        $this->expectException(QueryException::class);

        Employee::create([
            'department_id' => $department->id,
            'employee_code' => 'EMP-UNQ-01',
            'name' => 'Karyawan Duplikat',
            'position' => 'Operator',
            'join_date' => '2026-11-06',
            'status' => 'active',
        ]);
    }

    public function test_employee_searchable_and_filter_scopes_work(): void
    {
        $hrDept = Department::create([
            'code' => 'DEP-HR',
            'name' => 'SDM & Organisasi',
        ]);
        $prodDept = Department::create([
            'code' => 'DEP-PROD',
            'name' => 'Produksi Sirkular',
        ]);

        Employee::create([
            'department_id' => $hrDept->id,
            'employee_code' => 'EMP-HR-01',
            'name' => 'Siti Rahmawati',
            'position' => 'Manajer SDM',
            'join_date' => '2026-11-01',
            'status' => 'active',
        ]);

        Employee::create([
            'department_id' => $prodDept->id,
            'employee_code' => 'EMP-PR-02',
            'name' => 'Eko Prasetyo',
            'position' => 'Supervisor Produksi',
            'join_date' => '2026-11-02',
            'status' => 'inactive',
        ]);

        $searchColumns = ['employee_code', 'name', 'position', 'phone', 'department.name'];
        $searchResults = Employee::search('Sirkular', $searchColumns)->get();
        $this->assertCount(1, $searchResults);
        $this->assertSame('EMP-PR-02', $searchResults->first()->employee_code);

        $filteredResults = Employee::filter([
            'department_id' => $hrDept->id,
            'status' => 'active',
        ])->get();
        $this->assertCount(1, $filteredResults);
        $this->assertSame('EMP-HR-01', $filteredResults->first()->employee_code);
    }

    public function test_employee_routes_require_authentication_and_permissions(): void
    {
        $department = Department::create([
            'code' => 'DEP-HR',
            'name' => 'SDM & Organisasi',
        ]);
        $employee = Employee::create([
            'department_id' => $department->id,
            'employee_code' => 'EMP-AUTH-01',
            'name' => 'Ahmad Karyawan',
            'position' => 'Staf Administrasi',
            'join_date' => '2026-11-05',
            'status' => 'active',
        ]);

        $this->get('/hr/karyawan')->assertRedirect('/login');
        $this->get('/hr/karyawan/create')->assertRedirect('/login');
        $this->post('/hr/karyawan', [])->assertRedirect('/login');

        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('penjualan');
        $this->actingAs($unauthorizedUser)->get('/hr/karyawan')->assertForbidden();
        $this->get('/hr/karyawan/create')->assertForbidden();
        $this->post('/hr/karyawan', [])->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('hr.view');
        $this->actingAs($viewer)->get('/hr/karyawan')->assertOk();
        $this->get("/hr/karyawan/{$employee->id}")->assertOk();
        $this->get('/hr/karyawan/create')->assertForbidden();
        $this->post('/hr/karyawan', [
            'department_id' => $department->id,
            'employee_code' => 'EMP-NEW',
            'name' => 'Baru',
            'position' => 'Staf',
            'join_date' => '2026-11-09',
            'status' => 'active',
        ])->assertForbidden();
        $this->get("/hr/karyawan/{$employee->id}/edit")->assertForbidden();
        $this->put("/hr/karyawan/{$employee->id}", [
            'department_id' => $department->id,
            'employee_code' => 'EMP-AUTH-01',
            'name' => 'Ubah',
            'position' => 'Staf',
            'join_date' => '2026-11-09',
            'status' => 'active',
        ])->assertForbidden();
        $this->delete("/hr/karyawan/{$employee->id}")->assertForbidden();
    }

    public function test_authorized_user_can_create_view_search_update_and_delete_employee_with_photo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->hrUser());

        $department = Department::create([
            'code' => 'DEP-QC',
            'name' => 'Kontrol Kualitas',
        ]);
        $linkedUser = User::factory()->create([
            'name' => 'Akun Rina',
            'email' => 'rina.qc@ecofab.local',
        ]);

        $this->get('/hr/karyawan/create')->assertOk()
            ->assertSee('Tambah Karyawan')
            ->assertSee('enctype="multipart/form-data"', false);

        $this->post('/hr/karyawan', [
            'department_id' => $department->id,
            'user_id' => $linkedUser->id,
            'employee_code' => 'EMP-QC-10',
            'name' => 'Rina Kusuma',
            'position' => 'Inspektur Mutu',
            'phone' => '081298765432',
            'address' => 'Jl. Merdeka No. 45, Semarang',
            'join_date' => '2026-11-09',
            'status' => 'active',
            'photo' => UploadedFile::fake()->image('rina.jpg'),
        ])->assertRedirect('/hr/karyawan');

        $employee = Employee::where('employee_code', 'EMP-QC-10')->firstOrFail();
        $oldPhoto = $employee->photo_path;
        $this->assertNotNull($oldPhoto);
        Storage::disk('public')->assertExists($oldPhoto);

        $this->get("/hr/karyawan?cari=Rina&department_id={$department->id}&status=active")
            ->assertOk()
            ->assertSee('EMP-QC-10')
            ->assertSee('Rina Kusuma')
            ->assertSee('Kontrol Kualitas');

        $this->get("/hr/karyawan/{$employee->id}")->assertOk()
            ->assertSee('EMP-QC-10')
            ->assertSee('Inspektur Mutu')
            ->assertSee('rina.qc@ecofab.local')
            ->assertSee(Storage::disk('public')->url($oldPhoto));

        $this->get("/hr/karyawan/{$employee->id}/edit")->assertOk()
            ->assertSee('value="EMP-QC-10"', false);

        $this->put("/hr/karyawan/{$employee->id}", [
            'department_id' => $department->id,
            'user_id' => $linkedUser->id,
            'employee_code' => 'EMP-QC-11',
            'name' => 'Rina Kusuma Dewi',
            'position' => 'Koordinator QC',
            'phone' => '081298765499',
            'address' => 'Jl. Pemuda No. 12, Semarang',
            'join_date' => '2026-11-09',
            'status' => 'inactive',
            'photo' => UploadedFile::fake()->image('rina-baru.png'),
        ])->assertRedirect('/hr/karyawan');

        $employee->refresh();
        $this->assertSame('EMP-QC-11', $employee->employee_code);
        $this->assertSame('Rina Kusuma Dewi', $employee->name);
        $this->assertSame('inactive', $employee->status);
        $this->assertNotSame($oldPhoto, $employee->photo_path);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($employee->photo_path);

        $newPhoto = $employee->photo_path;
        $this->delete("/hr/karyawan/{$employee->id}")
            ->assertRedirect('/hr/karyawan');

        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);
        Storage::disk('public')->assertMissing($newPhoto);
    }

    public function test_store_and_update_validate_required_fields_foreign_keys_unique_constraints_and_photo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->hrUser());

        $department = Department::create([
            'code' => 'DEP-01',
            'name' => 'Departemen Satu',
        ]);
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $first = Employee::create([
            'department_id' => $department->id,
            'user_id' => $userOne->id,
            'employee_code' => 'EMP-01',
            'name' => 'Karyawan Satu',
            'position' => 'Staf',
            'join_date' => '2026-11-01',
            'status' => 'active',
        ]);

        $second = Employee::create([
            'department_id' => $department->id,
            'user_id' => $userTwo->id,
            'employee_code' => 'EMP-02',
            'name' => 'Karyawan Dua',
            'position' => 'Staf',
            'join_date' => '2026-11-02',
            'status' => 'active',
        ]);

        $this->from('/hr/karyawan/create')
            ->post('/hr/karyawan', [
                'department_id' => 9999,
                'user_id' => 9999,
                'employee_code' => '',
                'name' => '',
                'position' => '',
                'join_date' => 'bukan-tanggal',
                'status' => 'invalid',
                'photo' => UploadedFile::fake()->image('besar.jpg')->size(2049),
            ])
            ->assertRedirect('/hr/karyawan/create')
            ->assertSessionHasErrors([
                'department_id',
                'user_id',
                'employee_code',
                'name',
                'position',
                'join_date',
                'status',
                'photo',
            ]);

        $this->put("/hr/karyawan/{$second->id}", [
            'department_id' => $department->id,
            'user_id' => $userOne->id,
            'employee_code' => 'EMP-01',
            'name' => 'Konflik Unik',
            'position' => 'Staf',
            'join_date' => '2026-11-02',
            'status' => 'active',
        ])->assertSessionHasErrors(['employee_code', 'user_id']);

        $this->put("/hr/karyawan/{$first->id}", [
            'department_id' => $department->id,
            'user_id' => $userOne->id,
            'employee_code' => 'EMP-01',
            'name' => 'Karyawan Satu Diperbarui',
            'position' => 'Staf Senior',
            'join_date' => '2026-11-01',
            'status' => 'active',
        ])->assertRedirect('/hr/karyawan');

        $this->assertDatabaseHas('employees', [
            'id' => $first->id,
            'name' => 'Karyawan Satu Diperbarui',
            'position' => 'Staf Senior',
        ]);
    }

    public function test_employee_referenced_by_attendances_cannot_be_deleted(): void
    {
        Storage::fake('public');
        $this->actingAs($this->hrUser());

        $department = Department::create([
            'code' => 'DEP-PROD',
            'name' => 'Produksi Sirkular',
        ]);

        $employee = Employee::create([
            'department_id' => $department->id,
            'employee_code' => 'EMP-ATT-01',
            'name' => 'Bambang Operator',
            'position' => 'Operator Mesin',
            'join_date' => '2026-11-05',
            'status' => 'active',
        ]);

        if (! Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('work_date')->nullable();
                $table->string('shift')->nullable();
                $table->string('status')->nullable();
            });
        }

        DB::table('attendances')->insert([
            'id' => 1,
            'employee_id' => $employee->id,
            'work_date' => '2026-11-09',
            'shift' => 'Pagi',
            'status' => 'hadir',
        ]);

        $this->get("/hr/karyawan/{$employee->id}")
            ->assertOk()
            ->assertSee('Pagi');

        $this->delete("/hr/karyawan/{$employee->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
        ]);
    }

    private function hrUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('hr');

        return $user;
    }
}
