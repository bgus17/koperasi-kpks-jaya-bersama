<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $items = [
        'Kertas,Pena,Tinta dll',
        'Perlengkapan Kantor',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $kategoriId = DB::table('kategori_pengeluaran')
                ->where('nomor_kategori', 'VII')
                ->value('id');

            if (!$kategoriId) {
                return;
            }

            $nextNumber = (int) DB::table('sub_pengeluaran')
                ->where('kategori_id', $kategoriId)
                ->max('nomor_sub');

            foreach ($this->items as $index => $name) {
                DB::table('sub_pengeluaran')->updateOrInsert(
                    [
                        'kategori_id' => $kategoriId,
                        'nama_sub' => $name,
                    ],
                    [
                        'nomor_sub' => $nextNumber + $index + 1,
                        'jenis_detail' => 'perlengkapan',
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
                ->where('nomor_kategori', 'VII')
                ->value('id');

            if (!$kategoriId) {
                return;
            }

            $removableIds = DB::table('sub_pengeluaran as sp')
                ->leftJoin('pengeluaran as p', 'p.sub_id', '=', 'sp.id')
                ->where('sp.kategori_id', $kategoriId)
                ->whereIn('sp.nama_sub', $this->items)
                ->whereNull('p.id')
                ->pluck('sp.id');

            if ($removableIds->isNotEmpty()) {
                DB::table('sub_pengeluaran')->whereIn('id', $removableIds)->delete();
            }
        });
    }
};
