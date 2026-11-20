@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Tambah Limbah Produksi" subtitle="Catat jenis, berat, dan kondisi limbah produksi.">
        <x-slot:actions>
            <a href="{{ route('produksi.limbah.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">Kembali ke daftar</a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Limbah Produksi" subtitle="Kolom bertanda * wajib diisi.">
        <form action="{{ route('produksi.limbah.store') }}" method="POST" class="space-y-5">
            @csrf
            @include('produksi.limbah._form', ['submitLabel' => 'Simpan Limbah', 'cancelUrl' => route('produksi.limbah.index')])
        </form>
    </x-card>
</div>
@endsection
