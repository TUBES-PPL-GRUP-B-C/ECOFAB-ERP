<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $departments = Department::search($request->cari, ['code', 'name', 'description'])
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('hr.departemen.index', compact('departments'));
    }

    public function create(): View
    {
        return view('hr.departemen.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Department::create($validated);

        return redirect()
            ->route('hr.departemen.index')
            ->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function show(Department $departemen): View
    {
        $employees = Schema::hasTable('employees')
            ? DB::table('employees')->where('department_id', $departemen->id)->orderBy('id')->get()
            : collect();

        return view('hr.departemen.show', [
            'department' => $departemen,
            'employees' => $employees,
        ]);
    }

    public function edit(Department $departemen): View
    {
        return view('hr.departemen.edit', [
            'department' => $departemen,
        ]);
    }

    public function update(Request $request, Department $departemen): RedirectResponse
    {
        $validated = $request->validate($this->rules($departemen->id));

        $departemen->update($validated);

        return redirect()
            ->route('hr.departemen.index')
            ->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $departemen): RedirectResponse
    {
        if (Schema::hasTable('employees') && DB::table('employees')->where('department_id', $departemen->id)->exists()) {
            abort(409, 'Departemen yang masih memiliki karyawan tidak dapat dihapus.');
        }

        $departemen->delete();

        return redirect()
            ->route('hr.departemen.index')
            ->with('success', 'Departemen berhasil dihapus.');
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments', 'code')->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
