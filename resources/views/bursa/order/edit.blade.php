@extends('layouts.app')

@section('content')
<div class="min-w-0 space-y-6 [overflow-wrap:anywhere]">
    <x-page-header title="Edit Pesanan #{{ $order->id }}" subtitle="Perbarui pesanan yang menunggu persetujuan">
        <x-slot:actions>
            <a href="{{ route('bursa.order.show', $order) }}" class="text-sm font-semibold text-brand-800 hover:underline">Kembali ke detail</a>
        </x-slot:actions>
    </x-page-header>
    <x-card title="Data pesanan">
        @if ($order->status !== 'pending')
            <p role="status" class="text-sm text-slate-600">Pesanan ini telah diproses dan tidak dapat diedit.</p>
        @else
            <form action="{{ route('bursa.order.update', $order) }}" method="POST" class="max-w-2xl space-y-5">
                @csrf
                @method('PUT')
                @if ($errors->any())
                    <div role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                        <p class="font-semibold">Perubahan belum tersimpan. Periksa data berikut.</p>
                        <ul class="mt-2 list-inside list-disc">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="rounded-lg bg-slate-50 p-4 text-sm">
                    <p class="font-semibold text-slate-700">Limbah yang dipesan</p>
                    <p>{{ $order->listing?->category ?? 'Listing tidak tersedia' }}</p>
                    <p class="mt-1 text-slate-500">Harga per kg saat pengajuan: Rp {{ $order->price_per_kg }}. Listing dan harga pesanan tidak dapat diubah melalui formulir ini.</p>
                </div>
                @include('bursa.order._fields', ['availableWeight' => $order->listing?->available_weight ?? 0])
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-900">Simpan perubahan</button>
            </form>
        @endif
    </x-card>
</div>
@endsection
