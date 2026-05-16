<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranInsentive extends Model
{
    protected $table = 'pengeluaran_insentive';

    protected $fillable = [
        'pengeluaran_id',
        'volume',
        'satuan',
        'harga_satuan',
        'supplier_vendor',
        'no_referensi',
    ];

    protected $casts = [
        'volume'       => 'decimal:2',
        'harga_satuan' => 'integer',
    ];

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'pengeluaran_id');
    }
}
