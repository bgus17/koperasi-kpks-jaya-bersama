<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PendapatanController as ApiPendapatan;
use App\Http\Controllers\Api\PengeluaranController as ApiPengeluaran;
use App\Http\Controllers\Api\RekapController as ApiRekap;
 
// Semua endpoint API dilindungi dengan auth:sanctum
// Jika belum pakai Sanctum, ubah ke 'auth' atau hapus middleware-nya
Route::middleware('auth:sanctum')->group(function () {
 
    // ── Pendapatan API ───────────────────────────────────────
    Route::get('pendapatan/summary', [ApiPendapatan::class, 'summary'])->name('api.pendapatan.summary');
    Route::apiResource('pendapatan', ApiPendapatan::class);
 
    // ── Pengeluaran API ──────────────────────────────────────
    Route::get('pengeluaran/summary', [ApiPengeluaran::class, 'summary'])->name('api.pengeluaran.summary');
    Route::apiResource('pengeluaran', ApiPengeluaran::class);
 
    // ── Rekap API ────────────────────────────────────────────
    Route::get('rekap/laporan-lengkap', [ApiRekap::class, 'laporanLengkap'])->name('api.rekap.laporan-lengkap');
    Route::get('rekap',                 [ApiRekap::class, 'index'])->name('api.rekap.index');
    Route::get('rekap/{tahun}',         [ApiRekap::class, 'show'])->name('api.rekap.show');
});
 












