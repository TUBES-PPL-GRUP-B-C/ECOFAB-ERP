@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        :title="$supplier->name"
        :subtitle="'Detail informasi pemasok bahan baku (' . $supplier->code . ')'"
        :badge="$supplier->code"
    >
        <x-slot:actions>
            <a href="{{ route('pembelian.supplier.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                Kembali ke daftar
            </a>
            @can('pembelian.update')
                <a href="{{ route('pembelian.supplier.edit', $supplier) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Ubah Supplier
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card title="Informasi Pemasok & Lokasi Asal" subtitle="Data identitas kontak dan ketertelusuran lokasi asal bahan baku (Eco-Passport).">
        <dl class="grid gap-4 sm:grid-cols-3 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kode Supplier</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $supplier->code }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Pemasok</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $supplier->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</dt>
                <dd class="mt-1">
                    <x-badge :status="$supplier->status === 'active' ? 'aktif' : 'tidak_aktif'">
                        {{ $supplier->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Kontak (PIC)</dt>
                <dd class="mt-1 text-slate-700">{{ $supplier->contact_person ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Telepon</dt>
                <dd class="mt-1 text-slate-700">{{ $supplier->phone ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Email</dt>
                <dd class="mt-1 text-slate-700">{{ $supplier->email ?: '—' }}</dd>
            </div>
            <div class="sm:col-span-3 rounded-lg border border-emerald-100 bg-emerald-50/40 p-3.5">
                <dt class="text-xs font-semibold uppercase tracking-wider text-brand-900">Lokasi Asal Bahan Baku (Kota, Provinsi, Negara)</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $supplier->origin_location }}</dd>
            </div>
            <div class="sm:col-span-3">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Lengkap</dt>
                <dd class="mt-1 text-slate-700">{{ $supplier->address ?: 'Tidak ada keterangan alamat tambahan.' }}</dd>
            </div>
        </dl>
    </x-card>

    <x-card title="Riwayat Purchase Order" subtitle="Daftar Purchase Order yang diterbitkan kepada pemasok ini.">
        @if ($purchaseOrders->isEmpty())
            <x-empty-state
                title="Belum ada Purchase Order"
                description="Belum ada transaksi Purchase Order yang tercatat untuk pemasok ini."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">Nomor PO</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Status</th>
                    </tr>
                </x-slot:header>
                @foreach ($purchaseOrders as $po)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $po->po_number ?? 'PO #' . $po->id }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $po->order_date ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $po->status ?? '—' }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>
@endsection
