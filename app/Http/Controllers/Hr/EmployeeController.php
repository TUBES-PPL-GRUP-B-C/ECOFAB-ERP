<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $searchColumns = ['employee_code', 'name', 'position', 'phone', 'department.name'];

        $employees = Employee::with(['department', 'user'])
            ->search($request->cari, $searchColumns)
            ->filter($request->only(['department_id', 'status']))
            ->orderBy('employee_code')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('hr.karyawan.index', compact('employees', 'departments'));
    }

    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        $assignedUserIds = Employee::whereNotNull('user_id')->pluck('user_id');
        $users = User::whereNotIn('id', $assignedUserIds)->orderBy('name')->get();

        return view('hr.karyawan.create', compact('departments', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $attributes = collect($validated)->except('photo')->all();

        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('employees', 'public');
        }

        try {
            Employee::create($attributes);
        } catch (\Throwable $exception) {
            if (isset($attributes['photo_path'])) {
                Storage::disk('public')->delete($attributes['photo_path']);
            }
            throw $exception;
        }

        return redirect()
            ->route('hr.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $karyawan): View
    {
        $karyawan->load(['department', 'user']);

        $attendances = Schema::hasTable('attendances')
            ? DB::table('attendances')->where('employee_id', $karyawan->id)->orderByDesc('work_date')->limit(20)->get()
            : collect();

        return view('hr.karyawan.show', [
            'employee' => $karyawan,
            'attendances' => $attendances,
        ]);
    }

    public function edit(Employee $karyawan): View
    {
        $departments = Department::orderBy('name')->get();
        $assignedUserIds = Employee::whereNotNull('user_id')
            ->where('id', '!=', $karyawan->id)
            ->pluck('user_id');
        $users = User::whereNotIn('id', $assignedUserIds)->orderBy('name')->get();

        return view('hr.karyawan.edit', [
            'employee' => $karyawan,
            'departments' => $departments,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Employee $karyawan): RedirectResponse
    {
        $validated = $request->validate($this->rules($karyawan->id));
        $attributes = collect($validated)->except('photo')->all();
        $oldPhoto = $karyawan->photo_path;

        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('employees', 'public');
        }

        try {
            $karyawan->update($attributes);
        } catch (\Throwable $exception) {
            if (isset($attributes['photo_path'])) {
                Storage::disk('public')->delete($attributes['photo_path']);
            }
            throw $exception;
        }

        if (isset($attributes['photo_path']) && $this->isManagedPhoto($oldPhoto)) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('hr.karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $karyawan): RedirectResponse
    {
        if (Schema::hasTable('attendances') && DB::table('attendances')->where('employee_id', $karyawan->id)->exists()) {
            abort(409, 'Karyawan yang sudah memiliki riwayat absensi tidak dapat dihapus.');
        }

        $photoPath = $karyawan->photo_path;
        $karyawan->delete();

        if ($this->isManagedPhoto($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        return redirect()
            ->route('hr.karyawan.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    private function isManagedPhoto(?string $path): bool
    {
        return $path !== null && preg_match('~^employees/[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|webp)$~i', $path) === 1;
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('employees', 'user_id')->ignore($ignoreId),
            ],
            'employee_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_code')->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'photo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }
}
