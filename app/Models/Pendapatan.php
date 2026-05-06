<?php
// app/Models/Pendapatan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    protected $table = 'pendapatan';

    protected $fillable = [
        'nomor_kategori',
        'kategori',
        'sub_kategori',
        'debet',
        'kredit',
        'saldo',
        'tahun',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'debet'   => 'integer',
        'kredit'  => 'integer',
        'saldo'   => 'integer',
        'tahun'   => 'integer',
        'tanggal' => 'date',
    ];
}