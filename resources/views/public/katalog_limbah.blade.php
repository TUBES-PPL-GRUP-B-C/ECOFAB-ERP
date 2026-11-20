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

    <x-card>
        <form action="{{ route('public.bursa.index') }}" method="GET" class="grid min-w-0 gap-4 sm:grid-cols-2">
            <div class="min-w-0">
                <label for="cari" class="mb-1 block text-sm font-semibold text-slate-700">Cari limbah</label>
                <input type="search" id="cari" name="cari" value="{{ $search }}" maxlength="255" placeholder="Cari berdasarkan kategori limbah..." class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                @error('cari')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="min-w-0">
                <label for="category" class="mb-1 block text-sm font-semibold text-slate-700">Kategori</label>
                <select id="category" name="category" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $option)
                        <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Cari</button>
                @if ($search !== '' || $category !== '')
                    <a href="{{ route('public.bursa.index') }}" class="text-sm font-semibold text-brand-800 hover:underline">Reset pencarian</a>
                @endif
            </div>
        </form>
    </x-card>

    <div class="grid min-w-0 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($listings as $listing)
            <x-card class="min-w-0 break-words">
                <h2 class="font-semibold text-slate-900 [overflow-wrap:anywhere]">
                    <a href="{{ route('public.bursa.show', $listing->id) }}" class="hover:underline">{{ $listing->category }}</a>
                </h2>
                <p class="mt-2 text-sm">Berat tersedia: {{ $listing->available_weight }} kg</p>
                <p class="text-sm">Harga per kg: Rp {{ $listing->price_per_kg }}</p>
                <a href="{{ route('public.bursa.show', $listing->id) }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 hover:underline">Lihat detail</a>
            </x-card>
        @empty
            <x-card class="min-w-0 sm:col-span-2 lg:col-span-3">
                @if ($listings->currentPage() > $listings->lastPage())
                    <x-empty-state title="Halaman katalog tidak tersedia" description="Kembali ke halaman pertama untuk melihat hasil katalog.">
                        <x-slot:action>
                            <a href="{{ $listings->url(1) }}" class="text-sm font-semibold text-brand-800 hover:underline">Ke halaman pertama</a>
                        </x-slot:action>
                    </x-empty-state>
                @else
                    <x-empty-state
                        :title="$search !== '' || $category !== '' ? 'Tidak ada limbah yang cocok' : 'Belum ada limbah tersedia'"
                        :description="$search !== '' || $category !== '' ? 'Ubah kata pencarian atau reset filter untuk melihat limbah lainnya.' : 'Silakan kunjungi katalog kembali untuk melihat ketersediaan limbah.'"
                    />
                @endif
            </x-card>
        @endforelse
    </div>

    <div class="min-w-0 overflow-x-auto">
        <x-pagination :paginator="$listings" />
    </div>
</div>
@endsection
