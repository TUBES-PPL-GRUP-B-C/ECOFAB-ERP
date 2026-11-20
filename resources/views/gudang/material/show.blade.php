@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="{{ $material->name }}"
        subtitle="SKU: {{ $material->sku }} &bull; Kategori: {{ $material->category }}"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.material.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
            @can('gudang.update')
                <a href="{{ route('gudang.material.edit', $material->id) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Edit Data
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Spesifikasi -->
        <x-card title="Spesifikasi Material">
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Satuan</dt>
                    <dd class="font-semibold text-slate-900">{{ $material->unit }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Biaya Standar</dt>
                    <dd class="font-semibold text-slate-900">Rp {{ number_format($material->unit_cost, 0, ',', '.') }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Batas Stok Min.</dt>
                    <dd class="font-semibold text-slate-900">{{ number_format($material->min_stock, 1) }} {{ $material->unit }}</dd>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <dt class="text-slate-500">Status Saldo</dt>
                    <dd>
                        @if($material->is_low_stock)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                Stok Kritis
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Aman
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="py-2.5">
                    <dt class="text-slate-500 mb-1">Jejak Karbon (Faktor Emisi)</dt>
                    <dd>
                        @if($material->emissionFactor)
                            <div class="font-semibold text-emerald-700">{{ number_format($material->emissionFactor->value_kg_co2e, 4) }} kg CO₂e / {{ $material->unit }}</div>
                            <small class="text-slate-400">Sumber: {{ $material->emissionFactor->source ?? '-' }}</small>
                        @else
                            <span class="text-slate-400">Belum diset</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </x-card>

        <!-- Sebaran Stok di Gudang & Riwayat -->
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Sebaran Stok per Fasilitas Gudang" subtitle="Total stok di seluruh gudang: {{ number_format($material->total_stock, 2) }} {{ $material->unit }}">
                @if($material->stocks->isEmpty())
                    <x-empty-state title="Belum ada saldo stok" description="Belum ada barang yang masuk ke gudang manapun." />
                @else
                    <x-table>
                        <x-slot:header>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Nama Gudang</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase text-slate-600">Kuantitas</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Satuan</th>
                            </tr>
                        </x-slot:header>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($material->stocks as $stock)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $stock->warehouse?->code }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-slate-900">{{ $stock->warehouse?->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-right text-sm font-bold text-slate-900">{{ number_format($stock->qty, 2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-xs text-slate-600">{{ $material->unit }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-table>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection
