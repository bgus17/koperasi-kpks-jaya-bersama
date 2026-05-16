<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KaryawanSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['nik' => 'KRY-2026-001', 'nama' => 'Ahmad Fauzi', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0001', 'alamat' => 'Afdeling I Blok A', 'tanggal_masuk' => '2022-01-10', 'status' => 'aktif', 'jabatan' => 'Mandor Panen', 'divisi' => 'Produksi', 'keterangan' => 'Mandor panen TBS afdeling I.'],
            ['nik' => 'KRY-2026-002', 'nama' => 'Rudi Hartono', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0002', 'alamat' => 'Afdeling II Blok C', 'tanggal_masuk' => '2021-09-15', 'status' => 'aktif', 'jabatan' => 'Mandor Perawatan', 'divisi' => 'Perawatan', 'keterangan' => 'Mandor perawatan kebun dan pemeliharaan piringan.'],
            ['nik' => 'KRY-2026-003', 'nama' => 'Siti Aminah', 'jenis_kelamin' => 'P', 'no_hp' => '0812-4011-0003', 'alamat' => 'Afdeling I Emplasmen', 'tanggal_masuk' => '2023-03-01', 'status' => 'aktif', 'jabatan' => 'Krani Lapangan', 'divisi' => 'Administrasi Lapangan', 'keterangan' => 'Membantu pencatatan hasil kerja lapangan.'],
            ['nik' => 'KRY-2026-004', 'nama' => 'Budi Santoso', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0004', 'alamat' => 'Desa Suka Maju', 'tanggal_masuk' => '2022-04-12', 'status' => 'aktif', 'jabatan' => 'Pemanen', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja panen TBS.'],
            ['nik' => 'KRY-2026-005', 'nama' => 'Joko Prasetyo', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0005', 'alamat' => 'Desa Bukit Raya', 'tanggal_masuk' => '2022-05-18', 'status' => 'aktif', 'jabatan' => 'Pemanen', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja panen TBS.'],
            ['nik' => 'KRY-2026-006', 'nama' => 'Dedi Saputra', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0006', 'alamat' => 'Desa Mulya Jaya', 'tanggal_masuk' => '2022-07-03', 'status' => 'aktif', 'jabatan' => 'Pemanen', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja panen dan muat buah.'],
            ['nik' => 'KRY-2026-007', 'nama' => 'Agus Salim', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0007', 'alamat' => 'Desa Harapan Baru', 'tanggal_masuk' => '2023-01-21', 'status' => 'aktif', 'jabatan' => 'Pemanen', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja panen TBS.'],
            ['nik' => 'KRY-2026-008', 'nama' => 'Hendra Wijaya', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0008', 'alamat' => 'Afdeling II Blok D', 'tanggal_masuk' => '2023-02-11', 'status' => 'aktif', 'jabatan' => 'Pemanen', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja panen TBS.'],
            ['nik' => 'KRY-2026-009', 'nama' => 'Slamet Riyadi', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0009', 'alamat' => 'Desa Sumber Makmur', 'tanggal_masuk' => '2021-11-26', 'status' => 'aktif', 'jabatan' => 'Pekerja Angkutan', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja angkut TBS ke TPH.'],
            ['nik' => 'KRY-2026-010', 'nama' => 'Marwan', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0010', 'alamat' => 'Desa Karya Bakti', 'tanggal_masuk' => '2024-01-08', 'status' => 'aktif', 'jabatan' => 'Pekerja Angkutan', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja angkut dan susun janjang.'],
            ['nik' => 'KRY-2026-011', 'nama' => 'Rina Wulandari', 'jenis_kelamin' => 'P', 'no_hp' => '0812-4011-0011', 'alamat' => 'Desa Suka Maju', 'tanggal_masuk' => '2022-08-19', 'status' => 'aktif', 'jabatan' => 'Kutip Berondol', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja kutip berondolan.'],
            ['nik' => 'KRY-2026-012', 'nama' => 'Nurhayati', 'jenis_kelamin' => 'P', 'no_hp' => '0812-4011-0012', 'alamat' => 'Desa Bukit Raya', 'tanggal_masuk' => '2023-04-04', 'status' => 'aktif', 'jabatan' => 'Kutip Berondol', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja kutip berondolan.'],
            ['nik' => 'KRY-2026-013', 'nama' => 'Wati Sari', 'jenis_kelamin' => 'P', 'no_hp' => '0812-4011-0013', 'alamat' => 'Desa Mulya Jaya', 'tanggal_masuk' => '2023-06-13', 'status' => 'aktif', 'jabatan' => 'Kutip Berondol', 'divisi' => 'Produksi', 'keterangan' => 'Pekerja kutip berondolan dan bersih TPH.'],
            ['nik' => 'KRY-2026-014', 'nama' => 'Eko Prabowo', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0014', 'alamat' => 'Desa Harapan Baru', 'tanggal_masuk' => '2022-10-09', 'status' => 'aktif', 'jabatan' => 'Perawatan', 'divisi' => 'Perawatan', 'keterangan' => 'Pekerja pruning dan sanitasi kebun.'],
            ['nik' => 'KRY-2026-015', 'nama' => 'Suryadi', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0015', 'alamat' => 'Afdeling III Blok F', 'tanggal_masuk' => '2023-08-30', 'status' => 'aktif', 'jabatan' => 'Perawatan', 'divisi' => 'Perawatan', 'keterangan' => 'Pekerja semprot gulma dan pemeliharaan piringan.'],
            ['nik' => 'KRY-2026-016', 'nama' => 'Murniati', 'jenis_kelamin' => 'P', 'no_hp' => '0812-4011-0016', 'alamat' => 'Desa Sumber Makmur', 'tanggal_masuk' => '2024-02-17', 'status' => 'aktif', 'jabatan' => 'Perawatan', 'divisi' => 'Perawatan', 'keterangan' => 'Pekerja pemupukan dan rawat gawangan.'],
            ['nik' => 'KRY-2026-017', 'nama' => 'Teguh Rahman', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0017', 'alamat' => 'Desa Karya Bakti', 'tanggal_masuk' => '2024-03-06', 'status' => 'aktif', 'jabatan' => 'Perawatan', 'divisi' => 'Perawatan', 'keterangan' => 'Pekerja pemeliharaan jalan panen dan pasar pikul.'],
            ['nik' => 'KRY-2026-018', 'nama' => 'Rahmat Hidayat', 'jenis_kelamin' => 'L', 'no_hp' => '0812-4011-0018', 'alamat' => 'Afdeling III Blok G', 'tanggal_masuk' => '2021-06-20', 'status' => 'cuti', 'jabatan' => 'Pemanen', 'divisi' => 'Produksi', 'keterangan' => 'Sedang cuti, tidak tampil sebagai pekerja aktif.'],
            ['nik' => 'KRY-2026-019', 'nama' => 'Sri Lestari', 'jenis_kelamin' => 'P', 'no_hp' => '0812-4011-0019', 'alamat' => 'Desa Suka Maju', 'tanggal_masuk' => '2020-12-02', 'status' => 'nonaktif', 'jabatan' => 'Kutip Berondol', 'divisi' => 'Produksi', 'keterangan' => 'Data arsip karyawan nonaktif.'],
        ];

        $hasNik = Schema::hasColumn('karyawan', 'nik');
        $hasJabatan = Schema::hasColumn('karyawan', 'jabatan');
        $hasDivisi = Schema::hasColumn('karyawan', 'divisi');
        $hasTanggalMasuk = Schema::hasColumn('karyawan', 'tanggal_masuk');
        $hasGajiPokok = Schema::hasColumn('karyawan', 'gaji_pokok');
        $now = now();

        foreach ($records as $record) {
            $payload = [
                'nama' => $record['nama'],
                'jenis_kelamin' => $record['jenis_kelamin'],
                'no_hp' => $record['no_hp'],
                'alamat' => $record['alamat'],
                'status' => $record['status'],
                'keterangan' => $record['keterangan'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasNik) {
                $payload['nik'] = $record['nik'];
            }

            if ($hasJabatan) {
                $payload['jabatan'] = $record['jabatan'];
            }

            if ($hasDivisi) {
                $payload['divisi'] = $record['divisi'];
            }

            if ($hasTanggalMasuk) {
                $payload['tanggal_masuk'] = $record['tanggal_masuk'];
            }

            if ($hasGajiPokok) {
                $payload['gaji_pokok'] = 0;
            }

            $lookup = $hasNik
                ? ['nik' => $record['nik']]
                : ['nama' => $record['nama']];

            DB::table('karyawan')->updateOrInsert($lookup, $payload);
        }

        $this->command->info('KaryawanSeeder selesai: ' . count($records) . ' data karyawan lapangan.');
    }
}
