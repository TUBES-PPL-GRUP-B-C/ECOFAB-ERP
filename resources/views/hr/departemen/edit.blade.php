@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Ubah Departemen" :subtitle="'Perbarui data departemen ' . $department->name">
        <x-slot:actions>
            <a href="{{ route('hr.departemen.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">
                Kembali ke daftar
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Perubahan Departemen" subtitle="Pastikan kode departemen tetap unik.">
        <form action="{{ route('hr.departemen.update', $department) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form-input
                    name="code"
                    label="Kode Departemen"
                    :value="$department->code"
                    :required="true"
                />

                <x-form-input
                    name="name"
                    label="Nama Departemen"
                    :value="$department->name"
                    :required="true"
                />
            </div>

            <div class="space-y-1.5">
                <label for="description" class="block text-xs font-semibold text-slate-700">Keterangan</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('description') border-rose-500 @enderror"
                >{{ old('description', $department->description) }}</textarea>
                @error('description')
                    <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('hr.departemen.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
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
