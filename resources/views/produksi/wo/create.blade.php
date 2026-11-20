@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Tambah Work Order" subtitle="Susun rencana produksi baru dengan status awal Draft.">
        <x-slot:actions>
            <a href="{{ route('produksi.wo.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">Kembali ke daftar</a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Work Order" subtitle="Kolom bertanda * wajib diisi.">
        <form action="{{ route('produksi.wo.store') }}" method="POST" class="space-y-5">
            @csrf
            @include('produksi.wo._form', ['submitLabel' => 'Simpan Draft', 'cancelUrl' => route('produksi.wo.index')])
        </form>
    </x-card>
</div>
@endsection
