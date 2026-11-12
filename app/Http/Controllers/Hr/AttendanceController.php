<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $searchColumns = ['shift', 'status', 'notes', 'employee.employee_code', 'employee.name'];

        $attendances = Attendance::with(['employee.department'])
            ->search($request->cari, $searchColumns)
            ->filter($request->only(['shift', 'status']))
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('hr.absensi.index', compact('attendances'));
    }

    public function create(): View
    {
        $employees = Employee::with('department')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('hr.absensi.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));

        Attendance::create($validated);

        return redirect()
            ->route('hr.absensi.index')
            ->with('success', 'Data absensi berhasil ditambahkan.');
    }

    public function show(Attendance $absensi): View
    {
        $absensi->load(['employee.department']);

        return view('hr.absensi.show', [
            'attendance' => $absensi,
        ]);
    }

    public function edit(Attendance $absensi): View
    {
        $employees = Employee::with('department')
            ->where(function ($query) use ($absensi) {
                $query->where('status', 'active')
                    ->orWhere('id', $absensi->employee_id);
            })
            ->orderBy('name')
            ->get();

        return view('hr.absensi.edit', [
            'attendance' => $absensi,
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, Attendance $absensi): RedirectResponse
    {
        $validated = $request->validate($this->rules($request, $absensi->id));

        $absensi->update($validated);

        return redirect()
            ->route('hr.absensi.index')
            ->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function destroy(Attendance $absensi): RedirectResponse
    {
        $absensi->delete();

        return redirect()
            ->route('hr.absensi.index')
            ->with('success', 'Data absensi berhasil dihapus.');
    }

    private function rules(Request $request, ?int $ignoreId = null): array
    {
        $normalizedWorkDate = null;
        if ($request->filled('work_date')) {
            try {
                $normalizedWorkDate = Carbon::parse($request->input('work_date'))->toDateString();
            } catch (\Throwable) {
                $normalizedWorkDate = null;
            }
        }

        return [
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
            'work_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, Closure $fail) use ($request, $ignoreId, $normalizedWorkDate): void {
                    if ($normalizedWorkDate === null || ! $request->filled('employee_id')) {
                        return;
                    }

                    $exists = Attendance::query()
                        ->where('employee_id', $request->input('employee_id'))
                        ->where('work_date', $normalizedWorkDate)
                        ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                        ->exists();

                    if ($exists) {
                        $fail('Absensi untuk karyawan ini pada tanggal kerja tersebut sudah tercatat.');
                    }
                },
            ],
            'shift' => ['required', 'string', Rule::in(Attendance::SHIFTS)],
            'check_in' => [
                'nullable',
                'string',
                'regex:/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/',
            ],
            'check_out' => [
                'nullable',
                'string',
                'regex:/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/',
                function (string $attribute, mixed $value, Closure $fail) use ($request): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $checkIn = (string) $request->input('check_in', '');
                    if ($checkIn === '') {
                        $fail('Waktu check-in wajib diisi sebelum mengisi waktu check-out.');
                        return;
                    }

                    $inTime = substr($checkIn, 0, 5);
                    $outTime = substr((string) $value, 0, 5);

                    if ($outTime < $inTime) {
                        $fail('Waktu check-out harus sama dengan atau setelah waktu check-in.');
                    }
                },
            ],
            'status' => ['required', 'string', Rule::in(Attendance::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
