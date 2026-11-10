@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Tambah Karyawan" subtitle="Catat personel baru beserta penempatan departemen, akun pengguna, dan foto profil.">
        <x-slot:actions>
            <a href="{{ route('hr.karyawan.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">
                Kembali ke daftar
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Data Karyawan" subtitle="Lengkapi kode unik karyawan, departemen, jabatan, serta tanggal bergabung.">
        <form action="{{ route('hr.karyawan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form-input
                    name="employee_code"
                    label="Kode Karyawan (NIP)"
                    :required="true"
                    placeholder="Contoh: EMP-2026-001"
                    help="Gunakan kode singkat unik maksimal 50 karakter."
                />

                <x-form-input
                    name="name"
                    label="Nama Lengkap"
                    :required="true"
                    placeholder="Contoh: Siti Rahmawati"
                />
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div class="space-y-1.5">
                    <label for="department_id" class="block text-xs font-semibold text-slate-700">
                        Departemen <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="department_id"
                        name="department_id"
                        required
                        aria-invalid="{{ $errors->has('department_id') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('department_id') border-rose-500 @enderror"
                    >
                        <option value="">Pilih Departemen</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>
                                {{ $department->code }} — {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-input
                    name="position"
                    label="Jabatan / Posisi"
                    :required="true"
                    placeholder="Contoh: Supervisor Produksi"
                />

                <x-form-input
                    name="join_date"
                    type="date"
                    label="Tanggal Bergabung"
                    :required="true"
                />
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-form-input
                    name="phone"
                    label="Nomor Telepon"
                    placeholder="Contoh: 081234567890"
                />

                <div class="space-y-1.5">
                    <label for="user_id" class="block text-xs font-semibold text-slate-700">
                        Tautkan Akun Pengguna (Opsional)
                    </label>
                    <select
                        id="user_id"
                        name="user_id"
                        aria-invalid="{{ $errors->has('user_id') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('user_id') border-rose-500 @enderror"
                    >
                        <option value="">Tidak ditautkan ke akun login</option>
                        @foreach ($users as $userAccount)
                            <option value="{{ $userAccount->id }}" @selected((string) old('user_id') === (string) $userAccount->id)>
                                {{ $userAccount->name }} ({{ $userAccount->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="status" class="block text-xs font-semibold text-slate-700">
                        Status Karyawan <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="status"
                        name="status"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('status') border-rose-500 @enderror"
                    >
                        <option value="active" @selected(old('status', 'active') === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Nonaktif</option>
                    </select>
                    @error('status')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="address" class="block text-xs font-semibold text-slate-700">Alamat Domisili</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        placeholder="Alamat lengkap tempat tinggal karyawan (opsional)"
                        aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('address') border-rose-500 @enderror"
                    >{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="photo" class="block text-xs font-semibold text-slate-700">Foto Profil (Opsional)</label>
                    <input
                        id="photo"
                        name="photo"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        aria-invalid="{{ $errors->has('photo') ? 'true' : 'false' }}"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1 file:text-sm file:font-semibold file:text-brand-800 @error('photo') border-rose-500 @enderror"
                    >
                    <p class="text-xs text-slate-500">Format JPG, PNG, atau WebP; ukuran maksimal 2 MB.</p>
                    @error('photo')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('hr.karyawan.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Karyawan
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
