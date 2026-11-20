@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        :title="'Detail Absensi — ' . ($attendance->employee?->name ?? 'Karyawan')"
        :subtitle="'Catatan kehadiran pada ' . format_tanggal($attendance->work_date)"
        :badge="'Shift ' . $attendance->shift"
    >
        <x-slot:actions>
            <a href="{{ route('hr.absensi.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                Kembali ke daftar
            </a>
            @can('hr.update')
                <a href="{{ route('hr.absensi.edit', $attendance) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Ubah Absensi
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card title="Informasi Kehadiran Karyawan" subtitle="Rincian tanggal kerja, shift, waktu check-in/check-out, dan status kehadiran.">
        <dl class="grid gap-4 sm:grid-cols-3 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Karyawan</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $attendance->employee?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kode Karyawan (NIP)</dt>
                <dd class="mt-1 font-semibold text-brand-900">{{ $attendance->employee?->employee_code ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Departemen</dt>
                <dd class="mt-1 text-slate-700">{{ $attendance->employee?->department?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Kerja</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ format_tanggal($attendance->work_date) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Shift Kerja</dt>
                <dd class="mt-1 text-slate-800">Shift {{ $attendance->shift }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Kehadiran</dt>
                <dd class="mt-1">
                    <x-badge :status="$attendance->badge_status">
                        {{ $attendance->status_label }}
                    </x-badge>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Waktu Check-In</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ $attendance->check_in ? substr($attendance->check_in, 0, 5) : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Waktu Check-Out</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ $attendance->check_out ? substr($attendance->check_out, 0, 5) : '—' }}</dd>
            </div>
            <div class="sm:col-span-3">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan / Keterangan</dt>
                <dd class="mt-1 text-slate-700">{{ $attendance->notes ?: 'Tidak ada catatan tambahan.' }}</dd>
            </div>
        </dl>
    </x-card>
</div>
@endsection
