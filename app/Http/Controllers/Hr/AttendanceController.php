<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $searchColumns = ['shift', 'status', 'notes', 'employee.employee_code', 'employee.name'];
        $startDate = $request->input('tanggal_mulai', $request->input('start_date'));
        $endDate = $request->input('tanggal_selesai', $request->input('end_date'));
        $requestedMonth = $request->input('bulan', $request->input('month'));

        $attendances = Attendance::with(['employee.department'])
            ->search($request->cari, $searchColumns)
            ->filter($request->only(['employee_id', 'shift', 'status']))
            ->dateRange($startDate, $endDate)
            ->forMonth($requestedMonth)
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $recapMonth = $this->resolveRecapMonth($requestedMonth);
        $monthlyRecap = $this->buildMonthlyRecap($recapMonth, $request->input('employee_id'), $startDate, $endDate);
        $recapTotals = [
            'hadir' => (int) $monthlyRecap->sum('hadir_count'),
            'izin' => (int) $monthlyRecap->sum('izin_count'),
            'sakit' => (int) $monthlyRecap->sum('sakit_count'),
            'alpa' => (int) $monthlyRecap->sum('alpa_count'),
            'total' => (int) $monthlyRecap->sum('total_count'),
        ];

        $employees = Employee::with('department')
            ->orderBy('employee_code')
            ->get();

        return view('hr.absensi.index', compact(
            'attendances',
            'employees',
            'monthlyRecap',
            'recapTotals',
            'recapMonth'
        ));
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

    private function resolveRecapMonth(?string $requestedMonth): string
    {
        if ($requestedMonth !== null && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requestedMonth) === 1) {
            return $requestedMonth;
        }

        $latestWorkDate = Attendance::query()->max('work_date');
        if (is_string($latestWorkDate) && strlen($latestWorkDate) >= 7) {
            return substr($latestWorkDate, 0, 7);
        }

        return Carbon::now()->format('Y-m');
    }

    private function buildMonthlyRecap(
        string $recapMonth,
        mixed $employeeId = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $recordsQuery = Attendance::query()->forMonth($recapMonth);

        if ($employeeId !== null && $employeeId !== '') {
            $recordsQuery->where('employee_id', $employeeId);
        }

        if (($startDate !== null && $startDate !== '') || ($endDate !== null && $endDate !== '')) {
            $recordsQuery->dateRange($startDate, $endDate);
        }

        $grouped = $recordsQuery->get()->groupBy('employee_id');

        $employeesQuery = Employee::with('department')->orderBy('employee_code');
        if ($employeeId !== null && $employeeId !== '') {
            $employeesQuery->where('id', $employeeId);
        } else {
            $employeesQuery->where(function ($query) use ($grouped) {
                $query->where('status', 'active');
                if ($grouped->isNotEmpty()) {
                    $query->orWhereIn('id', $grouped->keys());
                }
            });
        }

        return $employeesQuery->get()->map(function (Employee $employee) use ($grouped) {
            $items = $grouped->get($employee->id, collect());
            $hadir = $items->where('status', 'hadir')->count();
            $izin = $items->where('status', 'izin')->count();
            $sakit = $items->where('status', 'sakit')->count();
            $alpa = $items->where('status', 'alpa')->count();

            return (object) [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'name' => $employee->name,
                'department_name' => $employee->department?->name ?? '—',
                'hadir_count' => $hadir,
                'izin_count' => $izin,
                'sakit_count' => $sakit,
                'alpa_count' => $alpa,
                'total_count' => $hadir + $izin + $sakit + $alpa,
            ];
        });
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
