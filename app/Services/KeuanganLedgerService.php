<?php

namespace App\Services;

use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KeuanganLedgerService
{
    public static function summary(?int $tahun = null): array
    {
        $pendapatanQuery = Pendapatan::query();
        $pengeluaranQuery = Pengeluaran::query();

        if ($tahun) {
            $pendapatanQuery->where('tahun', $tahun);
            $pengeluaranQuery->whereYear('tanggal', $tahun);
        }

        $pendapatanDebet = (int) (clone $pendapatanQuery)->sum('debet');
        $pendapatanKredit = (int) (clone $pendapatanQuery)->sum('kredit');
        $saldoPendapatan = (int) (clone $pendapatanQuery)->sum('saldo');
        $jumlahPendapatan = (int) (clone $pendapatanQuery)->count();

        $pengeluaranJumlah = (int) (clone $pengeluaranQuery)->sum('jumlah');
        $jumlahPengeluaran = (int) (clone $pengeluaranQuery)->count();

        if (self::hasPengeluaranLedgerColumns()) {
            $pengeluaranDebet = (int) (clone $pengeluaranQuery)->sum('debet');
            $pengeluaranKredit = (int) (clone $pengeluaranQuery)->sum('kredit');
            $mutasiPengeluaran = (int) (clone $pengeluaranQuery)->sum('saldo');
        } else {
            $pengeluaranDebet = 0;
            $pengeluaranKredit = $pengeluaranJumlah;
            $mutasiPengeluaran = -$pengeluaranJumlah;
        }

        $totalDebet = $pendapatanDebet - $pengeluaranJumlah;
        $totalKredit = $pendapatanKredit + $pengeluaranKredit;
        $saldoAkhir = $saldoPendapatan - $pengeluaranJumlah;

        return [
            'tahun' => $tahun,
            'pendapatan_debet' => $pendapatanDebet,
            'pendapatan_kredit' => $pendapatanKredit,
            'pendapatan_saldo' => $saldoPendapatan,
            'pengeluaran_jumlah' => $pengeluaranJumlah,
            'pengeluaran_debet' => $pengeluaranDebet,
            'pengeluaran_kredit' => $pengeluaranKredit,
            'pengeluaran_saldo' => $mutasiPengeluaran,
            'pengeluaran_pengurang' => $pengeluaranJumlah,
            'total_debet' => $totalDebet,
            'total_kredit' => $totalKredit,
            'saldo_akhir' => $saldoAkhir,
            'jumlah_pendapatan' => $jumlahPendapatan,
            'jumlah_pengeluaran' => $jumlahPengeluaran,
            'jumlah_record' => $jumlahPendapatan + $jumlahPengeluaran,
        ];
    }

    public static function pengeluaranPerKategori(int $tahun): Collection
    {
        $query = DB::table('pengeluaran')
            ->whereYear('tanggal', $tahun)
            ->join('kategori_pengeluaran as kp', 'pengeluaran.kategori_id', '=', 'kp.id');

        $select = [
            'kp.nomor_kategori',
            'kp.nama_kategori as kategori',
            DB::raw('SUM(pengeluaran.jumlah) as total_jumlah'),
        ];

        if (self::hasPengeluaranLedgerColumns()) {
            $select[] = DB::raw('SUM(pengeluaran.debet) as total_debet');
            $select[] = DB::raw('SUM(pengeluaran.kredit) as total_kredit');
            $select[] = DB::raw('SUM(pengeluaran.saldo) as total_saldo');
        } else {
            $select[] = DB::raw('0 as total_debet');
            $select[] = DB::raw('SUM(pengeluaran.jumlah) as total_kredit');
            $select[] = DB::raw('SUM(pengeluaran.jumlah) * -1 as total_saldo');
        }

        return $query
            ->select($select)
            ->groupBy('kp.nomor_kategori', 'kp.nama_kategori')
            ->orderBy('kp.nomor_kategori')
            ->get();
    }

    public static function tahunList(): Collection
    {
        $pendapatanYears = Pendapatan::select('tahun')
            ->distinct()
            ->pluck('tahun');

        $pengeluaranYears = Pengeluaran::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->pluck('tahun');

        return $pendapatanYears
            ->merge($pengeluaranYears)
            ->push(now()->year)
            ->filter()
            ->map(fn ($tahun) => (int) $tahun)
            ->unique()
            ->sortDesc()
            ->values();
    }

    private static function hasPengeluaranLedgerColumns(): bool
    {
        return Schema::hasColumn('pengeluaran', 'jenis_transaksi')
            && Schema::hasColumn('pengeluaran', 'debet')
            && Schema::hasColumn('pengeluaran', 'kredit')
            && Schema::hasColumn('pengeluaran', 'saldo');
    }
}
