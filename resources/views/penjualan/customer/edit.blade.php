@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Ubah Customer" :subtitle="'Perbarui data pelanggan ' . $customer->code">
        <x-slot:actions>
            <a href="{{ route('penjualan.customer.show', $customer) }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">
                Kembali ke detail
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Data Customer" subtitle="Perubahan kode harus tetap unik.">
        <form action="{{ route('penjualan.customer.update', $customer) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            @include('penjualan.customer._form', ['customer' => $customer])

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('penjualan.customer.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
