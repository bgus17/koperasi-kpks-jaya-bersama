<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranKutipBerondol extends Model
{
    protected $table = 'pengeluaran_kutip_berondol';

    protected $fillable = [
        'pengeluaran_id',
        'mandor_id',
        'mandor',
        'blok',
        'jumlah_pekerja',
        'brondolan_kg',
        'volume',
        'satuan',
        'harga_satuan',
    ];

    protected $casts = [
        'mandor_id'      => 'integer',
        'jumlah_pekerja' => 'integer',
        'brondolan_kg'   => 'decimal:2',
        'volume'         => 'decimal:2',
        'harga_satuan'   => 'integer',
    ];

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'pengeluaran_id');
    }

    public function mandorKaryawan()
    {
        return $this->belongsTo(Karyawan::class, 'mandor_id');
    }
}
