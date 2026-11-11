@extends('layouts.public')

@section('content')
<div class="space-y-6">
    <a href="{{ route('public.bursa.index') }}" class="text-sm font-semibold text-brand-800 hover:underline">Kembali ke katalog</a>
    <h1 class="text-2xl font-bold text-slate-900">{{ $listing->category }}</h1>
    <x-card>
        @if ($listing->photo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($listing->photo_path) }}" alt="Foto limbah {{ $listing->category }}" class="mb-6 max-h-80 rounded-lg object-contain">
        @else
            <p class="mb-6 rounded-lg bg-slate-50 p-4 text-sm text-slate-500">Foto limbah belum tersedia.</p>
        @endif
        <dl class="space-y-4 text-sm">
            <div><dt class="font-semibold">Kategori</dt><dd>{{ $listing->category }}</dd></div>
            <div><dt class="font-semibold">Berat tersedia (kg)</dt><dd>{{ $listing->available_weight }}</dd></div>
            <div><dt class="font-semibold">Harga per kg (Rp)</dt><dd>{{ $listing->price_per_kg }}</dd></div>
        </dl>
    </x-card>
</div>
@endsection
