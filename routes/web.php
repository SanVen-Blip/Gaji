<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\GajiController;
use App\Http\Controllers\AbsensiController;

// ===== AUTH =====
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ===== PROTECTED =====
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => view('app'))->name('dashboard');

    Route::prefix('api')->group(function () {

        // Karyawan
        Route::get('/karyawan',         [KaryawanController::class, 'index']);
        Route::post('/karyawan',        [KaryawanController::class, 'store']);
        Route::get('/karyawan/{id}',    [KaryawanController::class, 'show']);
        Route::put('/karyawan/{id}',    [KaryawanController::class, 'update']);
        Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy']);

        // Gaji
        Route::get('/gaji/dashboard',   [GajiController::class, 'dashboard']);
        Route::get('/gaji',             [GajiController::class, 'index']);
        Route::post('/gaji',            [GajiController::class, 'store']);
        Route::get('/gaji/{id}',        [GajiController::class, 'show']);
        Route::put('/gaji/{id}',        [GajiController::class, 'update']);
        Route::delete('/gaji/{id}',     [GajiController::class, 'destroy']);

        // Absensi
        Route::get('/absensi/rekap',    [AbsensiController::class, 'rekap']);
        Route::get('/absensi',          [AbsensiController::class, 'index']);
        Route::post('/absensi',         [AbsensiController::class, 'store']);
        Route::get('/absensi/{id}',     [AbsensiController::class, 'show']);
        Route::put('/absensi/{id}',     [AbsensiController::class, 'update']);
        Route::delete('/absensi/{id}',  [AbsensiController::class, 'destroy']);
    });
});
