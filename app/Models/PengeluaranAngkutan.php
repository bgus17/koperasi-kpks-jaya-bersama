<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranAngkutan extends Model
{
    protected $table = 'pengeluaran_angkutan';

    protected $fillable = [
        'pengeluaran_id',
        'blok',
        'tonase_kg',
        'volume',
        'satuan',
        'harga_satuan',
        'supplier_vendor',
        'no_referensi',
    ];

    protected $casts = [
        'tonase_kg'    => 'decimal:2',
        'volume'       => 'decimal:2',
        'harga_satuan' => 'integer',
    ];

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'pengeluaran_id');
    }
}
