@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        :title="$department->name"
        :subtitle="'Detail informasi unit departemen (' . $department->code . ')'"
        :badge="$department->code"
    >
        <x-slot:actions>
            <a href="{{ route('hr.departemen.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                Kembali ke daftar
            </a>
            @can('hr.update')
                <a href="{{ route('hr.departemen.edit', $department) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Ubah Departemen
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card title="Informasi Departemen">
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kode Departemen</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $department->code }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Departemen</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $department->name }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Keterangan</dt>
                <dd class="mt-1 text-slate-700">{{ $department->description ?: 'Tidak ada keterangan tambahan.' }}</dd>
            </div>
        </dl>
    </x-card>

    <x-card title="Karyawan pada Departemen Ini" subtitle="Daftar anggota organisasi yang ditempatkan di departemen ini.">
        @if ($employees->isEmpty())
            <x-empty-state
                title="Belum ada karyawan"
                description="Belum ada karyawan yang terdaftar pada departemen ini."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Nama / Posisi</th>
                    </tr>
                </x-slot:header>
                @foreach ($employees as $employee)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-800">#{{ $employee->id }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $employee->name ?? 'Karyawan #' . $employee->id }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>
@endsection
