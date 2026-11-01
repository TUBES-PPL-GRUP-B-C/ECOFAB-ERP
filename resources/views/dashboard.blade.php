@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Konsol Eksekutif Manufaktur Sirkular"
        subtitle="Lantai Produksi Fasilitas Unit II - Cirebon Barat, Jawa Barat"
        badge="ISO 14044 Terverifikasi"
    >
        <x-slot:icon>
            <x-icon name="dashboard" class="w-5 h-5 text-emerald-400" />
        </x-slot:icon>

        <x-slot:actions>
            @if(app()->environment('local'))
                <div class="flex items-center bg-white p-1 rounded-xl border border-slate-200 shadow-2xs space-x-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2">SIMULASI PERAN:</span>
                    <a 
                        href="{{ route('simulasi.peran', 'admin') }}" 
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors {{ auth()->user()->hasRole('admin') ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Admin Pabrik
                    </a>
                    <a 
                        href="{{ route('simulasi.peran', 'produksi') }}" 
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors {{ auth()->user()->hasRole('produksi') ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Staf Operasional
                    </a>
                    <a 
                        href="{{ route('simulasi.peran', 'penjualan') }}" 
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors {{ auth()->user()->hasRole('penjualan') ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Staf Komersial
                    </a>
                </div>
            @endif
        </x-slot:actions>
    </x-page-header>

    <!-- Banner Hero & Placeholder FR-02 -->
    <div class="rounded-2xl p-6 sm:p-8 bg-gradient-to-r from-brand-950 via-brand-900 to-emerald-900 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-3">
                PABRIK MANUFAKTUR ROTAN & KAYU HIJAU CIREBON &bull; FONDASI TERCAPAI
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat Datang di EcoFab ERP</h2>
            <p class="text-xs sm:text-sm text-slate-200 mt-2 leading-relaxed">
                Fondasi arsitektur modular monolit MVC, autentikasi terpadu, dan RBAC Spatie (FR-01) telah siap digunakan oleh 7 anggota tim pengembang. Halaman Dashboard analitik jejak karbon (kg CO₂e) dan bursa limbah lengkap (FR-02) akan dibangun pada branch fitur terpisah.
            </p>
        </div>
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-600/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Informasi Profil dan Hak Akses Saat Ini -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <x-card title="Pengguna Aktif" accent="true" accentColor="bg-brand-800">
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-xl bg-brand-900 text-white font-black text-lg flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900">{{ auth()->user()->name }}</h4>
                        <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Kelompok:</span>
                    <span class="font-bold text-emerald-800">{{ auth()->user()->kelompok_peran }}</span>
                </div>
            </div>
        </x-card>

        <x-card title="Peran Sistem (RBAC)" accent="true" accentColor="bg-emerald-600">
            <div class="space-y-2">
                <div class="flex flex-wrap gap-1.5">
                    @foreach(auth()->user()->roles as $r)
                        <x-badge status="selesai" :label="strtoupper($r->name)" />
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 pt-2">
                    Akses menu sidebar di sebelah kiri otomatis menyesuaikan dengan izin peran yang Anda pegang.
                </p>
            </div>
        </x-card>

        <x-card title="Pintasan Standar Tim" accent="true" accentColor="bg-sky-600">
            <ul class="text-xs text-slate-600 space-y-2">
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                    Routes modular: <code class="ml-1 text-slate-800 font-mono bg-slate-100 px-1 rounded-sm">routes/modules/*.php</code>
                </li>
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                    Menu sidebar: <code class="ml-1 text-slate-800 font-mono bg-slate-100 px-1 rounded-sm">config/menu.php</code>
                </li>
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                    Helper angka: <code class="ml-1 text-slate-800 font-mono bg-slate-100 px-1 rounded-sm">format_angka()</code>
                </li>
            </ul>
        </x-card>
    </div>
</div>
@endsection
