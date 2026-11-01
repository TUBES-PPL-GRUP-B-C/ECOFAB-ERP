<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak - {{ config('app.name', 'EcoFab ERP') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans bg-slate-50">
    <div class="max-w-md w-full text-center">
        <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center mx-auto mb-4 shadow-xs">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-100 text-rose-800">
            Galat 403 &bull; Hak Akses Terbatas
        </span>

        <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-4">Akses Ditolak</h1>
        <p class="text-sm text-slate-600 mt-2">
            Peran akun Anda saat ini (<strong>{{ auth()->user()?->role_label ?? 'Tamu' }}</strong>) tidak memiliki izin yang cukup untuk mengakses halaman atau fitur ini.
        </p>

        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a 
                href="{{ route('dashboard') }}" 
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-900 hover:bg-brand-950 text-white font-semibold text-xs transition-colors shadow-xs"
            >
                &larr; Kembali ke Dashboard
            </a>
            <button 
                onclick="window.history.back()" 
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors"
            >
                Halaman Sebelumnya
            </button>
        </div>

        <p class="text-xs text-slate-400 mt-8">
            Hubungi Admin Sistem jika Anda memerlukan izin tambahan untuk modul ini.
        </p>
    </div>
</body>
</html>
