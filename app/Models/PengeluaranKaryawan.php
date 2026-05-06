<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranKaryawan extends Model
{
    protected $table = 'pengeluaran_karyawan';

    protected $fillable = [
        'pengeluaran_id',
        'karyawan_id',
        'nama_karyawan_snapshot',
        'tonase_kg',
        'jumlah_janjang',
        'brondolan_kg',
        'luas_ha',
        'hk',
        'volume',
        'satuan',
        'tarif_satuan',
        'upah',
        'keterangan',
    ];

    protected $casts = [
        'tonase_kg'      => 'decimal:2',
        'brondolan_kg'   => 'decimal:2',
        'luas_ha'        => 'decimal:2',
        'hk'             => 'decimal:2',
        'volume'         => 'decimal:2',
        'tarif_satuan'   => 'integer',
        'upah'           => 'integer',
        'jumlah_janjang' => 'integer',
    ];

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'pengeluaran_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
