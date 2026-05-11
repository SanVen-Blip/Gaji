<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\GajiController;

// ===== AUTH ROUTES =====
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===== PROTECTED ROUTES (harus login) =====
Route::middleware('auth')->group(function () {

    // Halaman utama / dashboard
    Route::get('/', function () {
        return view('app');
    })->name('dashboard');

    // API Karyawan
    Route::prefix('api')->group(function () {
        Route::get('/karyawan',         [KaryawanController::class, 'index']);
        Route::post('/karyawan',        [KaryawanController::class, 'store']);
        Route::get('/karyawan/{id}',    [KaryawanController::class, 'show']);
        Route::put('/karyawan/{id}',    [KaryawanController::class, 'update']);
        Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy']);

        // API Gaji
        Route::get('/gaji/dashboard',   [GajiController::class, 'dashboard']);
        Route::get('/gaji',             [GajiController::class, 'index']);
        Route::post('/gaji',            [GajiController::class, 'store']);
        Route::get('/gaji/{id}',        [GajiController::class, 'show']);
        Route::put('/gaji/{id}',        [GajiController::class, 'update']);
        Route::delete('/gaji/{id}',     [GajiController::class, 'destroy']);
    });
});
