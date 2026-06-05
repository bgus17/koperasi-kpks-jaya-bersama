<?php

namespace App\Services;

use App\Models\KategoriPengeluaran;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KeuanganLedgerService
{
    public static function summary(?int $tahun = null, ?int $bulan = null): array
    {
        $pendapatanQuery = Pendapatan::query();
        $pengeluaranQuery = Pengeluaran::query();

        self::applyPendapatanPeriod($pendapatanQuery, $tahun, $bulan);
        self::applyPengeluaranPeriod($pengeluaranQuery, $tahun, $bulan);

        $pendapatanDebet = (int) (clone $pendapatanQuery)->sum('debet');
        $pendapatanKredit = (int) (clone $pendapatanQuery)->sum('kredit');
        $jumlahPendapatan = (int) (clone $pendapatanQuery)->count();

        // Carry-forward: jika tahun ini belum ada data pendapatan sama sekali,
        // ambil semua data pendapatan dari tahun sebelumnya.
        if ($tahun && !$bulan && $jumlahPendapatan === 0) {
            $prevYear = $tahun - 1;
            $prevDebet = (int) Pendapatan::where('tahun', $prevYear)->sum('debet');
            $prevKredit = (int) Pendapatan::where('tahun', $prevYear)->sum('kredit');
            $pendapatanDebet += $prevDebet;
            $pendapatanKredit += $prevKredit;
        }

        $saldoPendapatan = $pendapatanDebet - $pendapatanKredit;

        $pengeluaranJumlah = (int) (clone $pengeluaranQuery)->sum('jumlah');
        $jumlahPengeluaran = (int) (clone $pengeluaranQuery)->count();

        // Selalu gunakan jumlah untuk kredit pengeluaran agar semua
        // pengeluaran tercatat (termasuk jenis_transaksi = 'saldo').
        $pengeluaranDebet = (int) (clone $pengeluaranQuery)->sum('debet');
        $pengeluaranKredit = $pengeluaranJumlah;
        $mutasiPengeluaran = -$pengeluaranJumlah;

        $totalDebet = $pendapatanDebet + $pengeluaranDebet;
        $totalKredit = $pendapatanKredit + $pengeluaranKredit;
        $saldoAkhir = $saldoPendapatan + $mutasiPengeluaran;

        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
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

    public static function pengeluaranPerKategori(int $tahun, ?int $bulan = null): Collection
    {
        $query = DB::table('pengeluaran')
            ->whereYear('tanggal', $tahun)
            ->join('kategori_pengeluaran as kp', 'pengeluaran.kategori_id', '=', 'kp.id');

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }

        $select = [
            'kp.nomor_kategori',
            'kp.nama_kategori as kategori',
            DB::raw('SUM(pengeluaran.jumlah) as total_jumlah'),
        ];

        // Selalu gunakan SUM(jumlah) agar semua pengeluaran tercatat.
        $select[] = DB::raw('SUM(pengeluaran.debet) as total_debet');
        $select[] = DB::raw('SUM(pengeluaran.jumlah) as total_kredit');
        $select[] = DB::raw('SUM(pengeluaran.jumlah) * -1 as total_saldo');

        return $query
            ->select($select)
            ->groupBy('kp.nomor_kategori', 'kp.nama_kategori')
            ->orderBy('kp.nomor_kategori')
            ->get();
    }

    public static function laporan(int $tahun, ?int $bulan = null): array
    {
        $bulan = self::normalizeBulan($bulan);
        $periode = self::periode($tahun, $bulan);
        $summary = self::summary($tahun, $bulan);

        $runningSaldo = 0;
        $sections = collect();
        $flatRows = collect();

        $pendapatanRows = self::pendapatanRows($tahun, $bulan);
        $pendapatanDetails = collect();

        foreach ($pendapatanRows as $index => $row) {
            $runningSaldo += $row['debet'] - $row['kredit'];

            $detail = [
                'type' => 'detail',
                'group' => 'I',
                'nomor' => $index + 1,
                'keterangan' => $row['keterangan'],
                'debet' => $row['debet'],
                'kredit' => $row['kredit'],
                'saldo_delta' => $row['debet'] - $row['kredit'],
                'saldo' => $runningSaldo,
                'has_data' => ($row['debet'] + $row['kredit']) > 0,
            ];

            $pendapatanDetails->push($detail);
            $flatRows->push($detail);
        }

        $sections->push([
            'type' => 'section',
            'nomor' => 'I',
            'nama' => 'Pendapatan',
            'debet' => $pendapatanDetails->sum('debet'),
            'kredit' => $pendapatanDetails->sum('kredit'),
            'rows' => $pendapatanDetails,
        ]);

        $totalsBySub = self::pengeluaranTotalsBySub($tahun, $bulan);
        $categories = KategoriPengeluaran::with([
            'subPengeluaran' => fn ($query) => $query
                ->orderBy('nomor_sub')
                ->orderBy('id'),
        ])
            ->orderBy('urutan')
            ->orderBy('nomor_kategori')
            ->get();

        foreach ($categories as $category) {
            $details = collect();

            foreach ($category->subPengeluaran as $sub) {
                $total = $totalsBySub->get($sub->id);
                $debet = (int) ($total->total_debet ?? 0);
                $kredit = (int) ($total->total_kredit ?? 0);
                $saldoDelta = (int) ($total->total_saldo ?? ($debet - $kredit));
                $runningSaldo += $saldoDelta;

                // Kolom saldo untuk pengeluaran menampilkan total
                // pengeluaran per sub-kategori, bukan saldo berjalan.
                $pengeluaranAmount = $kredit > 0 ? $kredit : abs($saldoDelta);

                $detail = [
                    'type' => 'detail',
                    'group' => $category->nomor_kategori,
                    'nomor' => $sub->nomor_sub,
                    'keterangan' => $sub->nama_sub,
                    'debet' => $debet,
                    'kredit' => $kredit,
                    'saldo_delta' => $saldoDelta,
                    'saldo' => $pengeluaranAmount,
                    'has_data' => ($debet + $kredit + abs($saldoDelta)) > 0,
                ];

                $details->push($detail);
                $flatRows->push($detail);
            }

            $sections->push([
                'type' => 'section',
                'nomor' => $category->nomor_kategori,
                'nama' => $category->nama_kategori,
                'debet' => $details->sum('debet'),
                'kredit' => $details->sum('kredit'),
                'rows' => $details,
            ]);
        }

        return [
            'periode' => $periode,
            'summary' => $summary,
            'sections' => $sections,
            'rows' => $flatRows,
            'saldo_berjalan' => $runningSaldo,
        ];
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

    public static function bulanList(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    private static function pendapatanRows(int $tahun, ?int $bulan): Collection
    {
        $rows = Pendapatan::query()
            ->where('tahun', $tahun)
            ->when($bulan, fn ($query) => $query->whereMonth('tanggal', $bulan))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        // Jika tahun ini belum ada data pendapatan sama sekali,
        // ambil semua data pendapatan dari tahun sebelumnya.
        if ($rows->isEmpty() && !$bulan) {
            $prevYear = $tahun - 1;
            $rows = Pendapatan::query()
                ->where('tahun', $prevYear)
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get();
        }

        $preferredOrder = [
            'Saldo Per 31 Des' => 1,
            'Saldo Per 31 Desember' => 1,
            'Pendapatan Diterima' => 2,
            'Pendapatan Di terima' => 2,
            'Penjualan Barang' => 3,
        ];

        return $rows
            ->groupBy(fn (Pendapatan $row) => $row->sub_kategori ?: $row->kategori)
            ->map(function (Collection $items, string $label) use ($preferredOrder) {
                $first = $items->first();

                return [
                    'order' => $preferredOrder[$label] ?? 99,
                    'first_date' => $items->min('tanggal')?->format('Y-m-d') ?? '',
                    'keterangan' => $label,
                    'debet' => (int) $items->sum('debet'),
                    'kredit' => (int) $items->sum('kredit'),
                    'kategori' => $first?->kategori,
                ];
            })
            ->sortBy([
                ['order', 'asc'],
                ['first_date', 'asc'],
                ['keterangan', 'asc'],
            ])
            ->values();
    }

    private static function pengeluaranTotalsBySub(int $tahun, ?int $bulan): Collection
    {
        $query = DB::table('pengeluaran')
            ->whereYear('tanggal', $tahun);

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }

        // Selalu gunakan SUM(jumlah) sebagai total_kredit agar semua
        // pengeluaran tercatat, termasuk yang jenis_transaksi = 'saldo'
        // (di mana kolom kredit = 0).
        $select = 'sub_id, SUM(debet) as total_debet, SUM(jumlah) as total_kredit, SUM(jumlah) * -1 as total_saldo';

        return $query
            ->selectRaw($select)
            ->groupBy('sub_id')
            ->get()
            ->keyBy('sub_id');
    }

    private static function periode(int $tahun, ?int $bulan): array
    {
        $start = $bulan
            ? CarbonImmutable::create($tahun, $bulan, 1)->startOfDay()
            : CarbonImmutable::create($tahun, 1, 1)->startOfDay();
        $end = $bulan ? $start->endOfMonth() : $start->endOfYear();
        $bulanList = self::bulanList();

        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'bulan_label' => $bulan ? $bulanList[$bulan] : null,
            'label' => $bulan ? $bulanList[$bulan] . ' ' . $tahun : 'Tahun ' . $tahun,
            'judul' => 'Dana Kebun Tahun ' . $tahun,
            'subjudul' => $bulan
                ? 'Periode ' . $bulanList[$bulan] . ' ' . $tahun
                : 'Per 31 Desember ' . $tahun,
            'tanggal_mulai' => $start,
            'tanggal_sampai' => $end,
            'tanggal_tutup' => $end,
        ];
    }

    private static function normalizeBulan(?int $bulan): ?int
    {
        return $bulan >= 1 && $bulan <= 12 ? $bulan : null;
    }

    private static function applyPendapatanPeriod($query, ?int $tahun, ?int $bulan): void
    {
        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
    }

    private static function applyPengeluaranPeriod($query, ?int $tahun, ?int $bulan): void
    {
        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
    }

    private static function hasPengeluaranLedgerColumns(): bool
    {
        return Schema::hasColumn('pengeluaran', 'jenis_transaksi')
            && Schema::hasColumn('pengeluaran', 'debet')
            && Schema::hasColumn('pengeluaran', 'kredit')
            && Schema::hasColumn('pengeluaran', 'saldo');
    }
}
