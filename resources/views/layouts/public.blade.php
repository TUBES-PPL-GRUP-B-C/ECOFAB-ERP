<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Katalog Bursa & Eco-Passport' }} - {{ config('app.name', 'EcoFab ERP') }}</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 bg-[#F8FAFC]">
    <!-- Header Publik Ringan -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 shadow-2xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <a href="{{ route('public.bursa.index') }}" class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-brand-900 flex items-center justify-center text-white shadow-xs">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-2-9m-7-3a9 9 0 00-9 9c0 4.97 4.03 9 9 9 0-4.97 4.03-9 9-9z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-base font-extrabold tracking-tight text-brand-900">EcoFab</span>
                    <span class="text-[9px] font-bold tracking-widest text-emerald-600 uppercase ml-1.5 px-1.5 py-0.5 bg-emerald-50 rounded-sm">SDG 12</span>
                </div>
            </a>

            <div class="flex items-center space-x-4 text-xs font-medium">
                <a href="{{ route('public.bursa.index') }}" class="text-slate-600 hover:text-brand-900 {{ request()->is('bursa/katalog*') ? 'text-brand-900 font-bold' : '' }}">
                    Bursa Limbah
                </a>
                <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-brand-900 font-semibold hover:bg-emerald-100 transition-colors">
                    Masuk Portal Internal &rarr;
                </a>
            </div>
        </div>
    </header>

    <!-- Konten Halaman Publik -->
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    <!-- Footer Publik -->
    <footer class="bg-white border-t border-slate-200/80 py-6 text-center text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-4">
            <p class="font-medium text-slate-700">EcoFab ERP &bull; Transparansi Jejak Karbon & Sirkularitas Manufaktur</p>
            <p class="mt-1 text-slate-400">Mendukung Target SDG 12.2, 12.5, 12.6 & 12.8 &bull; Cirebon Barat, Jawa Barat</p>
        </div>
    </footer>
</body>
</html>
