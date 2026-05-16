<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $subs = [
        'Pengurus',
        'Pendamping',
        'Badan Pengawas',
        'Pengurus Kelompok',
        'Mandor',
        'Karyawan Kantor',
        'Keamanan',
        'Kepala Desa/BINKAMMAS',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            DB::table('kategori_pengeluaran')->updateOrInsert(
                ['nomor_kategori' => 'VIII'],
                [
                    'nama_kategori' => 'Insentive',
                    'urutan' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $kategoriId = DB::table('kategori_pengeluaran')
                ->where('nomor_kategori', 'VIII')
                ->value('id');

            foreach ($this->subs as $index => $name) {
                DB::table('sub_pengeluaran')->updateOrInsert(
                    [
                        'kategori_id' => $kategoriId,
                        'nama_sub' => $name,
                    ],
                    [
                        'nomor_sub' => $index + 1,
                        'jenis_detail' => 'insentive',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $kategoriId = DB::table('kategori_pengeluaran')
                ->where('nomor_kategori', 'VIII')
                ->value('id');

            if (!$kategoriId) {
                return;
            }

            $used = DB::table('pengeluaran')
                ->where('kategori_id', $kategoriId)
                ->exists();

            if ($used) {
                return;
            }

            DB::table('sub_pengeluaran')->where('kategori_id', $kategoriId)->delete();
            DB::table('kategori_pengeluaran')->where('id', $kategoriId)->delete();
        });
    }
};
