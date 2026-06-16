<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PendapatanController;
use App\Http\Controllers\Admin\PengeluaranController;
use App\Http\Controllers\Admin\RekapController;
use App\Http\Controllers\Admin\KaryawanController;
use Illuminate\Support\Facades\Route;

// ── Gateway (Landing Page) ────────────────────────────────────
Route::get('/', [AuthController::class, 'gateway'])->name('gateway');

// ── Admin Auth (custom — override Breeze login) ──────────────
Route::get('/admin/login', [AuthController::class, 'showLogin'])
    ->middleware('guest')
    ->name('admin.login');

Route::post('/admin/login', [AuthController::class, 'login'])
    ->name('admin.login.post');

Route::post('/admin/logout', [AuthController::class, 'logout'])
    ->name('admin.logout')
    ->middleware('auth');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('dashboard');

// ── Profile (Breeze) ─────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin Panel (semua wajib login + role:admin) ──────────────
Route::group(['middleware' => ['auth', 'role:admin']], function () {

    // Pendapatan
    Route::resource('pendapatan', PendapatanController::class);

    // Pengeluaran (CRUD)
    Route::resource('pengeluaran', PengeluaranController::class);

    // Karyawan (CRUD)
    Route::resource('karyawan', KaryawanController::class);

    // Pengeluaran (Custom routes untuk kategori & sub-item)
    Route::get('pengeluaran/kategori/{slug}', [PengeluaranController::class, 'kategori'])->name('pengeluaran.kategori');
    Route::get('pengeluaran/kategori/{slug}/{subKategori}', [PengeluaranController::class, 'subIndex'])->name('pengeluaran.sub.index');
    Route::get('pengeluaran/kategori/{slug}/{subKategori}/create', [PengeluaranController::class, 'subCreate'])->name('pengeluaran.sub.create');
    Route::post('pengeluaran/kategori/{slug}/{subKategori}', [PengeluaranController::class, 'subStore'])->name('pengeluaran.sub.store');
    Route::get('pengeluaran/kategori/{slug}/{subKategori}/{pengeluaran}/edit', [PengeluaranController::class, 'subEdit'])->name('pengeluaran.sub.edit');
    Route::put('pengeluaran/kategori/{slug}/{subKategori}/{pengeluaran}', [PengeluaranController::class, 'subUpdate'])->name('pengeluaran.sub.update');
    Route::delete('pengeluaran/kategori/{slug}/{subKategori}/{pengeluaran}', [PengeluaranController::class, 'subDestroy'])->name('pengeluaran.sub.destroy');

    // Rekap
    Route::get('rekap',           [RekapController::class, 'index'])->name('rekap.index');
    Route::get('rekap/export/pdf',   [RekapController::class, 'exportPdf'])->name('rekap.export.pdf');
    Route::get('rekap/export/excel', [RekapController::class, 'exportExcel'])->name('rekap.export.excel');
    Route::get('rekap/create',    [RekapController::class, 'create'])->name('rekap.create');
    Route::post('rekap',          [RekapController::class, 'store'])->name('rekap.store');
    Route::get('rekap/{rekap}/edit',   [RekapController::class, 'edit'])->name('rekap.edit');
    Route::put('rekap/{rekap}',        [RekapController::class, 'update'])->name('rekap.update');
    Route::delete('rekap/{rekap}',     [RekapController::class, 'destroy'])->name('rekap.destroy');
    Route::post('rekap/hitung',        [RekapController::class, 'hitung'])->name('rekap.hitung');
});

// ── Breeze Auth Routes (login, forgot password, profile auth) ──
require __DIR__.'/auth.php';
