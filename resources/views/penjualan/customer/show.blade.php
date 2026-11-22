@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        :title="$customer->name"
        :subtitle="'Detail informasi customer (' . $customer->code . ')'"
        :badge="$customer->code"
    >
        <x-slot:actions>
            <a href="{{ route('penjualan.customer.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                Kembali ke daftar
            </a>
            @can('penjualan.update')
                <a href="{{ route('penjualan.customer.edit', $customer) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Ubah Customer
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card title="Informasi Customer" subtitle="Identitas dan kontak pelanggan.">
        <dl class="grid gap-4 sm:grid-cols-3 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kode Customer</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $customer->code }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Customer</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $customer->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Telepon</dt>
                <dd class="mt-1 text-slate-700">{{ $customer->phone ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Email</dt>
                <dd class="mt-1 text-slate-700">{{ $customer->email ?: '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat</dt>
                <dd class="mt-1 text-slate-700">{{ $customer->address ?: '—' }}</dd>
            </div>
        </dl>
    </x-card>

    <x-card title="Riwayat Sales Order" subtitle="Daftar Sales Order milik customer ini.">
        @if ($salesOrders->isEmpty())
            <x-empty-state
                title="Belum ada Sales Order"
                description="Belum ada transaksi Sales Order untuk customer ini."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Nomor SO</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Total</th>
                    </tr>
                </x-slot:header>
                @foreach ($salesOrders as $so)
                    <tr>
                        <td class="px-6 py-4 font-medium">
                            <a href="{{ route('penjualan.so.show', $so) }}" class="text-brand-800 hover:text-brand-950">{{ $so->so_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ format_tanggal($so->order_date) }}</td>
                        <td class="px-6 py-4"><x-badge :status="$so->status_badge">{{ $so->status_label }}</x-badge></td>
                        <td class="px-6 py-4 text-right text-slate-800">{{ format_rupiah($so->total_amount) }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>
@endsection
