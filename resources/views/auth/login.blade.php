<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - {{ config('app.name', 'EcoFab ERP') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-emerald-950 via-slate-900 to-slate-950 font-sans">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-800 text-white shadow-xl shadow-emerald-900/40 mb-4 ring-4 ring-emerald-500/20">
                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-2-9m-7-3a9 9 0 00-9 9c0 4.97 4.03 9 9 9 0-4.97 4.03-9 9-9z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white">EcoFab ERP</h1>
            <p class="text-xs font-semibold text-emerald-400 uppercase tracking-widest mt-1">Platform Manufaktur Sirkular & Karbon</p>
            <p class="text-xs text-slate-400 mt-2">Mendukung SDG 12: Konsumsi & Produksi Bertanggung Jawab</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 border border-slate-100">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Masuk ke Portal Internal</h2>
                <p class="text-xs text-slate-500 mt-1">Gunakan akun kredensial Anda untuk mengakses sistem ERP.</p>
            </div>

            <x-flash />

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <x-form-input
                    name="email"
                    label="Alamat Email"
                    type="email"
                    placeholder="nama@ecofab.local"
                    required="true"
                />

                <x-form-input
                    name="password"
                    label="Kata Sandi"
                    type="password"
                    placeholder="••••••••"
                    required="true"
                />

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center text-slate-600">
                        <input type="checkbox" name="remember" class="rounded-sm border-slate-300 text-brand-700 focus:ring-brand-500 mr-2">
                        Ingat sesi saya
                    </label>
                    <span class="text-slate-400">Hubungi Admin jika lupa</span>
                </div>

                <button 
                    type="submit" 
                    class="w-full mt-2 py-2.5 px-4 rounded-xl bg-brand-900 hover:bg-brand-950 text-white font-semibold text-sm shadow-md shadow-brand-900/20 transition-all hover:shadow-lg focus:ring-2 focus:ring-brand-500 focus:outline-hidden"
                >
                    Masuk ke Konsol &rarr;
                </button>
            </form>

            <!-- Bantuan Akun Demo / Development -->
            @if(app()->environment('local'))
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-2">Akun Uji Coba Cepat (Password: password123):</p>
                    <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                        <button type="button" onclick="document.getElementById('email').value='admin@ecofab.local'; document.getElementById('password').value='password123';" class="p-1.5 text-left rounded-md bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-900 font-medium border border-slate-200/60 transition-colors">
                            &bull; <strong>Admin:</strong> admin@ecofab.local
                        </button>
                        <button type="button" onclick="document.getElementById('email').value='produksi@ecofab.local'; document.getElementById('password').value='password123';" class="p-1.5 text-left rounded-md bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-900 font-medium border border-slate-200/60 transition-colors">
                            &bull; <strong>Produksi:</strong> produksi@ecofab.local
                        </button>
                        <button type="button" onclick="document.getElementById('email').value='gudang@ecofab.local'; document.getElementById('password').value='password123';" class="p-1.5 text-left rounded-md bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-900 font-medium border border-slate-200/60 transition-colors">
                            &bull; <strong>Gudang:</strong> gudang@ecofab.local
                        </button>
                        <button type="button" onclick="document.getElementById('email').value='penjualan@ecofab.local'; document.getElementById('password').value='password123';" class="p-1.5 text-left rounded-md bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-900 font-medium border border-slate-200/60 transition-colors">
                            &bull; <strong>Penjualan:</strong> penjualan@ecofab.local
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-6 text-center text-xs text-slate-400">
            <a href="{{ route('public.bursa.index') }}" class="hover:text-emerald-400 transition-colors">
                &larr; Lihat Katalog Publik Bursa Limbah
            </a>
        </div>
    </div>
</body>
</html>
