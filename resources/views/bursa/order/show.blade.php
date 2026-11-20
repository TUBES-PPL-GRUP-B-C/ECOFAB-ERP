@extends('layouts.app')

@section('content')
@php
    $statusLabels = ['pending' => 'Menunggu persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];
@endphp
<div class="min-w-0 space-y-6 [overflow-wrap:anywhere]">
    <x-page-header title="Detail Pesanan #{{ $order->id }}" subtitle="Informasi pesanan dan riwayat penanganan">
        <x-slot:actions>
            <a href="{{ route('bursa.order.index') }}" class="text-sm font-semibold text-brand-800 hover:underline">Kembali ke daftar</a>
        </x-slot:actions>
    </x-page-header>
    @if ($errors->any())
        <div role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-semibold">Tindakan belum dapat diproses.</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <x-card title="Data pesanan">
        <dl class="grid min-w-0 gap-5 text-sm sm:grid-cols-2">
            <div><dt class="font-semibold text-slate-600">Nama pemesan</dt><dd>{{ $order->buyer_name }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Kontak pemesan</dt><dd>{{ $order->buyer_contact }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Limbah</dt><dd>{{ $order->listing?->category ?? 'Listing tidak tersedia' }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Berat pesanan</dt><dd>{{ $order->requested_weight }} kg</dd></div>
            <div><dt class="font-semibold text-slate-600">Harga per kg saat pengajuan</dt><dd>Rp {{ $order->price_per_kg }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Status</dt><dd>{{ $statusLabels[$order->status] ?? $order->status }}</dd></div>
        </dl>
        @if ($order->status === 'pending')
            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
                @can('bursa.update')
                    <a href="{{ route('bursa.order.edit', $order) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Edit pesanan</a>
                @endcan
                @can('bursa.delete')
                    <form action="{{ route('bursa.order.destroy', $order) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Batalkan pesanan</button>
                    </form>
                @endcan
            </div>
        @endif
    </x-card>
    <x-card title="Riwayat penanganan">
        <dl class="grid gap-5 text-sm sm:grid-cols-2">
            <div><dt class="font-semibold text-slate-600">Diajukan pada</dt><dd>{{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Ditangani oleh</dt><dd>{{ $order->approver?->name ?? 'Belum ada keputusan Admin' }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Waktu keputusan</dt><dd>{{ $order->approved_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div><dt class="font-semibold text-slate-600">Alasan keputusan</dt><dd class="whitespace-pre-wrap">{{ $order->decision_reason ?: '—' }}</dd></div>
        </dl>
    </x-card>
    @if ($order->status === 'pending')
        @can('order-limbah.approve')
            <x-card title="Persetujuan Admin">
                <p class="mb-5 text-sm text-slate-500">Persetujuan mengurangi stok limbah sesuai berat pesanan. Penolakan mempertahankan stok.</p>
                <form action="{{ route('bursa.order.approve', $order) }}" method="POST" class="max-w-2xl space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label for="reason" class="block text-sm font-semibold text-slate-700">Alasan keputusan (opsional)</label>
                        <textarea id="reason" name="reason" rows="3" maxlength="500" aria-invalid="{{ $errors->has('reason') ? 'true' : 'false' }}" @error('reason') aria-describedby="reason_error" @enderror class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">{{ is_scalar(old('reason')) ? (string) old('reason') : '' }}</textarea>
                        @error('reason')
                            <p id="reason_error" role="alert" class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" name="action" value="approve" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Setujui pesanan</button>
                        <button type="submit" name="action" value="reject" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Tolak pesanan</button>
                    </div>
                </form>
            </x-card>
        @endcan
    @endif
</div>
@endsection
