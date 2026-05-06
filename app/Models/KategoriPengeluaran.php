<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriPengeluaran extends Model
{
    protected $table = 'kategori_pengeluaran';

    protected $fillable = [
        'nomor_kategori',
        'nama_kategori',
        'urutan',
    ];

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'kategori_id');
    }

    public function subPengeluaran()
    {
        return $this->hasMany(SubPengeluaran::class, 'kategori_id');
    }
}
