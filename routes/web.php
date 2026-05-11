<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\GajiController;

// Halaman utama
Route::get('/', function () {
    return view('app');
});

// API Karyawan
Route::prefix('api')->group(function () {
    Route::get('/karyawan',          [KaryawanController::class, 'index']);
    Route::post('/karyawan',         [KaryawanController::class, 'store']);
    Route::get('/karyawan/{id}',     [KaryawanController::class, 'show']);
    Route::put('/karyawan/{id}',     [KaryawanController::class, 'update']);
    Route::delete('/karyawan/{id}',  [KaryawanController::class, 'destroy']);

    // API Gaji
    Route::get('/gaji/dashboard',    [GajiController::class, 'dashboard']);
    Route::get('/gaji',              [GajiController::class, 'index']);
    Route::post('/gaji',             [GajiController::class, 'store']);
    Route::get('/gaji/{id}',         [GajiController::class, 'show']);
    Route::put('/gaji/{id}',         [GajiController::class, 'update']);
    Route::delete('/gaji/{id}',      [GajiController::class, 'destroy']);
});
