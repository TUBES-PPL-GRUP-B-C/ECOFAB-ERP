<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - EcoFab ERP
|--------------------------------------------------------------------------
| Fondasi rute aplikasi EcoFab ERP.
| Seluruh rute modul bisnis dimuat secara otomatis dari routes/modules/*.php
*/

// Redirect root ke dashboard jika sudah login, atau ke login jika belum
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Autentikasi Pengguna (Login & Logout)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Halaman Dashboard Utama (Dilindungi auth)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Manajemen Pengguna & Peran (Khusus Admin Sistem)
Route::middleware(['auth', 'permission:users.manage'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// Fitur Simulasi Peran (Hanya untuk demo / environment lokal dan testing)
if (app()->environment('local', 'testing')) {
    Route::get('/simulasi-peran/{role}', function (string $role) {
        $user = User::role($role)->first();
        if ($user) {
            Auth::login($user);
            return redirect()->back()->with('success', "Beralih peran ke: {$user->name} ({$user->kelompok_peran})");
        }
        return redirect()->back()->with('error', "Akun dengan peran {$role} tidak ditemukan.");
    })->middleware('auth')->name('simulasi.peran');
}

// --------------------------------------------------------------------------
// Pemuatan Otomatis Modul Bisnis dari routes/modules/*.php
// --------------------------------------------------------------------------
$modulesPath = __DIR__ . '/modules';
if (is_dir($modulesPath)) {
    foreach (glob($modulesPath . '/*.php') as $routeFile) {
        $moduleName = basename($routeFile, '.php'); // hr, pembelian, gudang, produksi, dsb.
        Route::prefix($moduleName)->group($routeFile);
    }
}

// Pemuatan Rute Publik (Bursa Limbah & Eco-Passport tanpa auth)
require __DIR__ . '/public.php';
