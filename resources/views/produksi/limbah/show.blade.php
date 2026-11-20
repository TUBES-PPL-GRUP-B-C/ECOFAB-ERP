@extends('layouts.app')

@section('content')
@php($canManage = in_array($wasteRecord->status, ['available', 'sold'], true) && $wasteRecord->waste_listings_count === 0)
<div class="space-y-6">
    <x-page-header :title="$wasteRecord->waste_type" subtitle="Detail catatan limbah produksi.">
        <x-slot:actions>
            <a href="{{ route('produksi.limbah.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke daftar</a>
            @if ($canManage)
                @can('produksi.update')
                    <a href="{{ route('produksi.limbah.edit', $wasteRecord) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Ubah Limbah</a>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (! $canManage)
        <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900" role="status">
            <p class="font-semibold">Catatan limbah dilindungi.</p>
            <p class="mt-1">Catatan yang terdaftar atau memiliki referensi di Bursa tidak dapat diubah maupun dihapus di sini agar data Bursa tetap konsisten.</p>
        </div>
    @endif

    <x-card title="Informasi Limbah Produksi">
        <dl class="grid gap-5 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Limbah</dt>
                <dd class="mt-1 break-words font-semibold text-slate-900">{{ $wasteRecord->waste_type }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Berat</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ number_format((float) $wasteRecord->weight_kg, 3, ',', '.') }} kg</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kondisi</dt>
                <dd class="mt-1 break-words text-slate-700">{{ $wasteRecord->condition }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</dt>
                <dd class="mt-1">@include('produksi.limbah._status', ['status' => $wasteRecord->status])</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Asal Work Order</dt>
                <dd class="mt-1 break-words text-slate-700">
                    @if ($wasteRecord->workOrder)
                        <a href="{{ route('produksi.wo.show', $wasteRecord->workOrder) }}" class="font-semibold text-brand-800 hover:underline">{{ $wasteRecord->workOrder->wo_number }}</a>
                        <p class="mt-1">{{ $wasteRecord->workOrder->product_name }}</p>
                    @else
                        Tanpa Work Order
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dicatat Oleh</dt>
                <dd class="mt-1 break-words text-slate-700">{{ $wasteRecord->creator?->name ?? 'Tidak tersedia' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Referensi Bursa</dt>
                <dd class="mt-1 text-slate-700">{{ $wasteRecord->waste_listings_count }} catatan</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Pencatatan</dt>
                <dd class="mt-1 text-slate-700">{{ $wasteRecord->created_at?->format('d/m/Y H:i') ?? 'Tidak tersedia' }}</dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan</dt>
                <dd class="mt-1 whitespace-pre-wrap break-words text-slate-700">{{ $wasteRecord->notes ?: 'Tidak ada catatan tambahan.' }}</dd>
            </div>
        </dl>
    </x-card>

    @if ($canManage)
        @can('produksi.delete')
            <x-card title="Hapus Catatan Limbah" subtitle="Catatan yang dihapus tidak dapat dikembalikan.">
                <form action="{{ route('produksi.limbah.destroy', $wasteRecord) }}" method="POST" onsubmit="return confirm('Hapus catatan limbah ini? Tindakan ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Hapus Limbah</button>
                </form>
            </x-card>
        @endcan
    @endif
</div>
@endsection
