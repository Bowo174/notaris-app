<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->middleware('auth')->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/admin', [DashboardController::class, 'admin'])->middleware(['auth', 'role:Admin'])->name('admin.dashboard');
Route::get('/staff', [DashboardController::class, 'staff'])->middleware(['auth', 'role:Staff'])->name('staff.dashboard');

Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/layanan', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/layanan/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/layanan/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
});
