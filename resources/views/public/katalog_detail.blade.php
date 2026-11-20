@extends('layouts.public')

@section('content')
<div class="min-w-0 space-y-6 [overflow-wrap:anywhere]">
    <a href="{{ route('public.bursa.index') }}" class="text-sm font-semibold text-brand-800 hover:underline">Kembali ke katalog</a>
    <h1 class="text-2xl font-bold text-slate-900">{{ $listing->category }}</h1>
    <x-card>
        @if ($listing->photo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($listing->photo_path) }}" alt="Foto limbah {{ $listing->category }}" class="mb-6 h-auto max-h-80 w-full rounded-lg object-contain">
        @else
            <p class="mb-6 rounded-lg bg-slate-50 p-4 text-sm text-slate-500">Foto limbah belum tersedia.</p>
        @endif
        <dl class="grid min-w-0 gap-4 text-sm sm:grid-cols-3">
            <div><dt class="font-semibold">Kategori</dt><dd>{{ $listing->category }}</dd></div>
            <div><dt class="font-semibold">Berat tersedia (kg)</dt><dd>{{ $listing->available_weight }}</dd></div>
            <div><dt class="font-semibold">Harga per kg (Rp)</dt><dd>{{ $listing->price_per_kg }}</dd></div>
        </dl>
    </x-card>
</div>
@endsection
