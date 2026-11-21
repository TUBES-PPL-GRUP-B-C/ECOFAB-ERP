@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <x-page-header
        title="Edit Gudang (FR-09-U)"
        subtitle="Perbarui data fasilitas {{ $warehouse->name }}"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.gudang.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Edit Informasi Gudang">
        <form action="{{ route('gudang.gudang.update', $warehouse->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kode Gudang <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $warehouse->code) }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Status Operasional</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                        <option value="active" @selected(old('status', $warehouse->status) === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status', $warehouse->status) === 'inactive')>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Gudang <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Alamat / Lokasi Fasilitas</label>
                <textarea name="address" rows="3" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">{{ old('address', $warehouse->address) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kapasitas Maksimal (m³ atau ton)</label>
                <input type="number" step="0.01" name="capacity" value="{{ old('capacity', $warehouse->capacity) }}"
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                <button type="button" onclick="if(confirm('Hapus gudang ini?')) document.getElementById('delete-form').submit();" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                    Hapus Gudang
                </button>
                <div class="flex gap-2">
                    <a href="{{ route('gudang.gudang.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </a>
                    <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                        Perbarui
                    </button>
                </div>
            </div>
        </form>

        <form id="delete-form" action="{{ route('gudang.gudang.destroy', $warehouse->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </x-card>
</div>
@endsection
