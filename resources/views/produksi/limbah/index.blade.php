@extends('layouts.app')

@section('content')
@php
    $searchValue = old('cari', request('cari'));
    $statusValue = old('status', request('status'));
    $hasFilters = request()->filled('cari') || request()->filled('status');
@endphp
<div class="space-y-6">
    <x-page-header title="Limbah Produksi (FR-22)" subtitle="Kelola catatan sisa bahan produksi, berat, dan kondisinya.">
        @can('produksi.create')
            <x-slot:actions>
                <a href="{{ route('produksi.limbah.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Tambah Limbah</a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Limbah Produksi" subtitle="Cari catatan limbah dan saring berdasarkan status.">
        <form action="{{ route('produksi.limbah.index') }}" method="GET" class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_14rem_auto] lg:items-end">
            <div class="space-y-1.5">
                <label for="cari" class="block text-xs font-semibold text-slate-700">Cari Limbah</label>
                <input id="cari" name="cari" type="search" value="{{ is_scalar($searchValue) ? $searchValue : '' }}" maxlength="255" placeholder="Cari catatan limbah" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="space-y-1.5">
                <label for="status" class="block text-xs font-semibold text-slate-700">Status</label>
                <select id="status" name="status" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(is_scalar($statusValue) && (string) $statusValue === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route('produksi.limbah.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                @endif
            </div>
        </form>

        @if ($wasteRecords->isEmpty())
            @if ($hasFilters)
                <x-empty-state title="Tidak ada limbah yang cocok" description="Coba kata pencarian atau status lain, atau reset filter untuk melihat seluruh catatan limbah." />
            @else
                <x-empty-state title="Belum ada catatan limbah" description="Catatan sisa bahan produksi akan muncul di sini setelah ditambahkan." />
            @endif
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Jenis Limbah / Kondisi</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Berat (kg)</th>
                        <th scope="col" class="px-6 py-3.5">Asal</th>
                        <th scope="col" class="px-6 py-3.5">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:header>
                @foreach ($wasteRecords as $wasteRecord)
                    @php($canManage = in_array($wasteRecord->status, ['available', 'sold'], true) && $wasteRecord->waste_listings_count === 0)
                    <tr class="transition-colors hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <a href="{{ route('produksi.limbah.show', $wasteRecord) }}" class="break-words font-semibold text-brand-900 hover:underline">{{ $wasteRecord->waste_type }}</a>
                            <p class="mt-1 break-words text-slate-600">{{ $wasteRecord->condition }}</p>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-medium text-slate-800">{{ number_format((float) $wasteRecord->weight_kg, 3, ',', '.') }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            @if ($wasteRecord->workOrder)
                                <a href="{{ route('produksi.wo.show', $wasteRecord->workOrder) }}" class="font-medium text-brand-800 hover:underline">{{ $wasteRecord->workOrder->wo_number }}</a>
                            @else
                                Tanpa Work Order
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @include('produksi.limbah._status', ['status' => $wasteRecord->status])
                            @if (! $canManage)
                                <p class="mt-1 text-xs text-slate-500">Dilindungi oleh referensi Bursa</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('produksi.limbah.show', $wasteRecord) }}" class="text-xs font-semibold text-slate-600 hover:text-brand-800" aria-label="Detail limbah {{ $wasteRecord->waste_type }}">Detail</a>
                                @if ($canManage)
                                    @can('produksi.update')
                                        <a href="{{ route('produksi.limbah.edit', $wasteRecord) }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950" aria-label="Ubah limbah {{ $wasteRecord->waste_type }}">Ubah</a>
                                    @endcan
                                    @can('produksi.delete')
                                        <form action="{{ route('produksi.limbah.destroy', $wasteRecord) }}" method="POST" onsubmit="return confirm('Hapus catatan limbah ini? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800" aria-label="Hapus limbah {{ $wasteRecord->waste_type }}">Hapus</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
        <x-pagination :paginator="$wasteRecords->withQueryString()" />
    </x-card>
</div>
@endsection
