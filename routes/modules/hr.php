<?php

use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\EmployeeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul HR & Organisasi (Prefix: /hr)
|--------------------------------------------------------------------------
| CRUD Departemen, Karyawan, dan Absensi
*/

Route::middleware(['auth', 'permission:hr.view'])->group(function () {
    Route::get('/', function () {
        return view('hr.index');
    })->name('hr.index');

    Route::get('/departemen', [DepartmentController::class, 'index'])->name('hr.departemen.index');
    Route::get('/departemen/create', [DepartmentController::class, 'create'])
        ->middleware('permission:hr.create')->name('hr.departemen.create');
    Route::post('/departemen', [DepartmentController::class, 'store'])
        ->middleware('permission:hr.create')->name('hr.departemen.store');
    Route::get('/departemen/{departemen}', [DepartmentController::class, 'show'])->name('hr.departemen.show');
    Route::get('/departemen/{departemen}/edit', [DepartmentController::class, 'edit'])
        ->middleware('permission:hr.update')->name('hr.departemen.edit');
    Route::put('/departemen/{departemen}', [DepartmentController::class, 'update'])
        ->middleware('permission:hr.update')->name('hr.departemen.update');
    Route::delete('/departemen/{departemen}', [DepartmentController::class, 'destroy'])
        ->middleware('permission:hr.delete')->name('hr.departemen.destroy');

    Route::get('/karyawan', [EmployeeController::class, 'index'])->name('hr.karyawan.index');
    Route::get('/karyawan/create', [EmployeeController::class, 'create'])
        ->middleware('permission:hr.create')->name('hr.karyawan.create');
    Route::post('/karyawan', [EmployeeController::class, 'store'])
        ->middleware('permission:hr.create')->name('hr.karyawan.store');
    Route::get('/karyawan/{karyawan}', [EmployeeController::class, 'show'])->name('hr.karyawan.show');
    Route::get('/karyawan/{karyawan}/edit', [EmployeeController::class, 'edit'])
        ->middleware('permission:hr.update')->name('hr.karyawan.edit');
    Route::put('/karyawan/{karyawan}', [EmployeeController::class, 'update'])
        ->middleware('permission:hr.update')->name('hr.karyawan.update');
    Route::delete('/karyawan/{karyawan}', [EmployeeController::class, 'destroy'])
        ->middleware('permission:hr.delete')->name('hr.karyawan.destroy');

    Route::get('/absensi', [AttendanceController::class, 'index'])->name('hr.absensi.index');
    Route::get('/absensi/create', [AttendanceController::class, 'create'])
        ->middleware('permission:hr.create')->name('hr.absensi.create');
    Route::post('/absensi', [AttendanceController::class, 'store'])
        ->middleware('permission:hr.create')->name('hr.absensi.store');
    Route::get('/absensi/{absensi}', [AttendanceController::class, 'show'])->name('hr.absensi.show');
    Route::get('/absensi/{absensi}/edit', [AttendanceController::class, 'edit'])
        ->middleware('permission:hr.update')->name('hr.absensi.edit');
    Route::put('/absensi/{absensi}', [AttendanceController::class, 'update'])
        ->middleware('permission:hr.update')->name('hr.absensi.update');
    Route::delete('/absensi/{absensi}', [AttendanceController::class, 'destroy'])
        ->middleware('permission:hr.delete')->name('hr.absensi.destroy');
});
