@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Ubah Absensi" :subtitle="'Perbarui data kehadiran ' . ($attendance->employee?->name ?? 'Karyawan')">
        <x-slot:actions>
            <a href="{{ route('hr.absensi.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">
                Kembali ke daftar
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Perubahan Absensi" subtitle="Perbarui shift, jam check-in/check-out, atau status kehadiran karyawan.">
        <form action="{{ route('hr.absensi.update', $attendance) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

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
                            <option value="{{ $employee->id }}" @selected((string) old('employee_id', $attendance->employee_id) === (string) $employee->id)>
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
                    :value="$attendance->work_date"
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
                            <option value="{{ $shiftOption }}" @selected(old('shift', $attendance->shift) === $shiftOption)>
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
                    :value="$attendance->check_in ? substr($attendance->check_in, 0, 5) : null"
                />

                <x-form-input
                    name="check_out"
                    type="time"
                    label="Waktu Check-Out"
                    :value="$attendance->check_out ? substr($attendance->check_out, 0, 5) : null"
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
                        <option value="hadir" @selected(old('status', $attendance->status) === 'hadir')>Hadir</option>
                        <option value="izin" @selected(old('status', $attendance->status) === 'izin')>Izin</option>
                        <option value="sakit" @selected(old('status', $attendance->status) === 'sakit')>Sakit</option>
                        <option value="alpa" @selected(old('status', $attendance->status) === 'alpa')>Alpa</option>
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
                    aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('notes') border-rose-500 @enderror"
                >{{ old('notes', $attendance->notes) }}</textarea>
                @error('notes')
                    <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('hr.absensi.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
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
