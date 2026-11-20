@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Ubah Limbah Produksi" :subtitle="'Perbarui catatan ' . $wasteRecord->waste_type . '.'">
        <x-slot:actions>
            <a href="{{ route('produksi.limbah.show', $wasteRecord) }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">Kembali ke detail</a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Limbah Produksi" subtitle="Kolom bertanda * wajib diisi.">
        @if (in_array($wasteRecord->status, ['available', 'sold'], true) && $wasteRecord->waste_listings_count === 0)
            @can('produksi.update')
                <form action="{{ route('produksi.limbah.update', $wasteRecord) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    @include('produksi.limbah._form', ['submitLabel' => 'Simpan Perubahan', 'cancelUrl' => route('produksi.limbah.show', $wasteRecord)])
                </form>
            @endcan
        @else
            <p class="text-sm text-slate-600">Catatan limbah yang terdaftar atau memiliki referensi di Bursa tidak dapat diubah di sini agar data Bursa tetap konsisten.</p>
        @endif
    </x-card>
</div>
@endsection
