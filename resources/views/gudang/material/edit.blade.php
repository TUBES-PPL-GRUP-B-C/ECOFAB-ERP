@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <x-page-header
        title="Edit Material (FR-10-U)"
        subtitle="Perbarui data bahan {{ $material->name }}"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.material.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Edit Data Bahan Baku">
        <form action="{{ route('gudang.material.update', $material->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">SKU Material <span class="text-rose-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku', $material->sku) }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kategori Material <span class="text-rose-500">*</span></label>
                    <input type="text" name="category" value="{{ old('category', $material->category) }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Bahan Baku <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $material->name) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Satuan Ukuran <span class="text-rose-500">*</span></label>
                    <input type="text" name="unit" value="{{ old('unit', $material->unit) }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Batas Stok Minimum <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="min_stock" value="{{ old('min_stock', $material->min_stock) }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Biaya Standar Satuan (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost', $material->unit_cost) }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Referensi Faktor Emisi</label>
                <select name="emission_factor_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">-- Pilih Faktor Emisi Karbon (Opsional) --</option>
                    @foreach($emissionFactors as $ef)
                        <option value="{{ $ef->id }}" @selected(old('emission_factor_id', $material->emission_factor_id) == $ef->id)>
                            {{ $ef->name }} ({{ number_format($ef->value_kg_co2e, 4) }} kg CO₂e / {{ $ef->unit }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                <button type="button" onclick="if(confirm('Hapus material ini?')) document.getElementById('delete-mat-form').submit();" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                    Hapus Material
                </button>
                <div class="flex gap-2">
                    <a href="{{ route('gudang.material.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </a>
                    <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                        Perbarui
                    </button>
                </div>
            </div>
        </form>

        <form id="delete-mat-form" action="{{ route('gudang.material.destroy', $material->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </x-card>
</div>
@endsection
