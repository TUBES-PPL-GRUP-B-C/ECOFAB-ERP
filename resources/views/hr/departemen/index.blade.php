@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Kelola Departemen (FR-03)"
        subtitle="Manajemen struktur departemen dan unit organisasi pabrik"
    >
        @can('hr.create')
            <x-slot:actions>
                <a href="{{ route('hr.departemen.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Tambah Departemen
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Departemen" subtitle="Cari dan kelola data kode, nama, serta keterangan departemen.">
        <form action="{{ route('hr.departemen.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari kode, nama, atau keterangan departemen..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">
                    Cari
                </button>
                @if (request()->filled('cari'))
                    <a href="{{ route('hr.departemen.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if ($departments->isEmpty())
            <x-empty-state
                title="Belum ada departemen"
                description="Data departemen pabrik akan muncul di sini setelah ditambahkan."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Nama Departemen</th>
                        <th class="px-6 py-3.5">Keterangan</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:header>

                @foreach ($departments as $department)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-semibold text-brand-900">
                            {{ $department->code }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-800">
                            {{ $department->name }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $department->description ?: '—' }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('hr.departemen.show', $department) }}" class="text-xs font-semibold text-slate-600 hover:text-brand-800">
                                Detail
                            </a>
                            @can('hr.update')
                                <a href="{{ route('hr.departemen.edit', $department) }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950">
                                    Ubah
                                </a>
                            @endcan
                            @can('hr.delete')
                                <x-confirm-delete
                                    :action="route('hr.departemen.destroy', $department)"
                                    :itemName="'departemen ' . $department->name"
                                />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$departments" />
        @endif
    </x-card>
</div>
@endsection
