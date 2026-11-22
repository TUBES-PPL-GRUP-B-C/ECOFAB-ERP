@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Kelola Customer (FR-19)"
        subtitle="Manajemen data pelanggan untuk transaksi penjualan produk jadi"
    >
        @can('penjualan.create')
            <x-slot:actions>
                <a href="{{ route('penjualan.customer.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Tambah Customer
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Customer" subtitle="Cari pelanggan berdasarkan kode, nama, kontak, atau alamat.">
        <form action="{{ route('penjualan.customer.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari kode, nama, telepon, email, atau alamat..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">
                    Cari
                </button>
                @if (request()->filled('cari'))
                    <a href="{{ route('penjualan.customer.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if ($customers->isEmpty())
            <x-empty-state
                title="Belum ada customer"
                description="Data pelanggan akan muncul di sini setelah ditambahkan."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Nama Customer</th>
                        <th class="px-6 py-3.5">Kontak</th>
                        <th class="px-6 py-3.5">Alamat</th>
                        <th class="px-6 py-3.5 text-center">Jumlah SO</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:header>

                @foreach ($customers as $customer)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-semibold text-brand-900 whitespace-nowrap">{{ $customer->code }}</td>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $customer->name }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            <div>{{ $customer->phone ?: '—' }}</div>
                            @if ($customer->email)
                                <div class="text-xs text-slate-500">{{ $customer->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ \Illuminate\Support\Str::limit($customer->address, 60) ?: '—' }}</td>
                        <td class="px-6 py-4 text-center text-slate-700">{{ $customer->sales_orders_count }}</td>
                        <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('penjualan.customer.show', $customer) }}" class="text-xs font-semibold text-slate-600 hover:text-brand-800">
                                Detail
                            </a>
                            @can('penjualan.update')
                                <a href="{{ route('penjualan.customer.edit', $customer) }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950">
                                    Ubah
                                </a>
                            @endcan
                            @can('penjualan.delete')
                                <x-confirm-delete
                                    :action="route('penjualan.customer.destroy', $customer)"
                                    :itemName="'customer ' . $customer->name"
                                />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$customers" />
        @endif
    </x-card>
</div>
@endsection
