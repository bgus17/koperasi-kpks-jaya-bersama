<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengeluaranKategoriSeeder extends Seeder
{
    public function run(): void
    {
        $masterData = [
            ['nomor' => 'II', 'nama' => 'Biaya Produksi', 'urutan' => 1, 'subs' => [
                ['nama' => 'Angkutan', 'jenis_detail' => 'angkutan'],
                ['nama' => 'Panen', 'jenis_detail' => 'panen'],
                ['nama' => 'Kutip Berondol', 'jenis_detail' => 'berondol'],
            ]],
            ['nomor' => 'III', 'nama' => 'Biaya Perawatan', 'urutan' => 2, 'subs' => [
                ['nama' => 'Aplikasi Insektisida Capture', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Aplikasi Racun Tikus', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Aplikasi Herbisida', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Sulam Tanaman Kelapa Sawit', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Pemupukan / Muat / Ecer Pupuk', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Bokor / Piringan', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Pembersihan Lebung', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Tapak Kuda', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Tapak Timbun', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Kastrasi, Sanitasi Dan Pruning', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Dongkel Anak Kayu', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Pembuatan Pasar Pikul', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Pembuatan TPH', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Penanaman Bunga / Perawatan', 'jenis_detail' => 'perawatan'],
                ['nama' => 'Sensus Sawit', 'jenis_detail' => 'perawatan'],
            ]],
            ['nomor' => 'IV', 'nama' => 'Pembelian Pupuk & Racun', 'urutan' => 3, 'subs' => [
                ['nama' => 'Pupuk Urea', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Pupuk NPK', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Pupuk Borafe', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Pupuk Phosphat', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Pupuk KCL', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Pupuk Dolumit/Kisnite', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Pupuk Sulfur', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Pupuk Hayati Silitex', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Herbisida', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Insektisida Capture', 'jenis_detail' => 'pupuk'],
                ['nama' => 'Fungisida', 'jenis_detail' => 'pupuk'],
            ]],
            ['nomor' => 'VI', 'nama' => 'Pemakaian Alat Berat', 'urutan' => 4, 'subs' => [
                ['nama' => 'Beco Leader', 'jenis_detail' => 'alat_berat'],
                ['nama' => 'Roud Greder', 'jenis_detail' => 'alat_berat'],
                ['nama' => 'Compector', 'jenis_detail' => 'alat_berat'],
                ['nama' => 'Traktor', 'jenis_detail' => 'alat_berat'],
            ]],
            ['nomor' => 'VII', 'nama' => 'Perlengkapan', 'urutan' => 5, 'subs' => [
                ['nama' => 'Kep Semprot', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Alat Perangkap Orytes/Paralon', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Alat Pemadam Kebakaran', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'APD Karyawan', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Jalan, Jembatan, Gorong2 Dan Material', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Alat Pertanian', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Sewa Mobil Operasional Kebun', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Kertas,Pena,Tinta dll', 'jenis_detail' => 'perlengkapan'],
                ['nama' => 'Perlengkapan Kantor', 'jenis_detail' => 'perlengkapan'],
            ]],
            ['nomor' => 'VIII', 'nama' => 'Insentive', 'urutan' => 6, 'subs' => [
                ['nama' => 'Pengurus', 'jenis_detail' => 'insentive'],
                ['nama' => 'Pendamping', 'jenis_detail' => 'insentive'],
                ['nama' => 'Badan Pengawas', 'jenis_detail' => 'insentive'],
                ['nama' => 'Pengurus Kelompok', 'jenis_detail' => 'insentive'],
                ['nama' => 'Mandor', 'jenis_detail' => 'insentive'],
                ['nama' => 'Karyawan Kantor', 'jenis_detail' => 'insentive'],
                ['nama' => 'Keamanan', 'jenis_detail' => 'insentive'],
                ['nama' => 'Kepala Desa/BINKAMMAS', 'jenis_detail' => 'insentive'],
            ]],
            ['nomor' => 'V', 'nama' => 'Biaya Umum', 'urutan' => 7, 'subs' => [
                ['nama' => 'Administrasi', 'jenis_detail' => 'umum'],
                ['nama' => 'Operasional Kantor', 'jenis_detail' => 'umum'],
                ['nama' => 'Lain-lain', 'jenis_detail' => 'umum'],
            ]],
        ];

        foreach ($masterData as $item) {
            DB::table('kategori_pengeluaran')->updateOrInsert(
                ['nomor_kategori' => $item['nomor']],
                [
                    'nama_kategori' => $item['nama'],
                    'urutan' => $item['urutan'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $kategoriId = DB::table('kategori_pengeluaran')
                ->where('nomor_kategori', $item['nomor'])
                ->value('id');

            foreach ($item['subs'] as $index => $sub) {
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
    }
}
