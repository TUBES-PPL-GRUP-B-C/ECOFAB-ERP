@extends('layouts.app')

@section('title', 'Detail Gudang - ' . $warehouse->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('gudang.warehouses.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Gudang
        </a>
        <h3 class="fw-bold mb-0">{{ $warehouse->name }}</h3>
        <span class="badge bg-secondary font-monospace">{{ $warehouse->code }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('gudang.warehouses.edit', $warehouse->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i> Edit Gudang
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Info Gudang -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Informasi Fasilitas</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <small class="text-muted d-block">Kode Gudang</small>
                        <span class="fw-semibold">{{ $warehouse->code }}</span>
                    </li>
                    <li class="list-group-item px-0">
                        <small class="text-muted d-block">Alamat / Lokasi</small>
                        <span>{{ $warehouse->address ?? '-' }}</span>
                    </li>
                    <li class="list-group-item px-0">
                        <small class="text-muted d-block">Kapasitas Maksimal</small>
                        <span>{{ $warehouse->capacity ? number_format($warehouse->capacity, 2) . ' m³' : 'Tidak Dibatasi' }}</span>
                    </li>
                    <li class="list-group-item px-0">
                        <small class="text-muted d-block">Status Operasional</small>
                        @if($warehouse->is_active)
                            <span class="badge bg-success-subtle text-success">Aktif Beroperasi</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Saldo Stok di Gudang Ini -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Saldo Stok Bahan Baku di Gudang Ini (FR-11)</h6>
                <a href="{{ route('gudang.mutasi.create') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-arrow-left-right me-1"></i> Buat Mutasi
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Nama Material</th>
                                <th>Kategori</th>
                                <th class="text-end">Jumlah Stok</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouse->materialStocks as $st)
                                <tr>
                                    <td><code>{{ $st->material->sku }}</code></td>
                                    <td class="fw-semibold">{{ $st->material->name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $st->material->category }}</span></td>
                                    <td class="text-end fw-bold">{{ number_format($st->qty, 2) }}</td>
                                    <td>{{ $st->material->unit }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada material yang tersimpan di gudang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
