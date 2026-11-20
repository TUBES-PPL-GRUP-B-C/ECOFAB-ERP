@extends('layouts.public')

@section('content')
<div class="min-w-0 space-y-6 [overflow-wrap:anywhere]">
    <x-page-header title="Pesan Limbah" subtitle="Ajukan pesanan untuk ditinjau Admin">
        <x-slot:actions>
            <a href="{{ route('public.bursa.show', $listing->id) }}" class="text-sm font-semibold text-brand-800 hover:underline">Kembali ke detail limbah</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid min-w-0 gap-6 lg:grid-cols-2">
        <x-card title="Limbah yang dipesan" class="min-w-0">
            @if ($listing->photo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($listing->photo_path) }}" alt="Foto limbah {{ $listing->category }}" class="mb-5 h-auto max-h-64 w-full rounded-lg object-contain">
            @endif
            <dl class="space-y-4 text-sm">
                <div><dt class="font-semibold text-slate-600">Kategori</dt><dd>{{ $listing->category }}</dd></div>
                <div><dt class="font-semibold text-slate-600">Berat tersedia</dt><dd>{{ $listing->available_weight }} kg</dd></div>
                <div><dt class="font-semibold text-slate-600">Harga per kg</dt><dd>Rp {{ $listing->price_per_kg }}</dd></div>
            </dl>
            <p class="mt-5 text-sm text-slate-500">Pengajuan pesanan menunggu persetujuan Admin. Ketersediaan limbah diperiksa kembali saat pesanan disetujui.</p>
        </x-card>

        <x-card title="Data pemesanan" class="min-w-0">
            <form action="{{ route('public.bursa.order.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="waste_listing_id" value="{{ $listing->id }}">
                @if ($errors->any())
                    <div role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                        <p class="font-semibold">Pesanan belum dapat dikirim. Periksa data berikut.</p>
                        <ul class="mt-2 list-inside list-disc">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @include('bursa.order._fields', ['order' => null, 'availableWeight' => $listing->available_weight])
                <button type="submit" class="w-full rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-900 sm:w-auto">Kirim pesanan</button>
            </form>
        </x-card>
    </div>
</div>
@endsection
