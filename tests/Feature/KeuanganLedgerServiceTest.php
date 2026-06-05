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

    public function test_summary_uses_realtime_debet_kredit_and_saldo_mutation(): void
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
        $this->assertSame(1_500_000, $summary['pendapatan_saldo']);
        $this->assertSame(900_000, $summary['pengeluaran_jumlah']);
        $this->assertSame(200_000, $summary['pengeluaran_debet']);
        $this->assertSame(400_000, $summary['pengeluaran_kredit']);
        $this->assertSame(-500_000, $summary['pengeluaran_saldo']);
        $this->assertSame(1_700_000, $summary['total_debet']);
        $this->assertSame(400_000, $summary['total_kredit']);
        $this->assertSame(1_000_000, $summary['saldo_akhir']);
    }

    public function test_laporan_builds_grouped_rows_with_running_balance_and_month_filter(): void
    {
        $tahun = 2098;

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
            'debet' => 250_000,
            'kredit' => 0,
            'saldo' => 1_250_000,
            'tahun' => $tahun,
            'tanggal' => "{$tahun}-02-01",
        ]);

        $kategori = KategoriPengeluaran::create([
            'nomor_kategori' => 'Z',
            'nama_kategori' => 'Biaya Laporan',
            'urutan' => 1000,
        ]);

        $sub = SubPengeluaran::create([
            'kategori_id' => $kategori->id,
            'nomor_sub' => 1,
            'nama_sub' => 'Biaya Bulanan',
            'jenis_detail' => 'umum',
        ]);

        Pengeluaran::create([
            'kategori_id' => $kategori->id,
            'sub_id' => $sub->id,
            'tanggal' => "{$tahun}-02-03",
            'jumlah' => 100_000,
            'jenis_transaksi' => 'kredit',
            'debet' => 0,
            'kredit' => 100_000,
            'saldo' => -100_000,
            'keterangan' => null,
            'sudah_bayar' => true,
        ]);

        $laporan = KeuanganLedgerService::laporan($tahun, 2);
        $pendapatanSection = $laporan['sections']->firstWhere('nomor', 'I');
        $testSection = $laporan['sections']->firstWhere('nomor', 'Z');

        $this->assertSame('Februari 2098', $laporan['periode']['label']);
        $this->assertSame(250_000, $laporan['summary']['total_debet']);
        $this->assertSame(100_000, $laporan['summary']['total_kredit']);
        $this->assertSame(150_000, $laporan['summary']['saldo_akhir']);
        $this->assertSame('Pendapatan Diterima', $pendapatanSection['rows']->first()['keterangan']);
        $this->assertSame(250_000, $pendapatanSection['rows']->first()['saldo']);
        $this->assertSame(150_000, $testSection['rows']->first()['saldo']);
    }
}
