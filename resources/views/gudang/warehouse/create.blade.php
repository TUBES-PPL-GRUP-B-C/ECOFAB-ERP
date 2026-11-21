@extends('layouts.app')

@section('title', 'Tambah Gudang Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Tambah Master Gudang Baru (FR-09-C)</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('gudang.warehouses.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="code" class="form-label fw-semibold">Kode Gudang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="Contoh: GDG-UTM-01" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Gudang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Gudang Bahan Baku Utama" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold">Alamat / Lokasi Fasilitas</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap atau letak fisik di area pabrik...">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="capacity" class="form-label fw-semibold">Kapasitas Maksimal (m³ atau ton)</label>
                            <input type="number" step="0.01" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity') }}" placeholder="Contoh: 1500.00">
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Status Gudang Aktif</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('gudang.warehouses.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i> Simpan Gudang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
