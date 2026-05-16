<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $categories = [
        [
            'nomor' => 'VI',
            'nama' => 'Pemakaian Alat Berat',
            'urutan' => 4,
            'subs' => [
                ['nama' => 'Beco Leader', 'jenis_detail' => 'alat_berat'],
                ['nama' => 'Roud Greder', 'jenis_detail' => 'alat_berat'],
                ['nama' => 'Compector', 'jenis_detail' => 'alat_berat'],
                ['nama' => 'Traktor', 'jenis_detail' => 'alat_berat'],
            ],
        ],
        [
            'nomor' => 'VII',
            'nama' => 'Perlengkapan',
            'urutan' => 5,
            'subs' => [
                ['nama' => 'Kep Semprot', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Alat Perangkap Orytes/Paralon', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Alat Pemadam Kebakaran', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'APD Karyawan', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Jalan, Jembatan, Gorong2 Dan Material', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Alat Pertanian', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Sewa Mobil Operasional Kebun', 'jenis_detail' => 'perlengkapan'],
            ],
        ],
    ];

    public function up(): void
    {
        DB::transaction(function () {
            foreach ($this->categories as $category) {
                DB::table('kategori_pengeluaran')->updateOrInsert(
                    ['nomor_kategori' => $category['nomor']],
                    [
                        'nama_kategori' => $category['nama'],
                        'urutan' => $category['urutan'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $kategoriId = DB::table('kategori_pengeluaran')
                    ->where('nomor_kategori', $category['nomor'])
                    ->value('id');

                foreach ($category['subs'] as $index => $sub) {
                    DB::table('sub_pengeluaran')->updateOrInsert(
                        [
                            'kategori_id' => $kategoriId,
                            'nama_sub' => $sub['nama'],
                        ],
                        [
                            'nomor_sub' => $index + 1,
                            'jenis_detail' => $sub['jenis_detail'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            foreach ($this->categories as $category) {
                $kategoriId = DB::table('kategori_pengeluaran')
                    ->where('nomor_kategori', $category['nomor'])
                    ->value('id');

                if (!$kategoriId) {
                    continue;
                }

                $used = DB::table('pengeluaran')
                    ->where('kategori_id', $kategoriId)
                    ->exists();

                if ($used) {
                    continue;
                }

                DB::table('sub_pengeluaran')->where('kategori_id', $kategoriId)->delete();
                DB::table('kategori_pengeluaran')->where('id', $kategoriId)->delete();
            }
        });
    }
};
