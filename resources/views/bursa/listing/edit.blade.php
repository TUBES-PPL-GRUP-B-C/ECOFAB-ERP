@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Edit Listing #{{ $listing->id }}" subtitle="Perbarui data listing Bursa Limbah.">
        <x-slot:actions>
            <a href="{{ route('bursa.listing.show', $listing) }}" class="text-sm font-semibold text-brand-800 hover:underline">Kembali ke detail</a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Data listing">
        @if ($wasteRecordIds->isEmpty())
            <p role="status" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Data limbah produksi FR-22 belum tersedia. Listing baru dapat dibuat setelah data limbah produksi tersedia.</p>
        @else
            <form action="{{ route('bursa.listing.update', $listing) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <div class="space-y-1.5">
                    <label for="waste_record_id" class="block text-xs font-semibold text-slate-700">Limbah produksi <span class="text-rose-500">*</span></label>
                    <select id="waste_record_id" name="waste_record_id" required aria-invalid="{{ $errors->has('waste_record_id') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 @error('waste_record_id') border-rose-500 @enderror">
                        <option value="">Pilih ID limbah produksi</option>
                        @foreach ($wasteRecordIds as $id)
                            <option value="{{ $id }}" @selected((string) old('waste_record_id', $listing->waste_record_id) === (string) $id)>Limbah produksi #{{ $id }}</option>
                        @endforeach
                    </select>
                    @error('waste_record_id') <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p> @enderror
                </div>

                <x-form-input name="category" label="Kategori" :value="$listing->category" :required="true" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="price_per_kg" class="block text-xs font-semibold text-slate-700">Harga per kg (Rp) <span class="text-rose-500">*</span></label>
                        <input id="price_per_kg" name="price_per_kg" type="number" min="0" max="9999999999999.99" step="0.01" value="{{ old('price_per_kg', $listing->price_per_kg) }}" required aria-invalid="{{ $errors->has('price_per_kg') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 @error('price_per_kg') border-rose-500 @enderror">
                        @error('price_per_kg') <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="available_weight" class="block text-xs font-semibold text-slate-700">Berat tersedia (kg) <span class="text-rose-500">*</span></label>
                        <input id="available_weight" name="available_weight" type="number" min="0.001" max="999999999.999" step="0.001" value="{{ old('available_weight', $listing->available_weight) }}" required aria-invalid="{{ $errors->has('available_weight') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 @error('available_weight') border-rose-500 @enderror">
                        @error('available_weight') <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>
                @if ($listing->photo_path)
                    <div>
                        <p class="mb-2 text-sm font-semibold text-slate-700">Foto saat ini</p>
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($listing->photo_path) }}" alt="Foto limbah {{ $listing->category }}" class="max-h-48 rounded-lg object-contain">
                    </div>
                @endif
                <div class="space-y-1.5">
                    <label for="photo" class="block text-xs font-semibold text-slate-700">Ganti foto limbah (opsional)</label>
                    <input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-invalid="{{ $errors->has('photo') ? 'true' : 'false' }}" class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 @error('photo') border-rose-500 @enderror">
                    <p class="text-xs text-slate-500">JPG, PNG, atau WebP; maksimal 2 MB. Kosongkan untuk mempertahankan foto lama.</p>
                    @error('photo') <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-900">Simpan perubahan</button>
                </div>
            </form>
        @endif
    </x-card>
</div>
@endsection

