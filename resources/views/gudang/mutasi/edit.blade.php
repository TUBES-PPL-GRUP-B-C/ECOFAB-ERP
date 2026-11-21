@extends('layouts.app')

@section('content')
<div class="max-w-2xl space-y-6">
    <x-page-header
        title="Edit Catatan Mutasi (FR-11-U)"
        subtitle="Perbarui catatan audit mutasi stok"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.mutasi.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Catatan Mutasi Stok">
        <form action="{{ route('gudang.mutasi.update', $movement->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="rounded-lg bg-slate-50 p-4 border border-slate-200 text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Tipe Mutasi:</span>
                    <span class="font-bold text-slate-800">{{ strtoupper($movement->type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kuantitas:</span>
                    <span class="font-bold text-slate-800">{{ number_format($movement->qty, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Waktu:</span>
                    <span class="text-slate-800">{{ $movement->movement_date?->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Catatan Keterangan</label>
                <textarea name="notes" rows="4" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">{{ old('notes', $movement->notes) }}</textarea>
                <p class="mt-1 text-xs text-slate-400">Sesuai NFR-12, kuantitas saldo yang telah tervalidasi tidak dapat diubah; hanya catatan yang dapat disesuaikan.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('gudang.mutasi.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Catatan
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
