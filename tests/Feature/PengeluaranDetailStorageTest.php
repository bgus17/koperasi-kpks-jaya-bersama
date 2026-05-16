<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use App\Models\SubPengeluaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PengeluaranDetailStorageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_remaining_card_profiles_store_details_in_their_own_tables(): void
    {
        foreach ($this->cardProfiles() as $profile => $config) {
            $kategori = KategoriPengeluaran::create([
                'nomor_kategori' => $config['nomor_kategori'],
                'nama_kategori' => $config['nama_kategori'],
                'urutan' => 900,
            ]);

            $sub = SubPengeluaran::create([
                'kategori_id' => $kategori->id,
                'nomor_sub' => 1,
                'nama_sub' => $config['nama_sub'],
                'jenis_detail' => $profile,
            ]);

            $pengeluaran = Pengeluaran::create([
                'kategori_id' => $kategori->id,
                'sub_id' => $sub->id,
                'tanggal' => '2099-05-16',
                'jumlah' => 100_000,
                'jenis_transaksi' => 'kredit',
                'debet' => 0,
                'kredit' => 100_000,
                'saldo' => -100_000,
                'sudah_bayar' => true,
            ]);

            $relation = Pengeluaran::detailRelationForProfile($profile);
            $pengeluaran->{$relation}()->create($config['payload']);

            $this->assertDatabaseHas($config['table'], [
                'pengeluaran_id' => $pengeluaran->id,
            ]);

            $this->assertDatabaseMissing($config['old_table'], [
                'pengeluaran_id' => $pengeluaran->id,
            ]);
        }
    }

    private function cardProfiles(): array
    {
        return [
            'alat_berat' => [
                'nomor_kategori' => 'TST-VI',
                'nama_kategori' => 'Test Alat Berat',
                'nama_sub' => 'Test Traktor',
                'table' => 'pengeluaran_alat_berat',
                'old_table' => 'pengeluaran_pupuk',
                'payload' => [
                    'blok' => 'A1',
                    'volume' => 2,
                    'satuan' => 'HM',
                    'harga_satuan' => 50_000,
                    'supplier_vendor' => 'Vendor Alat',
                    'no_referensi' => 'AB-001',
                ],
            ],
            'perlengkapan' => [
                'nomor_kategori' => 'TST-VII',
                'nama_kategori' => 'Test Perlengkapan',
                'nama_sub' => 'Test APD',
                'table' => 'pengeluaran_perlengkapan',
                'old_table' => 'pengeluaran_pupuk',
                'payload' => [
                    'blok' => 'B1',
                    'volume' => 3,
                    'satuan' => 'unit',
                    'harga_satuan' => 50_000,
                    'supplier_vendor' => 'Vendor Perlengkapan',
                    'no_referensi' => 'PL-001',
                ],
            ],
            'insentive' => [
                'nomor_kategori' => 'TST-VIII',
                'nama_kategori' => 'Test Insentive',
                'nama_sub' => 'Test Mandor',
                'table' => 'pengeluaran_insentive',
                'old_table' => 'pengeluaran_umum',
                'payload' => [
                    'volume' => 1,
                    'satuan' => 'orang',
                    'harga_satuan' => 100_000,
                    'supplier_vendor' => 'Penerima Insentive',
                    'no_referensi' => 'IN-001',
                ],
            ],
        ];
    }
}
