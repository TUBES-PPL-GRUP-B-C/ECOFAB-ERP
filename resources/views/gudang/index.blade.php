@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Ringkasan Gudang & Inventaris"
        subtitle="Monitoring saldo material, lokasi penyimpanan, dan peringatan stok kritis"
    >
        @can('gudang.create')
            <x-slot:actions>
                <a href="{{ route('gudang.grn.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    + Penerimaan GRN
                </a>
                <a href="{{ route('gudang.mutasi.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    + Mutasi Stok
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            label="Total Fasilitas Gudang"
            value="{{ $totalWarehouses }}"
            help="Gudang aktif operasional"
        />
        <x-stat-card
            label="Total Jenis Material"
            value="{{ $totalMaterials }}"
            help="Bahan baku terdaftar"
        />
        <x-stat-card
            label="Stok Kritis / Menipis"
            value="{{ $lowStockCount }}"
            help="Di bawah stok minimum"
            color="{{ $lowStockCount > 0 ? 'red' : 'emerald' }}"
        />
        <x-stat-card
            label="Dokumen GRN Terbaru"
            value="{{ $recentGrn->count() }}"
            help="Penerimaan barang"
        />
    </div>

    @if($lowStockCount > 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-amber-900">Peringatan: Terdapat {{ $lowStockCount }} Bahan Baku Berada di Bawah Batas Minimum!</h4>
                    <p class="text-xs text-amber-700">Segera lakukan koordinasi pengadaan dengan Bagian Pembelian (Purchase Order).</p>
                </div>
                <a href="{{ route('gudang.material.index') }}" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                    Lihat Bahan
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Riwayat Mutasi Terkini -->
        <x-card title="Mutasi Stok Terkini (FR-11)" subtitle="5 catatan pergerakan barang terakhir">
            @if($recentMovements->isEmpty())
                <x-empty-state title="Belum ada riwayat mutasi" description="Pergerakan stok akan dicatat di sini." />
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recentMovements as $mov)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $mov->material?->name ?? 'Material #' . $mov->material_id }}</p>
                                <p class="text-xs text-slate-500">{{ $mov->movement_date?->format('d M Y H:i') }} &bull; Ref: {{ $mov->reference_type }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-slate-900">{{ number_format($mov->qty, 2) }}</span>
                                <x-badge variant="{{ $mov->type === 'in' ? 'success' : ($mov->type === 'out' ? 'danger' : 'info') }}">
                                    {{ strtoupper($mov->type) }}
                                </x-badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Penerimaan GRN Terkini -->
        <x-card title="Penerimaan Barang Terkini (FR-08)" subtitle="5 dokumen GRN terakhir">
            @if($recentGrn->isEmpty())
                <x-empty-state title="Belum ada dokumen GRN" description="Penerimaan material akan muncul di sini." />
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recentGrn as $grn)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $grn->grn_number }}</p>
                                <p class="text-xs text-slate-500">Gudang: {{ $grn->warehouse?->name }} &bull; {{ $grn->received_date?->format('d M Y') }}</p>
                            </div>
                            <div>
                                <x-badge variant="success">{{ strtoupper($grn->status) }}</x-badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
