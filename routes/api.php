<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController as ApiAuth;
use App\Http\Controllers\Api\ActorFormController as ApiActorForm;
use App\Http\Controllers\Api\PendapatanController as ApiPendapatan;
use App\Http\Controllers\Api\PengeluaranController as ApiPengeluaran;
use App\Http\Controllers\Api\RekapController as ApiRekap;

Route::post('auth/login', [ApiAuth::class, 'login'])->name('api.auth.login');
 
// Semua endpoint API dilindungi dengan auth:sanctum
// Jika belum pakai Sanctum, ubah ke 'auth' atau hapus middleware-nya
Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [ApiAuth::class, 'me'])->name('api.auth.me');
    Route::post('auth/logout', [ApiAuth::class, 'logout'])->name('api.auth.logout');

    // ── Form Hybrid per Aktor ───────────────────────────────
    Route::get('aktor/forms', [ApiActorForm::class, 'index'])->name('api.aktor.forms.index');
    Route::get('aktor/forms/{slug}', [ApiActorForm::class, 'show'])->name('api.aktor.forms.show');
    Route::post('aktor/forms/{slug}/{subKategori}', [ApiActorForm::class, 'store'])->name('api.aktor.forms.store');
 
    // ── Pendapatan API ───────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('pendapatan/summary', [ApiPendapatan::class, 'summary'])->name('api.pendapatan.summary');
        Route::apiResource('pendapatan', ApiPendapatan::class)->names('api.pendapatan');
 
        // ── Pengeluaran API Admin ────────────────────────────
        Route::get('pengeluaran/summary', [ApiPengeluaran::class, 'summary'])->name('api.pengeluaran.summary');
        Route::apiResource('pengeluaran', ApiPengeluaran::class)->names('api.pengeluaran');
    });
 
    // ── Rekap API ────────────────────────────────────────────
    Route::middleware('role:admin|staff_operator')->group(function () {
        Route::get('rekap/laporan-lengkap', [ApiRekap::class, 'laporanLengkap'])->name('api.rekap.laporan-lengkap');
        Route::get('rekap',                 [ApiRekap::class, 'index'])->name('api.rekap.index');
        Route::get('rekap/{tahun}',         [ApiRekap::class, 'show'])->name('api.rekap.show');
    });
});
 










