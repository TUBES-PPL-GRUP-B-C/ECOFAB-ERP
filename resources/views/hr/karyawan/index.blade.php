@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Kelola Karyawan (FR-04)"
        subtitle="Manajemen data personel pabrik, penempatan departemen, dan akun pengguna"
    >
        @can('hr.create')
            <x-slot:actions>
                <a href="{{ route('hr.karyawan.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Tambah Karyawan
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Karyawan" subtitle="Cari dan kelola identitas karyawan, departemen, jabatan, serta akun pengguna.">
        <form action="{{ route('hr.karyawan.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari kode karyawan, nama, jabatan, telepon, atau departemen..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
            </div>
            <div class="sm:w-52">
                <select
                    name="department_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
                    <option value="">Semua Departemen</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-40">
                <select
                    name="status"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">
                    Cari
                </button>
                @if (request()->filled('cari') || request()->filled('department_id') || request()->filled('status'))
                    <a href="{{ route('hr.karyawan.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if ($employees->isEmpty())
            <x-empty-state
                title="Belum ada karyawan"
                description="Data karyawan pabrik beserta penempatan departemennya akan muncul di sini setelah ditambahkan."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Nama Karyawan</th>
                        <th class="px-6 py-3.5">Departemen & Jabatan</th>
                        <th class="px-6 py-3.5">Tanggal Masuk</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:header>

                @foreach ($employees as $employee)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-semibold text-brand-900 whitespace-nowrap">
                            {{ $employee->employee_code }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $employee->name }}</div>
                            @if ($employee->user)
                                <div class="text-xs text-slate-500">{{ $employee->user->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <div class="font-medium text-slate-800">{{ $employee->department?->name ?? '—' }}</div>
                            <div class="text-xs text-slate-500">{{ $employee->position }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-700 whitespace-nowrap">
                            {{ format_tanggal($employee->join_date) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :status="$employee->status === 'active' ? 'aktif' : 'tidak_aktif'">
                                {{ $employee->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('hr.karyawan.show', $employee) }}" class="text-xs font-semibold text-slate-600 hover:text-brand-800">
                                Detail
                            </a>
                            @can('hr.update')
                                <a href="{{ route('hr.karyawan.edit', $employee) }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950">
                                    Ubah
                                </a>
                            @endcan
                            @can('hr.delete')
                                <x-confirm-delete
                                    :action="route('hr.karyawan.destroy', $employee)"
                                    :itemName="'karyawan ' . $employee->name"
                                />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$employees" />
        @endif
    </x-card>
</div>
@endsection
