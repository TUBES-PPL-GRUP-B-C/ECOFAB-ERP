@extends('layouts.public')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <!-- Kartu Paspor Digital -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm relative overflow-hidden">
        <div class="h-2 w-full bg-gradient-to-r from-emerald-600 to-brand-900 absolute top-0 left-0"></div>

        <div class="flex items-center justify-between pb-5 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-bold tracking-widest uppercase text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                    Eco-Passport Resmi
                </span>
                <h1 class="text-xl font-bold text-slate-900 mt-2">Kursi Rotan Teras Alami</h1>
                <p class="text-xs text-slate-500 font-mono">Kode Batch: {{ $kode ?? 'ECO-PASS-WO0013' }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center font-bold text-sm">
                QR
            </div>
        </div>

        <div class="py-6 border-b border-slate-100 text-center">
            <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Total Jejak Karbon Produk</span>
            <div class="mt-2 flex items-baseline justify-center space-x-1">
                <span class="text-4xl font-black text-brand-900">4,2</span>
                <span class="text-sm font-bold text-slate-600">kg CO₂e / unit</span>
            </div>
            <p class="text-xs text-emerald-700 font-semibold mt-1">
                ↓ 65% lebih rendah dibandingkan standar industri mebel konvensional (12 kg)
            </p>
        </div>

        <!-- Rantai Pasok Bahan Baku (SDG 12.8) -->
        <div class="pt-5 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Transparansi Asal Bahan Baku</h3>
            <ul class="text-xs space-y-2 text-slate-600">
                <li class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div>
                        <p class="font-semibold text-slate-800">Rotan Alami Cirebon</p>
                        <p class="text-[11px] text-slate-500">Pemasok Lokal: Koperasi Anyam Cirebon</p>
                    </div>
                    <span class="font-mono text-emerald-700 font-bold">1,8 kg CO₂e</span>
                </li>
                <li class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div>
                        <p class="font-semibold text-slate-800">Kayu Jati Tersertifikasi FSC</p>
                        <p class="text-[11px] text-slate-500">Asal: Majalengka, Jawa Barat</p>
                    </div>
                    <span class="font-mono text-emerald-700 font-bold">2,1 kg CO₂e</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
