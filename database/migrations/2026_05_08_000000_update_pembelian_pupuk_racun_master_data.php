<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $items = [
        'Pupuk Urea',
        'Pupuk NPK',
        'Pupuk Borafe',
        'Pupuk Phosphat',
        'Pupuk KCL',
        'Pupuk Dolumit/Kisnite',
        'Pupuk Sulfur',
        'Pupuk Hayati Silitex',
        'Herbisida',
        'Insektisida Capture',
        'Fungisida',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $kategoriId = DB::table('kategori_pengeluaran')
                ->where('nomor_kategori', 'IV')
                ->value('id');

            if (!$kategoriId) {
                return;
            }

            DB::table('kategori_pengeluaran')
                ->where('id', $kategoriId)
                ->update([
                    'nama_kategori' => 'Pembelian Pupuk & Racun',
                    'updated_at' => now(),
                ]);

            $this->renameLegacySubItems($kategoriId);

            foreach ($this->items as $index => $name) {
                DB::table('sub_pengeluaran')->updateOrInsert(
                    [
                        'kategori_id' => $kategoriId,
                        'nama_sub' => $name,
                    ],
                    [
                        'nomor_sub' => $index + 1,
                        'jenis_detail' => 'pupuk',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $unusedLegacyIds = DB::table('sub_pengeluaran as sp')
                ->leftJoin('pengeluaran as p', 'p.sub_id', '=', 'sp.id')
                ->where('sp.kategori_id', $kategoriId)
                ->whereNotIn('sp.nama_sub', $this->items)
                ->whereNull('p.id')
                ->pluck('sp.id');

            if ($unusedLegacyIds->isNotEmpty()) {
                DB::table('sub_pengeluaran')->whereIn('id', $unusedLegacyIds)->delete();
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $kategoriId = DB::table('kategori_pengeluaran')
                ->where('nomor_kategori', 'IV')
                ->value('id');

            if (!$kategoriId) {
                return;
            }

            DB::table('kategori_pengeluaran')
                ->where('id', $kategoriId)
                ->update([
                    'nama_kategori' => 'Pembelian Pupuk',
                    'updated_at' => now(),
                ]);

            $legacyItems = [
                'Pupuk Urea',
                'Pupuk MOP / KCL',
                'Pupuk SP-36',
                'Pupuk Dolomit',
                'Pupuk Organik',
            ];

            foreach ($legacyItems as $index => $name) {
                DB::table('sub_pengeluaran')->updateOrInsert(
                    [
                        'kategori_id' => $kategoriId,
                        'nama_sub' => $name,
                    ],
                    [
                        'nomor_sub' => $index + 1,
                        'jenis_detail' => 'pupuk',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $removableIds = DB::table('sub_pengeluaran as sp')
                ->leftJoin('pengeluaran as p', 'p.sub_id', '=', 'sp.id')
                ->where('sp.kategori_id', $kategoriId)
                ->whereIn('sp.nama_sub', array_diff($this->items, $legacyItems))
                ->whereNull('p.id')
                ->pluck('sp.id');

            if ($removableIds->isNotEmpty()) {
                DB::table('sub_pengeluaran')->whereIn('id', $removableIds)->delete();
            }
        });
    }

    private function renameLegacySubItems(int $kategoriId): void
    {
        $renames = [
            'Pupuk MOP / KCL' => 'Pupuk KCL',
            'Pupuk SP-36' => 'Pupuk Phosphat',
            'Pupuk Dolomit' => 'Pupuk Dolumit/Kisnite',
            'Pupuk Organik' => 'Pupuk Hayati Silitex',
        ];

        foreach ($renames as $from => $to) {
            $source = DB::table('sub_pengeluaran')
                ->where('kategori_id', $kategoriId)
                ->where('nama_sub', $from)
                ->first();

            if (!$source) {
                continue;
            }

            $target = DB::table('sub_pengeluaran')
                ->where('kategori_id', $kategoriId)
                ->where('nama_sub', $to)
                ->first();

            if ($target && $target->id !== $source->id) {
                DB::table('pengeluaran')
                    ->where('sub_id', $source->id)
                    ->update(['sub_id' => $target->id]);

                DB::table('sub_pengeluaran')->where('id', $source->id)->delete();

                continue;
            }

            DB::table('sub_pengeluaran')
                ->where('id', $source->id)
                ->update([
                    'nama_sub' => $to,
                    'updated_at' => now(),
                ]);
        }
    }
};
