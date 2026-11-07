@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Ubah Supplier" :subtitle="'Perbarui data pemasok ' . $supplier->name">
        <x-slot:actions>
            <a href="{{ route('pembelian.supplier.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">
                Kembali ke daftar
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Perubahan Supplier" subtitle="Pastikan kode pemasok tetap unik dan lokasi asal sesuai.">
        <form action="{{ route('pembelian.supplier.update', $supplier) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form-input
                    name="code"
                    label="Kode Supplier"
                    :value="$supplier->code"
                    :required="true"
                />

                <x-form-input
                    name="name"
                    label="Nama Pemasok"
                    :value="$supplier->name"
                    :required="true"
                />
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-form-input
                    name="contact_person"
                    label="Nama Kontak (PIC)"
                    :value="$supplier->contact_person"
                />

                <x-form-input
                    name="phone"
                    label="Nomor Telepon"
                    :value="$supplier->phone"
                />

                <x-form-input
                    name="email"
                    type="email"
                    label="Alamat Email"
                    :value="$supplier->email"
                />
            </div>

            <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 space-y-4">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-brand-900">Lokasi Asal Bahan Baku (Eco-Passport)</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Informasi kota, provinsi, dan negara digunakan untuk ketertelusuran asal material pada Eco-Passport produk.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-3">
                    <x-form-input
                        name="city"
                        label="Kota / Kabupaten Asal"
                        :value="$supplier->city"
                        :required="true"
                    />

                    <x-form-input
                        name="province"
                        label="Provinsi Asal"
                        :value="$supplier->province"
                        :required="true"
                    />

                    <x-form-input
                        name="country"
                        label="Negara Asal"
                        :value="$supplier->country"
                        :required="true"
                    />
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="address" class="block text-xs font-semibold text-slate-700">Alamat Lengkap</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('address') border-rose-500 @enderror"
                    >{{ old('address', $supplier->address) }}</textarea>
                    @error('address')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="status" class="block text-xs font-semibold text-slate-700">
                        Status Pemasok <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="status"
                        name="status"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('status') border-rose-500 @enderror"
                    >
                        <option value="active" @selected(old('status', $supplier->status) === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status', $supplier->status) === 'inactive')>Nonaktif</option>
                    </select>
                    @error('status')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('pembelian.supplier.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
