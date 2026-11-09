@extends('layouts.public')

@section('content')
<div class="space-y-6">
    <div class="text-center max-w-xl mx-auto py-4">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
            Bursa Sirkular Terbuka
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">Katalog Limbah Manufaktur Ramah Lingkungan</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-2">
            Akses langsung untuk pengrajin lokal dan industri daur ulang untuk memanfaatkan sisa potongan kayu FSC, serat rotan, dan serbuk gergaji berkualitas.
        </p>
    </div>

    @forelse ($listings as $listing)
        <x-card>
            <h2 class="font-semibold text-slate-900">
                <a href="{{ route('public.bursa.show', $listing->id) }}" class="hover:underline">{{ $listing->category }}</a>
            </h2>
            <p class="mt-2 text-sm">Berat tersedia: {{ $listing->available_weight }} kg</p>
            <p class="text-sm">Harga per kg: Rp {{ $listing->price_per_kg }}</p>
        </x-card>
    @empty
        <x-card>
            <x-empty-state
                title="Belum ada limbah tersedia"
                description="Silakan kunjungi katalog kembali untuk melihat ketersediaan limbah."
            />
        </x-card>
    @endforelse
</div>
@endsection
