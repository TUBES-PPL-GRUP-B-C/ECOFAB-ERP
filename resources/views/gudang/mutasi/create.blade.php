@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <x-page-header
        title="Buat Mutasi Stok (FR-11-C)"
        subtitle="Form transfer stok antar-fasilitas atau penyesuaian opname fisik"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.mutasi.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Formulir Mutasi Stok">
        <form action="{{ route('gudang.mutasi.store') }}" method="POST" class="space-y-4" x-data="{ type: 'transfer' }">
            @csrf

            <!-- Pilihan Jenis Mutasi -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Jenis Mutasi <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex cursor-pointer rounded-xl border p-4 shadow-xs focus:outline-hidden"
                        :class="type === 'transfer' ? 'border-brand-600 bg-brand-50/30 ring-2 ring-brand-500/20' : 'border-slate-200 bg-white'">
                        <input type="radio" name="type" value="transfer" x-model="type" class="sr-only">
                        <span class="flex flex-1 flex-col">
                            <span class="block text-sm font-bold text-slate-900">Transfer Antar Gudang</span>
                            <span class="mt-1 flex items-center text-xs text-slate-500">Pindahkan saldo dari satu fasilitas ke fasilitas lain.</span>
                        </span>
                    </label>

                    <label class="relative flex cursor-pointer rounded-xl border p-4 shadow-xs focus:outline-hidden"
                        :class="type === 'adjustment' ? 'border-brand-600 bg-brand-50/30 ring-2 ring-brand-500/20' : 'border-slate-200 bg-white'">
                        <input type="radio" name="type" value="adjustment" x-model="type" class="sr-only">
                        <span class="flex flex-1 flex-col">
                            <span class="block text-sm font-bold text-slate-900">Penyesuaian (Stock Opname)</span>
                            <span class="mt-1 flex items-center text-xs text-slate-500">Koreksi saldo fisik aktual jika terdapat selisih.</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Material -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Pilih Material <span class="text-rose-500">*</span></label>
                <select name="material_id" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">-- Pilih Bahan Baku --</option>
                    @foreach($materials as $mat)
                        <option value="{{ $mat->id }}" @selected(old('material_id') == $mat->id)>
                            {{ $mat->sku }} - {{ $mat->name }} (Satuan: {{ $mat->unit }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Bagian Transfer -->
            <div x-show="type === 'transfer'" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Gudang Asal <span class="text-rose-500">*</span></label>
                        <select name="from_warehouse_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                            <option value="">-- Pilih Gudang Asal --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" @selected(old('from_warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Gudang Tujuan <span class="text-rose-500">*</span></label>
                        <select name="to_warehouse_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                            <option value="">-- Pilih Gudang Tujuan --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" @selected(old('to_warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kuantitas Transfer <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="qty" value="{{ old('qty') }}" placeholder="Contoh: 50.00"
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
            </div>

            <!-- Bagian Opname -->
            <div x-show="type === 'adjustment'" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Gudang yang Diopname <span class="text-rose-500">*</span></label>
                    <select name="warehouse_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                        <option value="">-- Pilih Lokasi Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(old('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Jumlah Fisik Aktual <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="actual_qty" value="{{ old('actual_qty') }}" placeholder="Masukkan kuantitas fisik riil"
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <p class="mt-1 text-xs text-slate-500">Sistem akan otomatis menghitung selisih dan menyesuaikan saldo akhir.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Catatan / Alasan Mutasi</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Persiapan bahan untuk lini produksi Cirebon..."
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('gudang.mutasi.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Proses Mutasi
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
