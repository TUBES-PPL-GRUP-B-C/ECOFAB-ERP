@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Tambah Customer" subtitle="Catat data pelanggan baru untuk transaksi Sales Order.">
        <x-slot:actions>
            <a href="{{ route('penjualan.customer.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">
                Kembali ke daftar
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Data Customer" subtitle="Lengkapi kode unik, nama, alamat, dan kontak pelanggan.">
        <form action="{{ route('penjualan.customer.store') }}" method="POST" class="space-y-5">
            @csrf

            @include('penjualan.customer._form')

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('penjualan.customer.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Customer
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
