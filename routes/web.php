<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Dashboard Kepala Toko
Route::middleware(['auth', 'role:kepala_toko'])
    ->prefix('kepala-toko')
    ->name('kepala-toko.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboards.kepala-toko');
        })->name('dashboard');
    });

// Dashboard Admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboards.admin');
        })->name('dashboard');
    });
