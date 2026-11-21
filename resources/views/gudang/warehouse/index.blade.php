@extends('layouts.app')

@section('title', 'Daftar Gudang (FR-09)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Kelola Gudang (FR-09)</h3>
        <p class="text-muted mb-0">Manajemen data master lokasi penyimpanan material dan produk.</p>
    </div>
    <a href="{{ route('gudang.warehouses.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i> Tambah Gudang Baru
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <!-- Search & Filter Form (FR-28) -->
        <form method="GET" action="{{ route('gudang.warehouses.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari kode, nama, atau alamat gudang..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary w-100">Cari</button>
            </div>
            @if(request('search'))
                <div class="col-md-2">
                    <a href="{{ route('gudang.warehouses.index') }}" class="btn btn-link text-decoration-none text-muted">Reset Filter</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Gudang</th>
                        <th>Alamat</th>
                        <th>Kapasitas</th>
                        <th>Jumlah Item Disimpan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $wh)
                        <tr>
                            <td><span class="badge bg-secondary font-monospace">{{ $wh->code }}</span></td>
                            <td class="fw-semibold">{{ $wh->name }}</td>
                            <td class="text-muted small">{{ Str::limit($wh->address, 50) ?? '-' }}</td>
                            <td>{{ $wh->capacity ? number_format($wh->capacity, 2) . ' m³' : 'Tidak Dibatasi' }}</td>
                            <td><span class="badge bg-info text-dark">{{ $wh->material_stocks_count }} jenis bahan</span></td>
                            <td>
                                @if($wh->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('gudang.warehouses.show', $wh->id) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('gudang.warehouses.edit', $wh->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('gudang.warehouses.destroy', $wh->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus gudang ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data gudang yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $warehouses->links() }}
        </div>
    </div>
</div>
@endsection
