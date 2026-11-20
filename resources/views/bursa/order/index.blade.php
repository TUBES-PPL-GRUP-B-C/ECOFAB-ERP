@extends('layouts.app')

@section('content')
@php
    $statusLabels = ['pending' => 'Menunggu persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];
@endphp
<div class="min-w-0 space-y-6">
    <x-page-header title="Pesanan Limbah (FR-25)" subtitle="Daftar pesanan dan tindak lanjut pemesanan limbah">
        <x-slot:actions>
            <a href="{{ route('bursa.listing.index') }}" class="inline-flex rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lihat listing limbah</a>
        </x-slot:actions>
    </x-page-header>
    <x-card title="Daftar pesanan" subtitle="Informasi pemesan, limbah, berat pesanan, dan status penanganan">
        <x-table aria-label="Daftar pesanan limbah">
            <x-slot:header>
                <tr>
                    <th scope="col" class="px-6 py-3.5">Pesanan</th>
                    <th scope="col" class="px-6 py-3.5">Pemesan</th>
                    <th scope="col" class="px-6 py-3.5">Limbah</th>
                    <th scope="col" class="px-6 py-3.5">Berat pesanan</th>
                    <th scope="col" class="px-6 py-3.5">Status</th>
                    <th scope="col" class="px-6 py-3.5">Aksi</th>
                </tr>
            </x-slot:header>
            @forelse ($orders as $order)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">#{{ $order->id }}</td>
                    <td class="max-w-xs break-words px-6 py-4 [overflow-wrap:anywhere]">
                        <p class="font-semibold text-slate-800">{{ $order->buyer_name }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $order->buyer_contact }}</p>
                    </td>
                    <td class="max-w-xs break-words px-6 py-4 [overflow-wrap:anywhere]">{{ $order->listing?->category ?? 'Listing tidak tersedia' }}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{ $order->requested_weight }} kg</td>
                    <td class="px-6 py-4">{{ $statusLabels[$order->status] ?? $order->status }}</td>
                    <td class="px-6 py-4"><a href="{{ route('bursa.order.show', $order) }}" class="font-semibold text-brand-800 hover:underline" aria-label="Detail pesanan {{ $order->id }}">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 sm:px-6"><x-empty-state title="Belum ada pesanan" description="Pesanan limbah akan muncul di sini setelah diajukan melalui katalog publik." /></td></tr>
            @endforelse
        </x-table>
        <x-pagination :paginator="$orders" />
    </x-card>
</div>
@endsection
