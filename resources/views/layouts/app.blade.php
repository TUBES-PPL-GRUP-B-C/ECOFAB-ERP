<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Konsol Operasional' }} - {{ config('app.name', 'EcoFab ERP') }}</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans text-slate-800 bg-[#F8FAFC]">
    <div x-data="{ sidebarOpen: false }" class="min-h-full flex">
        <!-- Sidebar Mobile Backdrop -->
        <div 
            id="mobile-backdrop"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden hidden"
            onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); document.getElementById('mobile-backdrop').classList.add('hidden');"
        ></div>

        <!-- Sidebar Kiri (Lebar Tetap ±260px) -->
        <aside 
            id="sidebar" 
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col transition-transform duration-300 transform -translate-x-full lg:translate-x-0"
        >
            <!-- Logo EcoFab -->
            <div class="h-16 flex items-center px-6 border-b border-slate-100 bg-white">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-brand-900 flex items-center justify-center text-white shadow-xs group-hover:bg-brand-950 transition-colors">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-2-9m-7-3a9 9 0 00-9 9c0 4.97 4.03 9 9 9 0-4.97 4.03-9 9-9z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-extrabold tracking-tight text-brand-900">EcoFab</span>
                        <span class="block text-[10px] font-bold tracking-widest text-emerald-600 uppercase">SIRKULAR ERP</span>
                    </div>
                </a>
            </div>

            <!-- Navigasi Menu Dikelompokkan -->
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto scrollbar-thin">
                @foreach(config('menu.groups', []) as $group)
                    @php
                        // Filter item menu berdasarkan permission
                        $visibleItems = array_filter($group['items'], function($item) {
                            return !isset($item['permission']) || auth()->user()->can($item['permission']);
                        });
                    @endphp

                    @if(count($visibleItems) > 0)
                        <div>
                            <h3 class="px-3 text-[11px] font-bold text-slate-400 tracking-wider uppercase mb-1.5">
                                {{ $group['title'] }}
                            </h3>
                            <ul class="space-y-0.5">
                                @foreach($visibleItems as $item)
                                    @php
                                        $isActive = request()->is($item['active']);
                                        $routeUrl = Route::has($item['route']) ? route($item['route']) : '#';
                                    @endphp
                                    <li>
                                        <a 
                                            href="{{ $routeUrl }}" 
                                            class="flex items-center px-3 py-2 text-xs font-medium rounded-lg transition-colors group {{ $isActive ? 'bg-emerald-50 text-brand-900 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                                        >
                                            <span class="mr-3 {{ $isActive ? 'text-brand-800' : 'text-slate-400 group-hover:text-slate-600' }}">
                                                <x-icon :name="$item['icon']" class="w-4 h-4" />
                                            </span>
                                            <span class="truncate">{{ $item['label'] }}</span>
                                            @if($isActive)
                                                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-800"></span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </nav>

            <!-- Status Pengguna di Bawah Sidebar -->
            <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                <div class="flex items-center space-x-3 px-2 py-1.5">
                    <div class="w-8 h-8 rounded-full bg-brand-800 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="truncate flex-1">
                        <p class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()->role_label }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar" class="text-slate-400 hover:text-rose-600 p-1 rounded-md transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper (Kanan Sidebar) -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
            <!-- Topbar Lebar Penuh -->
            <header class="sticky top-0 z-30 h-16 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Kiri: Tombol Hamburger & Breadcrumb -->
                <div class="flex items-center space-x-3">
                    <button 
                        type="button" 
                        class="lg:hidden text-slate-500 hover:text-slate-700 p-1.5 rounded-lg border border-slate-200"
                        onclick="document.getElementById('sidebar').classList.remove('-translate-x-full'); document.getElementById('mobile-backdrop').classList.remove('hidden');"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div class="flex items-center space-x-2.5">
                        <nav class="hidden sm:flex items-center text-xs text-slate-500 font-medium">
                            <a href="{{ route('dashboard') }}" class="hover:text-slate-800">EcoFab ERP</a>
                            <span class="mx-2 text-slate-300">/</span>
                            <span class="text-slate-800 font-semibold">{{ $breadcrumb ?? 'Konsol Operasional' }}</span>
                        </nav>
                        <span class="hidden md:inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <svg class="w-3 h-3 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            SDG 12: Konsumsi & Produksi Bertanggung Jawab
                        </span>
                    </div>
                </div>

                <!-- Kanan: Tautan Publik, Lonceng, Profil & Simulasi -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Tautan Publik -->
                    <div class="hidden xl:flex items-center space-x-3 text-xs">
                        <a href="{{ route('public.bursa.index') }}" target="_blank" class="text-slate-600 hover:text-brand-900 font-medium flex items-center transition-colors">
                            <span>Katalog Bursa Limbah</span>
                            <svg class="w-3.5 h-3.5 ml-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                        <span class="text-slate-300">|</span>
                        <a href="{{ route('public.passport.show', 'sample') }}" target="_blank" class="text-slate-600 hover:text-brand-900 font-medium flex items-center transition-colors">
                            <span>Eco-Passport Publik</span>
                            <svg class="w-3.5 h-3.5 ml-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </a>
                    </div>

                    <!-- Lonceng Notifikasi -->
                    <div class="relative">
                        <button type="button" class="p-1.5 text-slate-500 hover:text-slate-800 rounded-lg transition-colors relative" title="Notifikasi">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                        </button>
                    </div>

                    <!-- Profil User -->
                    <div class="flex items-center space-x-2 pl-2 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-brand-900 text-white flex items-center justify-center font-bold text-xs ring-2 ring-emerald-500/20">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-xs font-bold text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-emerald-700 font-medium">{{ auth()->user()->kelompok_peran }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Area Konten Utama -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <x-flash />
                @yield('content')
            </main>

            <!-- Footer Konsisten -->
            <footer class="py-4 px-6 border-t border-slate-200/80 bg-white text-center text-xs text-slate-500">
                <p>EcoFab ERP &copy; {{ date('Y') }} &bull; Manufaktur Sirkular Berkelanjutan (SDG 12) &bull; Unit II Cirebon Barat, Jawa Barat.</p>
            </footer>
        </div>
    </div>
</body>
</html>
