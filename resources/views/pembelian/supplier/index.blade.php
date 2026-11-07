@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Kelola Supplier (FR-06)"
        subtitle="Manajemen pemasok bahan baku beserta lokasi asal untuk ketertelusuran Eco-Passport"
    >
        @can('pembelian.create')
            <x-slot:actions>
                <a href="{{ route('pembelian.supplier.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Tambah Supplier
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Supplier" subtitle="Cari dan kelola identitas pemasok, kontak, serta lokasi asal bahan baku (kota, provinsi, negara).">
        <form action="{{ route('pembelian.supplier.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari kode, nama pemasok, kontak, atau kota/provinsi..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
            </div>
            <div class="sm:w-44">
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
                @if (request()->filled('cari') || request()->filled('status'))
                    <a href="{{ route('pembelian.supplier.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if ($suppliers->isEmpty())
            <x-empty-state
                title="Belum ada supplier"
                description="Data pemasok bahan baku beserta lokasi asalnya akan muncul di sini setelah ditambahkan."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Nama Pemasok</th>
                        <th class="px-6 py-3.5">Kontak</th>
                        <th class="px-6 py-3.5">Lokasi Asal</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:header>

                @foreach ($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-semibold text-brand-900 whitespace-nowrap">
                            {{ $supplier->code }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $supplier->name }}</div>
                            @if ($supplier->email)
                                <div class="text-xs text-slate-500">{{ $supplier->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <div>{{ $supplier->contact_person ?: '—' }}</div>
                            @if ($supplier->phone)
                                <div class="text-xs text-slate-500">{{ $supplier->phone }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-700">
                            {{ $supplier->origin_location }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :status="$supplier->status === 'active' ? 'aktif' : 'tidak_aktif'">
                                {{ $supplier->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('pembelian.supplier.show', $supplier) }}" class="text-xs font-semibold text-slate-600 hover:text-brand-800">
                                Detail
                            </a>
                            @can('pembelian.update')
                                <a href="{{ route('pembelian.supplier.edit', $supplier) }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950">
                                    Ubah
                                </a>
                            @endcan
                            @can('pembelian.delete')
                                <x-confirm-delete
                                    :action="route('pembelian.supplier.destroy', $supplier)"
                                    :itemName="'supplier ' . $supplier->name"
                                />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$suppliers" />
        @endif
    </x-card>
</div>
@endsection
