<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_attendances_table_schema_model_persistence_and_relations(): void
    {
        $this->assertTrue(Schema::hasTable('attendances'));
        $this->assertTrue(Schema::hasColumns('attendances', [
            'id',
            'employee_id',
            'work_date',
            'shift',
            'check_in',
            'check_out',
            'status',
            'notes',
            'created_at',
            'updated_at',
        ]));

        $employee = $this->createEmployee('EMP-ATT-01', 'Budi Operator');

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Pagi',
            'check_in' => '08:00',
            'check_out' => '16:00',
            'status' => 'hadir',
            'notes' => 'Hadir tepat waktu pada shift pagi.',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Pagi',
            'status' => 'hadir',
        ]);

        $this->assertSame($employee->id, $attendance->employee->id);
        $this->assertSame('aktif', $attendance->badge_status);
        $this->assertSame('Hadir', $attendance->status_label);
        $this->assertCount(1, $employee->attendances);
    }

    public function test_attendance_unique_constraint_per_employee_and_work_date_at_database_level(): void
    {
        $employee = $this->createEmployee('EMP-UNQ-01', 'Siti Operator');

        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Pagi',
            'check_in' => '08:00',
            'check_out' => '16:00',
            'status' => 'hadir',
        ]);

        $this->expectException(QueryException::class);

        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Siang',
            'check_in' => '14:00',
            'check_out' => '22:00',
            'status' => 'hadir',
        ]);
    }

    public function test_attendance_searchable_and_filter_scopes_work(): void
    {
        $firstEmployee = $this->createEmployee('EMP-01', 'Rina Wijaya');
        $secondEmployee = $this->createEmployee('EMP-02', 'Dedi Kurniawan', 'DEP-GUD', 'Gudang Material');

        Attendance::create([
            'employee_id' => $firstEmployee->id,
            'work_date' => '2026-11-10',
            'shift' => 'Pagi',
            'check_in' => '07:55',
            'check_out' => '16:00',
            'status' => 'hadir',
        ]);

        Attendance::create([
            'employee_id' => $secondEmployee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Malam',
            'status' => 'sakit',
            'notes' => 'Surat keterangan dokter klinik.',
        ]);

        $searchColumns = ['shift', 'status', 'notes', 'employee.employee_code', 'employee.name'];
        $searchResults = Attendance::search('Dedi', $searchColumns)->get();
        $this->assertCount(1, $searchResults);
        $this->assertSame($secondEmployee->id, $searchResults->first()->employee_id);

        $filteredResults = Attendance::filter([
            'shift' => 'Pagi',
            'status' => 'hadir',
        ])->get();
        $this->assertCount(1, $filteredResults);
        $this->assertSame($firstEmployee->id, $filteredResults->first()->employee_id);
    }

    public function test_attendance_routes_require_authentication_and_hr_permissions(): void
    {
        $employee = $this->createEmployee('EMP-AUTH-01', 'Eko Karyawan');
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Pagi',
            'check_in' => '08:00',
            'check_out' => '16:00',
            'status' => 'hadir',
        ]);

        $this->get('/hr/absensi')->assertRedirect('/login');
        $this->get('/hr/absensi/create')->assertRedirect('/login');
        $this->post('/hr/absensi', [])->assertRedirect('/login');

        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('penjualan');
        $this->actingAs($unauthorizedUser)->get('/hr/absensi')->assertForbidden();
        $this->get('/hr/absensi/create')->assertForbidden();
        $this->post('/hr/absensi', [])->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('hr.view');
        $this->actingAs($viewer)->get('/hr/absensi')->assertOk();
        $this->get("/hr/absensi/{$attendance->id}")->assertOk();
        $this->get('/hr/absensi/create')->assertForbidden();
        $this->post('/hr/absensi', [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-12',
            'shift' => 'Pagi',
            'status' => 'hadir',
        ])->assertForbidden();
        $this->get("/hr/absensi/{$attendance->id}/edit")->assertForbidden();
        $this->put("/hr/absensi/{$attendance->id}", [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Siang',
            'status' => 'hadir',
        ])->assertForbidden();
        $this->delete("/hr/absensi/{$attendance->id}")->assertForbidden();
    }

    public function test_authorized_user_can_create_view_search_update_and_delete_attendance(): void
    {
        $this->actingAs($this->hrUser());
        $employee = $this->createEmployee('EMP-CRUD-01', 'Nadia Putri');

        $this->get('/hr/absensi/create')->assertOk()
            ->assertSee('Catat Absensi')
            ->assertSee('Nadia Putri');

        $this->post('/hr/absensi', [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Pagi',
            'check_in' => '08:00',
            'check_out' => '16:15',
            'status' => 'hadir',
            'notes' => 'Menyelesaikan target lini tenun.',
        ])->assertRedirect('/hr/absensi');

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('work_date', '2026-11-11')
            ->firstOrFail();

        $this->get('/hr/absensi?cari=Nadia&shift=Pagi&status=hadir')
            ->assertOk()
            ->assertSee('Nadia Putri')
            ->assertSee('08:00')
            ->assertSee('16:15');

        $this->get("/hr/absensi/{$attendance->id}")
            ->assertOk()
            ->assertSee('Nadia Putri')
            ->assertSee('Shift Pagi')
            ->assertSee('Menyelesaikan target lini tenun.');

        $this->get("/hr/absensi/{$attendance->id}/edit")
            ->assertOk()
            ->assertSee('value="2026-11-11"', false);

        $this->put("/hr/absensi/{$attendance->id}", [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Siang',
            'check_in' => '14:00',
            'check_out' => '22:00',
            'status' => 'hadir',
            'notes' => 'Perubahan jadwal ke shift siang.',
        ])->assertRedirect('/hr/absensi');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'shift' => 'Siang',
            'check_in' => '14:00',
            'check_out' => '22:00',
        ]);

        $this->delete("/hr/absensi/{$attendance->id}")
            ->assertRedirect('/hr/absensi');

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    public function test_store_and_update_validate_check_out_order_and_duplicate_work_date(): void
    {
        $this->actingAs($this->hrUser());
        $employee = $this->createEmployee('EMP-VAL-01', 'Agus Salim');

        $first = Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-11-10',
            'shift' => 'Pagi',
            'check_in' => '08:00',
            'check_out' => '16:00',
            'status' => 'hadir',
        ]);

        $second = Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-11-11',
            'shift' => 'Pagi',
            'check_in' => '08:00',
            'check_out' => '16:00',
            'status' => 'hadir',
        ]);

        $this->from('/hr/absensi/create')
            ->post('/hr/absensi', [
                'employee_id' => $employee->id,
                'work_date' => '2026-11-12',
                'shift' => 'Pagi',
                'check_in' => '10:00',
                'check_out' => '08:30',
                'status' => 'hadir',
            ])
            ->assertRedirect('/hr/absensi/create')
            ->assertSessionHasErrors(['check_out']);

        $this->from('/hr/absensi/create')
            ->post('/hr/absensi', [
                'employee_id' => $employee->id,
                'work_date' => '2026-11-12',
                'shift' => 'Pagi',
                'check_in' => '',
                'check_out' => '16:00',
                'status' => 'hadir',
            ])
            ->assertRedirect('/hr/absensi/create')
            ->assertSessionHasErrors(['check_out']);

        $this->from('/hr/absensi/create')
            ->post('/hr/absensi', [
                'employee_id' => $employee->id,
                'work_date' => '2026-11-10',
                'shift' => 'Siang',
                'status' => 'izin',
            ])
            ->assertRedirect('/hr/absensi/create')
            ->assertSessionHasErrors(['work_date']);

        $this->put("/hr/absensi/{$second->id}", [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-10',
            'shift' => 'Siang',
            'status' => 'izin',
        ])->assertSessionHasErrors(['work_date']);

        $this->put("/hr/absensi/{$first->id}", [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-10',
            'shift' => 'Pagi',
            'check_in' => '08:00',
            'check_out' => '17:00',
            'status' => 'hadir',
        ])->assertRedirect('/hr/absensi');

        $this->assertDatabaseHas('attendances', [
            'id' => $first->id,
            'check_out' => '17:00',
        ]);
    }

    private function createEmployee(
        string $code,
        string $name,
        string $deptCode = 'DEP-PROD',
        string $deptName = 'Produksi Sirkular'
    ): Employee {
        $department = Department::firstOrCreate(
            ['code' => $deptCode],
            ['name' => $deptName]
        );

        return Employee::create([
            'department_id' => $department->id,
            'employee_code' => $code,
            'name' => $name,
            'position' => 'Operator Produksi',
            'join_date' => '2026-11-05',
            'status' => 'active',
        ]);
    }

    private function hrUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('hr');

        return $user;
    }
}
