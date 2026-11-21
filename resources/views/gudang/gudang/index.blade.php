@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Kelola Fasilitas Gudang (FR-09)"
        subtitle="Manajemen data master lokasi penyimpanan material dan produk jadi"
    >
        @can('gudang.create')
            <x-slot:actions>
                <a href="{{ route('gudang.gudang.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    + Tambah Gudang
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Lokasi Gudang" subtitle="Cari dan kelola lokasi fisik gudang penyimpanan.">
        <form action="{{ route('gudang.gudang.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari kode, nama gudang, atau alamat..."
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
                    <a href="{{ route('gudang.gudang.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if ($warehouses->isEmpty())
            <x-empty-state
                title="Belum ada gudang"
                description="Fasilitas gudang baru akan muncul di sini setelah ditambahkan."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Kode</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Nama Gudang</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Alamat</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Kapasitas</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Item Tersimpan</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Aksi</th>
                    </tr>
                </x-slot:header>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($warehouses as $wh)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 font-mono text-xs font-bold text-brand-900">
                                {{ $wh->code }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                <a href="{{ route('gudang.gudang.show', $wh->id) }}" class="hover:underline text-brand-800">
                                    {{ $wh->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate">
                                {{ $wh->address ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-700">
                                {{ $wh->capacity ? number_format($wh->capacity, 2) . ' m³' : 'Tidak Dibatasi' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <x-badge variant="info">{{ $wh->material_stocks_count }} jenis</x-badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <x-badge variant="{{ $wh->status === 'active' ? 'success' : 'neutral' }}">
                                    {{ $wh->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('gudang.gudang.show', $wh->id) }}" class="text-slate-600 hover:text-slate-900" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @can('gudang.update')
                                        <a href="{{ route('gudang.gudang.edit', $wh->id) }}" class="text-brand-600 hover:text-brand-800" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>

            <div class="mt-4">
                {{ $warehouses->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
