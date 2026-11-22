@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Penjualan & Komersial"
        subtitle="Kelola pelanggan (FR-19), Sales Order (FR-20), serta persiapan Pengiriman & Invoice (FR-21)"
    >
        @can('penjualan.create')
            <x-slot:actions>
                <a href="{{ route('penjualan.customer.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    + Customer
                </a>
                <a href="{{ route('penjualan.so.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    + Sales Order
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            title="Total Customer"
            :value="$totalCustomers"
            subtext="FR-19 Kelola Customer"
        />
        <x-stat-card
            title="Total Sales Order"
            :value="$totalSalesOrders"
            subtext="FR-20 Master-Detail SO"
        />
        <x-stat-card
            title="SO Dikonfirmasi"
            :value="$confirmedSalesOrders"
            subtext="Siap diproses pengiriman"
        />
        <x-stat-card
            title="Nilai Pesanan Aktif"
            :value="format_rupiah($totalSalesValue)"
            subtext="Di luar SO dibatalkan"
        />
    </div>

    <x-card title="Sales Order Terbaru" subtitle="5 transaksi Sales Order terbaru beserta ringkasan nilai dan status">
        <x-slot:actions>
            <a href="{{ route('penjualan.customer.index') }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Daftar Customer
            </a>
            <a href="{{ route('penjualan.so.index') }}" class="rounded-lg bg-brand-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-900">
                Lihat Semua SO
            </a>
        </x-slot:actions>

        @if ($recentSalesOrders->isEmpty())
            <x-empty-state
                title="Belum ada Sales Order"
                description="Sales Order yang baru dibuat akan tampil di ringkasan ini."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Nomor SO</th>
                        <th class="px-6 py-3.5">Customer</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Total</th>
                    </tr>
                </x-slot:header>
                @foreach ($recentSalesOrders as $so)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-mono font-semibold whitespace-nowrap">
                            <a href="{{ route('penjualan.so.show', $so) }}" class="text-brand-800 hover:text-brand-950">{{ $so->so_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-slate-800">{{ $so->customer?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ format_tanggal($so->order_date) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :status="$so->status_badge">{{ $so->status_label }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-800 whitespace-nowrap">{{ format_rupiah($so->total_amount) }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>
@endsection
