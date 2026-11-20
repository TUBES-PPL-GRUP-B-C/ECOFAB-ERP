@extends('layouts.app')

@section('content')
<div class="max-w-2xl space-y-6">
    <x-page-header
        title="Edit Catatan GRN (FR-08-U)"
        subtitle="Dokumen {{ $receipt->grn_number }}"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.grn.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Catatan Dokumen">
        <form action="{{ route('gudang.grn.update', $receipt->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="rounded-lg bg-slate-50 p-4 border border-slate-200 text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Nomor GRN:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $receipt->grn_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Gudang Penerima:</span>
                    <span class="font-semibold text-slate-800">{{ $receipt->warehouse?->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tanggal Diterima:</span>
                    <span class="text-slate-800">{{ $receipt->received_date?->format('d/m/Y') }}</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Catatan Keterangan / Surat Jalan</label>
                <textarea name="notes" rows="4" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">{{ old('notes', $receipt->notes) }}</textarea>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                <button type="button" onclick="if(confirm('Batalkan dokumen GRN ini? Saldo stok yang telah diterima akan ditarik kembali.')) document.getElementById('cancel-grn-form').submit();" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                    Batalkan GRN & Kembalikan Stok
                </button>
                <div class="flex gap-2">
                    <a href="{{ route('gudang.grn.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </a>
                    <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                        Simpan Catatan
                    </button>
                </div>
            </div>
        </form>

        <form id="cancel-grn-form" action="{{ route('gudang.grn.destroy', $receipt->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </x-card>
</div>
@endsection
