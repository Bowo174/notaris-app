<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\RoleWorkspaceController;
use App\Http\Controllers\PpatOrderController;
use App\Http\Controllers\PpatWorkSheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->middleware('auth')->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/admin', [DashboardController::class, 'admin'])->middleware(['auth', 'role:Admin'])->name('admin.dashboard');
Route::get('/staff', [DashboardController::class, 'staff'])->middleware(['auth', 'role:Staff'])->name('staff.dashboard');

Route::middleware(['auth', 'role:Notaris'])->prefix('notaris')->name('notaris.')->group(function () {
    Route::get('/', [RoleWorkspaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/layanan/{service}', [RoleWorkspaceController::class, 'service'])->name('services.show');
    Route::get('/data-client', [RoleWorkspaceController::class, 'clients'])->name('clients.index');
    Route::get('/data-client/{client}', [RoleWorkspaceController::class, 'client'])->name('clients.show');
    Route::get('/berkas/{clientFile}', [RoleWorkspaceController::class, 'previewFile'])->name('files.preview');
});

Route::middleware(['auth', 'role:PPAT'])->prefix('ppat')->name('ppat.')->group(function () {
    Route::get('/', [RoleWorkspaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/pemesanan', [PpatOrderController::class, 'index'])->name('orders.index');
    Route::get('/lembar-kerja', [PpatWorkSheetController::class, 'index'])->name('work-sheets.index');
    Route::get('/lembar-kerja/{workSheet:code}/qr.jpg', [PpatWorkSheetController::class, 'downloadQr'])->name('work-sheets.qr');
    Route::get('/lembar-kerja/{workSheet:code}', [PpatWorkSheetController::class, 'show'])->name('work-sheets.show');
    Route::patch('/lembar-kerja/{workSheet}', [PpatWorkSheetController::class, 'update'])->name('work-sheets.update');
    Route::get('/lembar-kerja/berkas/{workSheetFile}', [PpatWorkSheetController::class, 'previewFile'])->name('work-sheets.files.preview');
    Route::get('/pemesanan/tambah', [PpatOrderController::class, 'create'])->name('orders.create');
    Route::get('/pemesanan/cari-client', [PpatOrderController::class, 'searchClients'])->name('orders.clients.search');
    Route::post('/pemesanan', [PpatOrderController::class, 'store'])->name('orders.store');
    Route::patch('/pemesanan/{order}/status', [PpatOrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::get('/pemesanan/{order}', [PpatOrderController::class, 'show'])->name('orders.show');
    Route::get('/berkas-pemesanan/{orderFile}', [PpatOrderController::class, 'previewFile'])->name('orders.files.preview');
    Route::get('/layanan/{service}', [RoleWorkspaceController::class, 'service'])->name('services.show');
    Route::get('/data-client', [RoleWorkspaceController::class, 'clients'])->name('clients.index');
    Route::get('/data-client/{client}', [RoleWorkspaceController::class, 'client'])->name('clients.show');
    Route::get('/berkas/{clientFile}', [RoleWorkspaceController::class, 'previewFile'])->name('files.preview');
});

Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/layanan/kode-berikutnya', [ServiceController::class, 'nextCode'])->name('services.next-code');
    Route::post('/layanan', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/layanan/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/layanan/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::get('/data-client', [ClientController::class, 'index'])->name('clients.index');
    Route::post('/data-client', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/data-client/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::put('/data-client/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/data-client/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::get('/data-client/berkas/{clientFile}', [ClientController::class, 'downloadFile'])->name('clients.files.download');
    Route::delete('/data-client/berkas/{clientFile}', [ClientController::class, 'destroyFile'])->name('clients.files.destroy');
});
