<?php
// ============================================================
// FILE: app/Models/Rekap.php
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekap extends Model
{
    protected $table = 'rekap';

    protected $fillable = [
        'tahun',
        'tanggal_tutup',
        'grand_total_debet',
        'grand_total_kredit',
        'saldo_akhir',
        'ketua_pengurus',
        'sekretaris',
        'bendahara',
        'ketua_badan_pengawas',
        'lokasi',
    ];

    protected $casts = [
        'tahun'              => 'integer',
        'tanggal_tutup'      => 'date',
        'grand_total_debet'  => 'integer',
        'grand_total_kredit' => 'integer',
        'saldo_akhir'        => 'integer',
    ];
}