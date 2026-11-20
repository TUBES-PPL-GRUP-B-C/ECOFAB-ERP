@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="{{ $warehouse->name }}"
        subtitle="Kode: {{ $warehouse->code }} &bull; Status: {{ strtoupper($warehouse->status) }}"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.gudang.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
            @can('gudang.update')
                <a href="{{ route('gudang.gudang.edit', $warehouse->id) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Edit Data
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Rincian Informasi -->
        <x-card title="Spesifikasi Fasilitas">
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Kode Gudang</dt>
                    <dd class="font-mono font-bold text-slate-900">{{ $warehouse->code }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Kapasitas</dt>
                    <dd class="font-semibold text-slate-900">{{ $warehouse->capacity ? number_format($warehouse->capacity, 2) . ' m³' : 'Tidak Dibatasi' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        <x-badge variant="{{ $warehouse->status === 'active' ? 'success' : 'neutral' }}">
                            {{ strtoupper($warehouse->status) }}
                        </x-badge>
                    </dd>
                </div>
                <div class="py-2.5">
                    <dt class="text-slate-500 mb-1">Alamat Fasilitas</dt>
                    <dd class="text-slate-800 leading-relaxed">{{ $warehouse->address ?? 'Belum ada catatan alamat.' }}</dd>
                </div>
            </dl>
        </x-card>

        <!-- Saldo Stok di Gudang Ini -->
        <div class="lg:col-span-2">
            <x-card title="Saldo Stok Bahan di Gudang Ini (FR-11)" subtitle="Kuantitas aktual material yang tersimpan di lokasi {{ $warehouse->code }}">
                @if($warehouse->materialStocks->isEmpty())
                    <x-empty-state title="Gudang masih kosong" description="Belum ada material yang tersimpan di fasilitas ini." />
                @else
                    <x-table>
                        <x-slot:header>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Nama Bahan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Kategori</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase text-slate-600">Saldo Stok</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Satuan</th>
                            </tr>
                        </x-slot:header>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($warehouse->materialStocks as $st)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $st->material?->sku }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-slate-900">{{ $st->material?->name }}</td>
                                    <td class="px-6 py-3 text-xs text-slate-500">{{ $st->material?->category }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-right text-sm font-bold text-slate-900">{{ number_format($st->qty, 2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-xs text-slate-600">{{ $st->material?->unit }}</td>
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
