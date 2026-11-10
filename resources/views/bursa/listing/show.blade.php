@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Detail Listing #{{ $listing->id }}" subtitle="Informasi listing Bursa Limbah">
        <x-slot:actions>
            <a href="{{ route('bursa.listing.index') }}" class="text-sm font-semibold text-brand-800 hover:underline">Kembali ke daftar</a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Data listing">
        <dl class="grid gap-5 text-sm sm:grid-cols-2">
            <div><dt class="font-semibold text-slate-600">Limbah produksi</dt><dd>#{{ $listing->waste_record_id }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Kategori</dt><dd>{{ $listing->category }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Harga per kg (Rp)</dt><dd>{{ $listing->price_per_kg }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Berat tersedia (kg)</dt><dd>{{ $listing->available_weight }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Status</dt><dd>{{ $listing->status }}</dd></div>
        </dl>
        @if ($listing->photo_path)
            <div class="mt-6">
                <p class="mb-2 text-sm font-semibold text-slate-600">Foto limbah</p>
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($listing->photo_path) }}" alt="Foto limbah {{ $listing->category }}" class="max-h-80 rounded-lg object-contain">
            </div>
        @endif
        @can('bursa.update')
            <a href="{{ route('bursa.listing.edit', $listing) }}" class="mt-6 inline-block rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Edit listing</a>
        @endcan
    </x-card>
</div>
@endsection

