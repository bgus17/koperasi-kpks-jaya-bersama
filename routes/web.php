<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\PendapatanController;
use App\Http\Controllers\Admin\PengeluaranController;
use App\Http\Controllers\Admin\RekapController;
use App\Http\Controllers\Admin\KaryawanController;
 
// ── Auth ──────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('pendapatan.index'));
 
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');
 
// ── Admin (semua wajib login) ──────────────────────────────────
Route::group(['middleware' => ['auth']], function () {
 
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
    Route::get('rekap/create',    [RekapController::class, 'create'])->name('rekap.create');
    Route::post('rekap',          [RekapController::class, 'store'])->name('rekap.store');
    Route::get('rekap/{rekap}/edit',   [RekapController::class, 'edit'])->name('rekap.edit');
    Route::put('rekap/{rekap}',        [RekapController::class, 'update'])->name('rekap.update');
    Route::delete('rekap/{rekap}',     [RekapController::class, 'destroy'])->name('rekap.destroy');
    Route::post('rekap/hitung',        [RekapController::class, 'hitung'])->name('rekap.hitung');

    // Karyawan
    Route::resource('karyawan', KaryawanController::class);
    
});

