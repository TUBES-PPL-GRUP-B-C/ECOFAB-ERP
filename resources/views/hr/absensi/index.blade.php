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

    <x-card title="Daftar Kehadiran Karyawan" subtitle="Cari dan kelola data kehadiran harian, shift kerja, serta jam check-in dan check-out.">
        <form action="{{ route('hr.absensi.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari nama/kode karyawan, shift, status, atau catatan..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
            </div>
            <div class="sm:w-44">
                <select
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
            <div class="sm:w-44">
                <select
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
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">
                    Cari
                </button>
                @if (request()->filled('cari') || request()->filled('shift') || request()->filled('status'))
                    <a href="{{ route('hr.absensi.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

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
