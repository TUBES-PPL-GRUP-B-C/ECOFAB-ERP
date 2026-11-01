@extends('layouts.public')

@section('content')
<div class="space-y-6">
    <div class="text-center max-w-xl mx-auto py-4">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
            Bursa Sirkular Terbuka
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">Katalog Limbah Manufaktur Ramah Lingkungan</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-2">
            Akses langsung untuk pengrajin lokal dan industri daur ulang untuk memanfaatkan sisa potongan kayu FSC, serat rotan, dan serbuk gergaji berkualitas.
        </p>
    </div>

    <!-- Kotak Info / Pencarian -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
        <div class="flex flex-col sm:flex-row gap-3">
            <input 
                type="text" 
                placeholder="Cari jenis limbah (serbuk, offcut rotan, dsb.)..." 
                class="flex-1 px-4 py-2 text-sm rounded-xl border border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
            >
            <button class="px-5 py-2 rounded-xl bg-brand-900 text-white font-semibold text-xs shadow-xs hover:bg-brand-950 transition-colors">
                Cari Limbah
            </button>
        </div>
    </div>

    <!-- Empty State / Placeholder Listing -->
    <x-card>
        <x-empty-state
            title="Katalog Publik Bursa Limbah (FR-24)"
            description="Katalog publik ini akan menampilkan data listing limbah produksi siap jual yang terhubung dengan siklus Work Order."
        />
    </x-card>
</div>
@endsection
