@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Material Sirkular & Bahan Baku (FR-10)"
        subtitle="Kelola katalog bahan baku, faktor jejak karbon, dan monitoring stok minimum"
    >
        @can('gudang.create')
            <x-slot:actions>
                <a href="{{ route('gudang.material.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    + Tambah Material
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    @if($lowStockCount > 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-amber-900">Peringatan: Terdapat {{ $lowStockCount }} Bahan Baku Berada di Bawah Batas Minimum!</h4>
                    <p class="text-xs text-amber-700">Segera ajukan Purchase Order (PO) pengadaan untuk mencegah hambatan pada Work Order.</p>
                </div>
            </div>
        </div>
    @endif

    <x-card title="Daftar Bahan Baku & Material" subtitle="Cari dan pantau ketersediaan stok, emisi, dan harga bahan.">
        <form action="{{ route('gudang.material.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari SKU, nama material, atau kategori..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
            </div>
            <div class="sm:w-48">
                <select
                    name="category"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">
                    Cari
                </button>
                @if (request()->filled('cari') || request()->filled('category'))
                    <a href="{{ route('gudang.material.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if ($materials->isEmpty())
            <x-empty-state
                title="Belum ada material"
                description="Bahan baku baru akan muncul di sini setelah didaftarkan."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">SKU</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Nama Bahan Baku</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Kategori</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Faktor Emisi</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Biaya Standar</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Stok Min.</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Total Saldo</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Status Stok</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Aksi</th>
                    </tr>
                </x-slot:header>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($materials as $mat)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 font-mono text-xs font-bold text-brand-900">
                                {{ $mat->sku }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                <a href="{{ route('gudang.material.show', $mat->id) }}" class="hover:underline text-brand-800">
                                    {{ $mat->name }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs">
                                <x-badge variant="neutral">{{ $mat->category }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center text-xs">
                                @if($mat->emissionFactor)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ number_format($mat->emissionFactor->value_kg_co2e, 4) }} kg CO₂e
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-700">
                                Rp {{ number_format($mat->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-xs text-slate-500">
                                {{ number_format($mat->min_stock, 1) }} {{ $mat->unit }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-slate-900">
                                {{ number_format($mat->total_stock, 1) }} <span class="text-xs font-normal text-slate-500">{{ $mat->unit }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                @if($mat->is_low_stock)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Menipis / Kritis
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aman
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('gudang.material.show', $mat->id) }}" class="text-slate-600 hover:text-slate-900" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @can('gudang.update')
                                        <a href="{{ route('gudang.material.edit', $mat->id) }}" class="text-brand-600 hover:text-brand-800" title="Edit">
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
                {{ $materials->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
