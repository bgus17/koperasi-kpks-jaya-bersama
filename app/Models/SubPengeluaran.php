<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubPengeluaran extends Model
{
    protected $table = 'sub_pengeluaran';

    protected $fillable = [
        'kategori_id',
        'nomor_sub',
        'nama_sub',
        'jenis_detail',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriPengeluaran::class, 'kategori_id');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'sub_id');
    }
}
