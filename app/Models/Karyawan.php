<?php

// FILE: app/Models/Karyawan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = [
        'nama',
        'jenis_kelamin',
        'no_hp',
        'alamat',
        'status',
        'gaji_pokok',   // ← ditambahkan
        'keterangan',
        // kolom dihapus via migration: nik, jabatan, divisi, tanggal_masuk
    ];

    protected $casts = [
        'gaji_pokok' => 'integer',  // ← cast angka, bukan date lagi
    ];

    // ── SCOPES ────────────────────────────────────────────────────────────────

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeNonaktif(Builder $query): Builder
    {
        return $query->where('status', 'nonaktif');
    }

    public function scopeCuti(Builder $query): Builder
    {
        return $query->where('status', 'cuti');
    }

    // ── RELASI ────────────────────────────────────────────────────────────────

    public function pengeluaranSebagaiMandor()
    {
        return $this->hasMany(Pengeluaran::class, 'mandor_id');
    }

    public function detailPengeluaran()
    {
        return $this->hasMany(PengeluaranKaryawan::class, 'karyawan_id');
    }

    // ── ACCESSORS ─────────────────────────────────────────────────────────────

    /**
     * Label jenis kelamin untuk ditampilkan di view.
     */
    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    /**
     * Label status untuk ditampilkan di view.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif'    => 'Aktif',
            'nonaktif' => 'Non-aktif',
            'cuti'     => 'Cuti',
            default    => ucfirst((string) $this->status),
        };
    }

    /**
     * CSS class suffix untuk badge status di view.
     * Dipakai sebagai: "badge-status badge-status--{{ $karyawan->status_class }}"
     * Warna didefinisikan di app.css, bukan di sini.
     */
    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'aktif'    => 'aktif',
            'nonaktif' => 'nonaktif',
            'cuti'     => 'cuti',
            default    => 'nonaktif',
        };
    }

    /**
     * Gaji pokok dalam format Rupiah untuk ditampilkan di view.
     * Contoh: "Rp 2.500.000"
     */
    public function getGajiPokokFormatAttribute(): string
    {
        if (!$this->gaji_pokok) {
            return '—';
        }

        return 'Rp ' . number_format($this->gaji_pokok, 0, ',', '.');
    }

    // ── STATIC HELPERS ────────────────────────────────────────────────────────

    /**
     * Dipakai oleh controller untuk mengisi dropdown filter & stat card.
     */
    public static function getStatCount(): array
    {
        return [
            'semua'    => static::count(),
            'aktif'    => static::where('status', 'aktif')->count(),
            'nonaktif' => static::where('status', 'nonaktif')->count(),
            'cuti'     => static::where('status', 'cuti')->count(),
        ];
    }
}