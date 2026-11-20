@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Catat Absensi" subtitle="Rekam kehadiran harian karyawan beserta shift serta waktu check-in dan check-out.">
        <x-slot:actions>
            <a href="{{ route('hr.absensi.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">
                Kembali ke daftar
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Pencatatan Absensi" subtitle="Satu karyawan hanya dapat memiliki satu catatan absensi pada tanggal kerja yang sama.">
        <form action="{{ route('hr.absensi.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="employee_id" class="block text-xs font-semibold text-slate-700">
                        Karyawan <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="employee_id"
                        name="employee_id"
                        required
                        aria-invalid="{{ $errors->has('employee_id') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('employee_id') border-rose-500 @enderror"
                    >
                        <option value="">Pilih Karyawan</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>
                                {{ $employee->employee_code }} — {{ $employee->name }} ({{ $employee->department?->name ?? 'Tanpa Departemen' }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-input
                    name="work_date"
                    type="date"
                    label="Tanggal Kerja"
                    :value="date('Y-m-d')"
                    :required="true"
                />
            </div>

            <div class="grid gap-5 sm:grid-cols-4">
                <div class="space-y-1.5">
                    <label for="shift" class="block text-xs font-semibold text-slate-700">
                        Shift Kerja <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="shift"
                        name="shift"
                        required
                        aria-invalid="{{ $errors->has('shift') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('shift') border-rose-500 @enderror"
                    >
                        @foreach (\App\Models\Attendance::SHIFTS as $shiftOption)
                            <option value="{{ $shiftOption }}" @selected(old('shift', 'Pagi') === $shiftOption)>
                                Shift {{ $shiftOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('shift')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-input
                    name="check_in"
                    type="time"
                    label="Waktu Check-In"
                    help="Contoh: 08:00"
                />

                <x-form-input
                    name="check_out"
                    type="time"
                    label="Waktu Check-Out"
                    help="Harus >= waktu check-in"
                />

                <div class="space-y-1.5">
                    <label for="status" class="block text-xs font-semibold text-slate-700">
                        Status Kehadiran <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="status"
                        name="status"
                        required
                        aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('status') border-rose-500 @enderror"
                    >
                        <option value="hadir" @selected(old('status', 'hadir') === 'hadir')>Hadir</option>
                        <option value="izin" @selected(old('status') === 'izin')>Izin</option>
                        <option value="sakit" @selected(old('status') === 'sakit')>Sakit</option>
                        <option value="alpa" @selected(old('status') === 'alpa')>Alpa</option>
                    </select>
                    @error('status')
                        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="notes" class="block text-xs font-semibold text-slate-700">Catatan / Keterangan</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    placeholder="Keterangan tambahan seperti alasan izin/sakit atau lembur (opsional)"
                    aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('notes') border-rose-500 @enderror"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('hr.absensi.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Absensi
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
