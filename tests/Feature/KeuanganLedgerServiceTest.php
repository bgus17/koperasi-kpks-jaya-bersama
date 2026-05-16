<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\SubPengeluaran;
use App\Services\KeuanganLedgerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KeuanganLedgerServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_summary_reduces_debet_and_saldo_by_all_pengeluaran(): void
    {
        $tahun = 2099;

        Pendapatan::create([
            'nomor_kategori' => 'I',
            'kategori' => 'Saldo Per 31 Des',
            'sub_kategori' => 'Saldo Per 31 Des',
            'debet' => 1_000_000,
            'kredit' => 0,
            'saldo' => 1_000_000,
            'tahun' => $tahun,
            'tanggal' => "{$tahun}-01-01",
        ]);

        Pendapatan::create([
            'nomor_kategori' => 'I',
            'kategori' => 'Pendapatan Diterima',
            'sub_kategori' => 'Pendapatan Diterima',
            'debet' => 500_000,
            'kredit' => 0,
            'saldo' => 1_500_000,
            'tahun' => $tahun,
            'tanggal' => "{$tahun}-01-02",
        ]);

        $kategori = KategoriPengeluaran::create([
            'nomor_kategori' => 'TST',
            'nama_kategori' => 'Pengeluaran Test',
            'urutan' => 999,
        ]);

        $sub = SubPengeluaran::create([
            'kategori_id' => $kategori->id,
            'nomor_sub' => 1,
            'nama_sub' => 'Pengeluaran Test',
            'jenis_detail' => 'umum',
        ]);

        foreach ([
            ['jenis_transaksi' => 'kredit', 'jumlah' => 400_000, 'debet' => 0, 'kredit' => 400_000, 'saldo' => -400_000],
            ['jenis_transaksi' => 'debet', 'jumlah' => 200_000, 'debet' => 200_000, 'kredit' => 0, 'saldo' => 200_000],
            ['jenis_transaksi' => 'saldo', 'jumlah' => 300_000, 'debet' => 0, 'kredit' => 0, 'saldo' => -300_000],
        ] as $row) {
            Pengeluaran::create([
                'kategori_id' => $kategori->id,
                'sub_id' => $sub->id,
                'tanggal' => "{$tahun}-02-01",
                'jumlah' => $row['jumlah'],
                'jenis_transaksi' => $row['jenis_transaksi'],
                'debet' => $row['debet'],
                'kredit' => $row['kredit'],
                'saldo' => $row['saldo'],
                'keterangan' => null,
                'sudah_bayar' => true,
            ]);
        }

        $summary = KeuanganLedgerService::summary($tahun);

        $this->assertSame(1_500_000, $summary['pendapatan_debet']);
        $this->assertSame(2_500_000, $summary['pendapatan_saldo']);
        $this->assertSame(900_000, $summary['pengeluaran_jumlah']);
        $this->assertSame(600_000, $summary['total_debet']);
        $this->assertSame(1_600_000, $summary['saldo_akhir']);
    }
}
