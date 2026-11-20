@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <x-page-header
        title="Tambah Gudang Baru (FR-09-C)"
        subtitle="Daftarkan lokasi fasilitas penyimpanan material atau produk"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.gudang.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Formulir Data Gudang">
        <form action="{{ route('gudang.gudang.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kode Gudang <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Contoh: GDG-UTM-01" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('code') border-rose-500 @enderror">
                    @error('code')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Status Operasional</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                        <option value="active" @selected(old('status') === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Gudang <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Gudang Bahan Baku Utama Cirebon" required
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('name') border-rose-500 @enderror">
                @error('name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Alamat / Lokasi Fasilitas</label>
                <textarea name="address" rows="3" placeholder="Masukkan alamat lengkap atau letak fisik di area pabrik..."
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">{{ old('address') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kapasitas Maksimal (m³ atau ton)</label>
                <input type="number" step="0.01" name="capacity" value="{{ old('capacity') }}" placeholder="Contoh: 1500.00"
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('gudang.gudang.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Gudang
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
