@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Dokumen GRN: {{ $receipt->grn_number }}"
        subtitle="Tanggal Diterima: {{ $receipt->received_date?->format('d F Y') }} &bull; Status: {{ strtoupper($receipt->status) }}"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.grn.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
            @can('gudang.update')
                <a href="{{ route('gudang.grn.edit', $receipt->id) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Edit Catatan
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Informasi Header -->
        <x-card title="Informasi Penerimaan">
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Nomor GRN</dt>
                    <dd class="font-mono font-bold text-slate-900">{{ $receipt->grn_number }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Gudang Penerima</dt>
                    <dd class="font-semibold text-slate-900">{{ $receipt->warehouse?->name }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Ref. PO</dt>
                    <dd class="font-mono text-slate-700">{{ $receipt->purchase_order_id ? 'PO #' . $receipt->purchase_order_id : '-' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        <x-badge variant="{{ $receipt->status === 'received' ? 'success' : 'neutral' }}">
                            {{ strtoupper($receipt->status) }}
                        </x-badge>
                    </dd>
                </div>
                <div class="py-2.5">
                    <dt class="text-slate-500 mb-1">Catatan / Surat Jalan</dt>
                    <dd class="text-slate-800">{{ $receipt->notes ?? 'Tidak ada catatan.' }}</dd>
                </div>
            </dl>
        </x-card>

        <!-- Rincian Item Diterima -->
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Daftar Bahan yang Diterima" subtitle="Total: {{ $receipt->items->count() }} item bahan baku">
                <x-table>
                    <x-slot:header>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Nama Bahan</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase text-slate-600">Kuantitas</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Satuan</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-600">Keterangan</th>
                        </tr>
                    </x-slot:header>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($receipt->items as $item)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $item->material?->sku }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-slate-900">{{ $item->material?->name }}</td>
                                <td class="whitespace-nowrap px-6 py-3 text-right text-sm font-bold text-emerald-700">+{{ number_format($item->qty_received, 2) }}</td>
                                <td class="whitespace-nowrap px-6 py-3 text-xs text-slate-600">{{ $item->material?->unit }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500">{{ $item->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </x-card>
        </div>
    </div>
</div>
@endsection
