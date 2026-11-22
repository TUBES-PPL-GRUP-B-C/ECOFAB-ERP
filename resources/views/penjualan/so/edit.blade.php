@extends('layouts.app')

@section('content')
<div class="max-w-5xl space-y-6">
    <x-page-header title="Ubah Sales Order" :subtitle="'Perbarui pesanan ' . $salesOrder->so_number">
        <x-slot:actions>
            <a href="{{ route('penjualan.so.show', $salesOrder) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Sales Order">
        <form action="{{ route('penjualan.so.update', $salesOrder) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @include('penjualan.so._form', ['salesOrder' => $salesOrder])

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('penjualan.so.show', $salesOrder) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
