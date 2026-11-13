@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Kelola Absensi (FR-05)"
        subtitle="Pencatatan kehadiran harian, shift kerja, serta waktu check-in dan check-out karyawan"
    >
        @can('hr.create')
            <x-slot:actions>
                <a href="{{ route('hr.absensi.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Catat Absensi
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Filter & Pencarian Absensi" subtitle="Saring data kehadiran berdasarkan karyawan, bulan rekap, rentang tanggal, shift, atau status.">
        <form action="{{ route('hr.absensi.index') }}" method="GET" class="space-y-4">
            <div class="grid gap-3 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <label for="cari" class="mb-1 block text-xs font-semibold text-slate-600">Kata Kunci</label>
                    <input
                        id="cari"
                        type="text"
                        name="cari"
                        value="{{ request('cari') }}"
                        placeholder="Cari nama/kode karyawan, shift, status, atau catatan..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                    >
                </div>

                <div>
                    <label for="employee_id" class="mb-1 block text-xs font-semibold text-slate-600">Karyawan</label>
                    <select
                        id="employee_id"
                        name="employee_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                    >
                        <option value="">Semua Karyawan</option>
                        @foreach ($employees as $employeeOption)
                            <option value="{{ $employeeOption->id }}" @selected((string) request('employee_id') === (string) $employeeOption->id)>
                                {{ $employeeOption->employee_code }} — {{ $employeeOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="bulan" class="mb-1 block text-xs font-semibold text-slate-600">Bulan Rekap</label>
                    <input
                        id="bulan"
                        type="month"
                        name="bulan"
                        value="{{ request('bulan') }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                    >
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-5">
                <div>
                    <label for="tanggal_mulai" class="mb-1 block text-xs font-semibold text-slate-600">Dari Tanggal</label>
                    <input
                        id="tanggal_mulai"
                        type="date"
                        name="tanggal_mulai"
                        value="{{ request('tanggal_mulai') }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                    >
                </div>

                <div>
                    <label for="tanggal_selesai" class="mb-1 block text-xs font-semibold text-slate-600">Sampai Tanggal</label>
                    <input
                        id="tanggal_selesai"
                        type="date"
                        name="tanggal_selesai"
                        value="{{ request('tanggal_selesai') }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                    >
                </div>

                <div>
                    <label for="shift" class="mb-1 block text-xs font-semibold text-slate-600">Shift</label>
                    <select
                        id="shift"
                        name="shift"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                    >
                        <option value="">Semua Shift</option>
                        @foreach (\App\Models\Attendance::SHIFTS as $shiftOption)
                            <option value="{{ $shiftOption }}" @selected(request('shift') === $shiftOption)>
                                Shift {{ $shiftOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                    >
                        <option value="">Semua Status</option>
                        <option value="hadir" @selected(request('status') === 'hadir')>Hadir</option>
                        <option value="izin" @selected(request('status') === 'izin')>Izin</option>
                        <option value="sakit" @selected(request('status') === 'sakit')>Sakit</option>
                        <option value="alpa" @selected(request('status') === 'alpa')>Alpa</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">
                        Cari
                    </button>
                    @if (request()->anyFilled(['cari', 'employee_id', 'bulan', 'tanggal_mulai', 'tanggal_selesai', 'shift', 'status']))
                        <a href="{{ route('hr.absensi.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </x-card>

    <x-card
        title="Rekapitulasi Absensi Bulanan per Karyawan"
        :subtitle="'Ringkasan akumulasi status kehadiran karyawan pada periode ' . format_tanggal($recapMonth . '-01', 'F Y')"
    >
        <div class="mb-4 grid gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3.5">
                <div class="text-xs font-semibold text-emerald-800">Total Hadir</div>
                <div class="mt-1 text-lg font-bold text-emerald-950">{{ format_angka($recapTotals['hadir']) }} hari</div>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50/60 p-3.5">
                <div class="text-xs font-semibold text-sky-800">Total Izin</div>
                <div class="mt-1 text-lg font-bold text-sky-950">{{ format_angka($recapTotals['izin']) }} hari</div>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3.5">
                <div class="text-xs font-semibold text-amber-800">Total Sakit</div>
                <div class="mt-1 text-lg font-bold text-amber-950">{{ format_angka($recapTotals['sakit']) }} hari</div>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3.5">
                <div class="text-xs font-semibold text-rose-800">Total Alpa</div>
                <div class="mt-1 text-lg font-bold text-rose-950">{{ format_angka($recapTotals['alpa']) }} hari</div>
            </div>
        </div>

        @if ($monthlyRecap->isEmpty())
            <x-empty-state
                title="Belum ada data rekap karyawan"
                description="Tambahkan data karyawan dan absensi untuk melihat rekapitulasi bulanan."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Nama Karyawan</th>
                        <th class="px-6 py-3.5">Departemen</th>
                        <th class="px-6 py-3.5 text-center">Hadir</th>
                        <th class="px-6 py-3.5 text-center">Izin</th>
                        <th class="px-6 py-3.5 text-center">Sakit</th>
                        <th class="px-6 py-3.5 text-center">Alpa</th>
                        <th class="px-6 py-3.5 text-center">Total tercatat</th>
                    </tr>
                </x-slot:header>

                @foreach ($monthlyRecap as $recapRow)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-3.5 font-semibold text-brand-900 whitespace-nowrap">
                            {{ $recapRow->employee_code }}
                        </td>
                        <td class="px-6 py-3.5 font-medium text-slate-800">
                            {{ $recapRow->name }}
                        </td>
                        <td class="px-6 py-3.5 text-slate-600">
                            {{ $recapRow->department_name }}
                        </td>
                        <td class="px-6 py-3.5 text-center font-semibold text-emerald-700">
                            {{ format_angka($recapRow->hadir_count) }}
                        </td>
                        <td class="px-6 py-3.5 text-center font-semibold text-sky-700">
                            {{ format_angka($recapRow->izin_count) }}
                        </td>
                        <td class="px-6 py-3.5 text-center font-semibold text-amber-700">
                            {{ format_angka($recapRow->sakit_count) }}
                        </td>
                        <td class="px-6 py-3.5 text-center font-semibold text-rose-700">
                            {{ format_angka($recapRow->alpa_count) }}
                        </td>
                        <td class="px-6 py-3.5 text-center font-bold text-slate-900">
                            {{ format_angka($recapRow->total_count) }}
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>

    <x-card title="Daftar Kehadiran Karyawan" subtitle="Rincian kehadiran harian, shift kerja, serta jam check-in dan check-out.">

        @if ($attendances->isEmpty())
            <x-empty-state
                title="Belum ada data absensi"
                description="Catatan kehadiran karyawan akan muncul di sini setelah dicatat."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Tanggal Kerja</th>
                        <th class="px-6 py-3.5">Karyawan</th>
                        <th class="px-6 py-3.5">Shift</th>
                        <th class="px-6 py-3.5">Check-In / Out</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:header>

                @foreach ($attendances as $attendance)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-semibold text-brand-900 whitespace-nowrap">
                            {{ format_tanggal($attendance->work_date) }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $attendance->employee?->name ?? '—' }}</div>
                            <div class="text-xs text-slate-500">
                                {{ $attendance->employee?->employee_code ?? '—' }}
                                @if ($attendance->employee?->department)
                                    • {{ $attendance->employee->department->name }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-700 whitespace-nowrap">
                            Shift {{ $attendance->shift }}
                        </td>
                        <td class="px-6 py-4 text-slate-700 whitespace-nowrap">
                            {{ $attendance->check_in ? substr($attendance->check_in, 0, 5) : '—' }}
                            <span class="text-slate-400">s/d</span>
                            {{ $attendance->check_out ? substr($attendance->check_out, 0, 5) : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :status="$attendance->badge_status">
                                {{ $attendance->status_label }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('hr.absensi.show', $attendance) }}" class="text-xs font-semibold text-slate-600 hover:text-brand-800">
                                Detail
                            </a>
                            @can('hr.update')
                                <a href="{{ route('hr.absensi.edit', $attendance) }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950">
                                    Ubah
                                </a>
                            @endcan
                            @can('hr.delete')
                                <x-confirm-delete
                                    :action="route('hr.absensi.destroy', $attendance)"
                                    :itemName="'absensi ' . ($attendance->employee?->name ?? '') . ' tanggal ' . format_tanggal($attendance->work_date)"
                                />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$attendances" />
        @endif
    </x-card>
</div>
@endsection
