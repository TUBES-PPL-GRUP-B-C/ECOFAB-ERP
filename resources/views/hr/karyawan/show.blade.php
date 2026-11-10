@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        :title="$employee->name"
        :subtitle="'Detail informasi karyawan (' . $employee->employee_code . ')'"
        :badge="$employee->employee_code"
    >
        <x-slot:actions>
            <a href="{{ route('hr.karyawan.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                Kembali ke daftar
            </a>
            @can('hr.update')
                <a href="{{ route('hr.karyawan.edit', $employee) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Ubah Karyawan
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card title="Profil & Penempatan Karyawan" subtitle="Data identitas personel, departemen, jabatan, serta akun pengguna terkait.">
        <div class="grid gap-6 sm:grid-cols-4">
            <div class="sm:col-span-1">
                @if ($employee->photo_path)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($employee->photo_path) }}"
                        alt="Foto {{ $employee->name }}"
                        class="h-44 w-44 rounded-xl border border-slate-200 object-cover"
                    >
                @else
                    <div class="flex h-44 w-44 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-xs font-semibold text-slate-400">
                        Belum ada foto
                    </div>
                @endif
            </div>

            <dl class="grid gap-4 sm:col-span-3 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kode Karyawan (NIP)</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $employee->employee_code }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Lengkap</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $employee->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Departemen</dt>
                    <dd class="mt-1 font-semibold text-brand-900">{{ $employee->department?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jabatan / Posisi</dt>
                    <dd class="mt-1 text-slate-800">{{ $employee->position }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Bergabung</dt>
                    <dd class="mt-1 text-slate-700">{{ format_tanggal($employee->join_date) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</dt>
                    <dd class="mt-1">
                        <x-badge :status="$employee->status === 'active' ? 'aktif' : 'tidak_aktif'">
                            {{ $employee->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Telepon</dt>
                    <dd class="mt-1 text-slate-700">{{ $employee->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Akun Pengguna Tertaut</dt>
                    <dd class="mt-1 text-slate-700">
                        {{ $employee->user ? $employee->user->name . ' (' . $employee->user->email . ')' : 'Belum ditautkan' }}
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Domisili</dt>
                    <dd class="mt-1 text-slate-700">{{ $employee->address ?: 'Tidak ada keterangan alamat tambahan.' }}</dd>
                </div>
            </dl>
        </div>
    </x-card>

    <x-card title="Riwayat Absensi Terakhir" subtitle="Catatan kehadiran terbaru milik karyawan ini.">
        @if ($attendances->isEmpty())
            <x-empty-state
                title="Belum ada riwayat absensi"
                description="Catatan absensi karyawan ini akan muncul setelah data kehadiran dicatat."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Tanggal Kerja</th>
                        <th class="px-6 py-3.5">Shift</th>
                        <th class="px-6 py-3.5">Status</th>
                    </tr>
                </x-slot:header>
                @foreach ($attendances as $attendance)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ format_tanggal($attendance->work_date ?? null) }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $attendance->shift ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $attendance->status ?? '—' }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>
@endsection
