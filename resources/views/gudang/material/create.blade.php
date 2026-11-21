@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <x-page-header
        title="Tambah Material Baru (FR-10-C)"
        subtitle="Daftarkan bahan baku sirkular beserta faktor emisi dan batas stok minimum"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.material.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Formulir Data Bahan Baku">
        <form action="{{ route('gudang.material.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">SKU Material <span class="text-rose-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" placeholder="Contoh: MAT-ROT-001" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('sku') border-rose-500 @enderror">
                    @error('sku')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kategori Material <span class="text-rose-500">*</span></label>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="Contoh: Kayu, Rotan, Perekat" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('category') border-rose-500 @enderror">
                    @error('category')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Bahan Baku <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Rotan Sega Anyaman Terbarukan" required
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('name') border-rose-500 @enderror">
                @error('name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Satuan Ukuran <span class="text-rose-500">*</span></label>
                    <input type="text" name="unit" value="{{ old('unit', 'kg') }}" placeholder="kg, liter, meter, pcs" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Batas Stok Minimum <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="min_stock" value="{{ old('min_stock', '10.00') }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Biaya Standar Satuan (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost', '0') }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Referensi Faktor Emisi (Green BoM)</label>
                <select name="emission_factor_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">-- Pilih Faktor Emisi Karbon (Opsional) --</option>
                    @foreach($emissionFactors as $ef)
                        <option value="{{ $ef->id }}" @selected(old('emission_factor_id') == $ef->id)>
                            {{ $ef->name }} ({{ number_format($ef->value_kg_co2e, 4) }} kg CO₂e / {{ $ef->unit }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Akan digunakan otomatis saat penyusunan resep ramah lingkungan pada Green BoM (FR-15 & FR-16).</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('gudang.material.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Material
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
