@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header :title="$workOrder->wo_number" :subtitle="'Detail rencana produksi ' . $workOrder->product_name">
        <x-slot:actions>
            <a href="{{ route('produksi.wo.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke daftar</a>
            @if ($workOrder->isDraft())
                @can('produksi.update')
                    <a href="{{ route('produksi.wo.edit', $workOrder) }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Ubah Work Order</a>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    <x-card title="Informasi Work Order">
        <dl class="grid gap-5 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Work Order</dt>
                <dd class="mt-1 break-words font-semibold text-slate-900">{{ $workOrder->wo_number }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Produk</dt>
                <dd class="mt-1 break-words font-semibold text-slate-900">{{ $workOrder->product_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</dt>
                <dd class="mt-1">@include('produksi.wo._status', ['status' => $workOrder->status])</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Target Kuantitas</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ number_format((float) $workOrder->target_quantity, 2, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Mulai</dt>
                <dd class="mt-1 text-slate-700">{{ $workOrder->start_date?->format('d/m/Y') ?? 'Belum diatur' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Tenggat</dt>
                <dd class="mt-1 text-slate-700">{{ $workOrder->due_date?->format('d/m/Y') ?? 'Belum diatur' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dibuat Oleh</dt>
                <dd class="mt-1 break-words text-slate-700">{{ $workOrder->creator?->name ?? 'Tidak tersedia' }}</dd>
            </div>
            @if ($workOrder->actual_quantity !== null)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kuantitas Aktual</dt>
                    <dd class="mt-1 text-slate-700">{{ number_format((float) $workOrder->actual_quantity, 2, ',', '.') }}</dd>
                </div>
            @endif
            @if ($workOrder->completed_at !== null)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Waktu Selesai</dt>
                    <dd class="mt-1 text-slate-700">{{ $workOrder->completed_at->format('d/m/Y H:i') }}</dd>
                </div>
            @endif
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan</dt>
                <dd class="mt-1 whitespace-pre-wrap break-words text-slate-700">{{ $workOrder->notes ?: 'Tidak ada catatan tambahan.' }}</dd>
            </div>
        </dl>
    </x-card>

    @if ($workOrder->isDraft())
        <x-card title="Kesiapan Produksi" subtitle="Work Order hanya dapat dimulai setelah resep BoM dan ketersediaan stok bahan terverifikasi.">
            <div class="space-y-4">
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
                    <p class="font-semibold">Work Order belum siap dimulai.</p>
                    <p class="mt-1">Integrasi resep BoM dan verifikasi stok bahan belum tersedia. Nama produk saat ini dicatat secara manual untuk perencanaan Draft. Proses mulai produksi masih diblokir sampai dependensi tersebut tersedia.</p>
                </div>
                @can('wo.start')
                    <form action="{{ route('produksi.wo.start', $workOrder) }}" method="POST" onsubmit="return confirm('Periksa kesiapan dan mulai Work Order ini? Produksi hanya dapat dimulai jika BoM dan stok bahan terverifikasi.');">
                        @csrf
                        <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Periksa dan Mulai Produksi</button>
                    </form>
                @endcan
            </div>
        </x-card>

        @can('produksi.delete')
            <x-card title="Hapus Work Order" subtitle="Hanya Draft tanpa referensi bahan atau limbah yang dapat dihapus.">
                @if ($workOrder->materials_count === 0 && $workOrder->waste_records_count === 0)
                    <form action="{{ route('produksi.wo.destroy', $workOrder) }}" method="POST" onsubmit="return confirm('Hapus Work Order Draft ini? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Hapus Draft</button>
                    </form>
                @else
                    <p class="text-sm text-slate-600">Work Order ini memiliki referensi bahan atau limbah sehingga tidak dapat dihapus.</p>
                @endif
            </x-card>
        @endcan
    @endif
</div>
@endsection
