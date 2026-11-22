@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Sales Order / SO (FR-20)"
        subtitle="Pesanan penjualan produk jadi (master-detail)"
    >
        @can('penjualan.create')
            <x-slot:actions>
                <a href="{{ route('penjualan.so.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Buat Sales Order
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Sales Order" subtitle="Cari berdasarkan nomor SO, catatan, atau customer.">
        <form action="{{ route('penjualan.so.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nomor SO, nama/kode customer..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="sm:w-52">
                <select name="customer_id" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Semua Customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-44">
                <select name="status" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Semua Status</option>
                    @foreach (\App\Models\SalesOrder::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">Cari</button>
                @if (request()->filled('cari') || request()->filled('status') || request()->filled('customer_id'))
                    <a href="{{ route('penjualan.so.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                @endif
            </div>
        </form>

        @if ($salesOrders->isEmpty())
            <x-empty-state
                title="Belum ada Sales Order"
                description="Pesanan penjualan akan muncul di sini setelah dibuat."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Nomor SO</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Customer</th>
                        <th class="px-6 py-3.5 text-center">Item</th>
                        <th class="px-6 py-3.5 text-right">Total</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:header>

                @foreach ($salesOrders as $so)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-semibold text-brand-900 whitespace-nowrap font-mono">{{ $so->so_number }}</td>
                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ format_tanggal($so->order_date) }}</td>
                        <td class="px-6 py-4 text-slate-800">{{ $so->customer?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-center text-slate-700">{{ $so->items_count }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-800 whitespace-nowrap">{{ format_rupiah($so->total_amount) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap"><x-badge :status="$so->status_badge">{{ $so->status_label }}</x-badge></td>
                        <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('penjualan.so.show', $so) }}" class="text-xs font-semibold text-slate-600 hover:text-brand-800">Detail</a>
                            @can('penjualan.update')
                                @if ($so->isEditable())
                                    <a href="{{ route('penjualan.so.edit', $so) }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950">Ubah</a>
                                @endif
                            @endcan
                            @can('penjualan.delete')
                                @if ($so->isDeletable())
                                    <x-confirm-delete :action="route('penjualan.so.destroy', $so)" :itemName="'Sales Order ' . $so->so_number" />
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$salesOrders" />
        @endif
    </x-card>
</div>
@endsection
