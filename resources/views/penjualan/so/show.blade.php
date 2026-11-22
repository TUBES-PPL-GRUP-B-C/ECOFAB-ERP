@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        :title="'Sales Order ' . $salesOrder->so_number"
        :subtitle="'Pesanan dari ' . ($salesOrder->customer?->name ?? '-')"
        :badge="$salesOrder->status_label"
    >
        <x-slot:actions>
            <a href="{{ route('penjualan.so.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                Kembali ke daftar
            </a>
            @can('penjualan.update')
                @if ($salesOrder->isEditable())
                    <a href="{{ route('penjualan.so.edit', $salesOrder) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                        Ubah Sales Order
                    </a>
                @endif
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card title="Informasi Sales Order">
        <dl class="grid gap-4 sm:grid-cols-3 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor SO</dt>
                <dd class="mt-1 font-mono font-semibold text-slate-900">{{ $salesOrder->so_number }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Order</dt>
                <dd class="mt-1 text-slate-700">{{ format_tanggal($salesOrder->order_date) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</dt>
                <dd class="mt-1"><x-badge :status="$salesOrder->status_badge">{{ $salesOrder->status_label }}</x-badge></dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Customer</dt>
                <dd class="mt-1 text-slate-700">
                    @if ($salesOrder->customer)
                        <a href="{{ route('penjualan.customer.show', $salesOrder->customer) }}" class="font-semibold text-brand-800 hover:text-brand-950">
                            {{ $salesOrder->customer->code }} — {{ $salesOrder->customer->name }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dibuat Oleh</dt>
                <dd class="mt-1 text-slate-700">{{ $salesOrder->creator?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan</dt>
                <dd class="mt-1 text-slate-700">{{ $salesOrder->notes ?: '—' }}</dd>
            </div>
        </dl>
    </x-card>

    <x-card title="Item Pesanan">
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-6 py-3.5">#</th>
                    <th class="px-6 py-3.5">Produk</th>
                    <th class="px-6 py-3.5 text-right">Jumlah</th>
                    <th class="px-6 py-3.5 text-right">Harga Satuan</th>
                    <th class="px-6 py-3.5 text-right">Subtotal</th>
                </tr>
            </x-slot:header>
            @foreach ($salesOrder->items as $i => $item)
                <tr>
                    <td class="px-6 py-4 text-slate-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-4 font-medium text-slate-800">{{ $item->product_name }}</td>
                    <td class="px-6 py-4 text-right text-slate-700">{{ format_angka($item->qty, 3) }}</td>
                    <td class="px-6 py-4 text-right text-slate-700">{{ format_rupiah($item->unit_price) }}</td>
                    <td class="px-6 py-4 text-right font-semibold text-slate-800">{{ format_rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
            <tr class="bg-slate-50">
                <td colspan="4" class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-600">Total</td>
                <td class="px-6 py-4 text-right text-base font-bold text-brand-900">{{ format_rupiah($salesOrder->total_amount) }}</td>
            </tr>
        </x-table>
    </x-card>
</div>
@endsection
