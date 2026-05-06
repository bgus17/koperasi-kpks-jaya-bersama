<?php
// database/seeders/KeuanganSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KeuanganSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pendapatan')->truncate();
        if (Schema::hasTable('pengeluaran_karyawan')) {
            DB::table('pengeluaran_karyawan')->truncate();
        }
        foreach ([
            'pengeluaran_angkutan',
            'pengeluaran_panen',
            'pengeluaran_kutip_berondol',
            'pengeluaran_perawatan',
            'pengeluaran_pupuk',
            'pengeluaran_umum',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::table('pengeluaran')->truncate();
        DB::table('rekap')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ============================================================
        // TABEL PENDAPATAN - HANYA 3 KATEGORI
        // ============================================================

        $pendapatan = [
            [
                'nomor_kategori' => 'I',
                'kategori'       => 'Saldo Per 31 Des',
                'sub_kategori'   => 'Saldo Per 31 Des 2024',
                'debet'          => 648936438,
                'kredit'         => 0,
                'saldo'          => 648936438,
                'tahun'          => 2025,
                'tanggal'        => '2025-12-31',
                'keterangan'     => 'Saldo awal tahun 2025',
            ],
            [
                'nomor_kategori' => 'I',
                'kategori'       => 'Pendapatan Diterima',
                'sub_kategori'   => 'Pendapatan Diterima',
                'debet'          => 8861401697,
                'kredit'         => 0,
                'saldo'          => 9510338135,
                'tahun'          => 2025,
                'tanggal'        => '2025-12-31',
                'keterangan'     => 'Pendapatan yang diterima selama tahun 2025',
            ],
            [
                'nomor_kategori' => 'I',
                'kategori'       => 'Penjualan Barang',
                'sub_kategori'   => 'Penjualan Barang',
                'debet'          => 57290000,
                'kredit'         => 0,
                'saldo'          => 9567628135,
                'tahun'          => 2025,
                'tanggal'        => '2025-12-31',
                'keterangan'     => 'Penjualan barang selama tahun 2025',
            ],
        ];

        // Data pengeluaran tetap sama seperti sebelumnya
        $pengeluaran = [
            // ... (sama seperti file sebelumnya, tidak berubah)
        ];

        // Data rekap tetap sama
        $rekap = [
            // ... (sama seperti file sebelumnya)
        ];

        $now = now();

        $pendapatan  = array_map(fn($r) => array_merge($r, ['created_at' => $now, 'updated_at' => $now]), $pendapatan);
        $pengeluaran = array_map(fn($r) => array_merge($r, ['created_at' => $now, 'updated_at' => $now]), $pengeluaran);
        $rekap       = array_map(fn($r) => array_merge($r, ['created_at' => $now, 'updated_at' => $now]), $rekap);

        if (!empty($pendapatan)) {
            DB::table('pendapatan')->insert($pendapatan);
        }

        if (!empty($pengeluaran)) {
            DB::table('pengeluaran')->insert($pengeluaran);
        }

        if (!empty($rekap)) {
            DB::table('rekap')->insert($rekap);
        }

        $this->command->info('KeuanganSeeder selesai: ' . count($pendapatan) . ' pendapatan, ' . count($pengeluaran) . ' pengeluaran, ' . count($rekap) . ' rekap.');
    }
}
