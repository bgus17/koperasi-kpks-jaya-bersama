<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Services\ActorAccessService;
use App\Services\KeuanganLedgerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->periodInput($request);

        $bulanList = KeuanganLedgerService::bulanList();
        $summary = KeuanganLedgerService::summary($tahun, $bulan);
        $tahunList = KeuanganLedgerService::tahunList();
        $periodeLabel = $bulan ? $bulanList[$bulan] . ' ' . $tahun : 'Tahun ' . $tahun;

        $pendapatanNeto = max(0, (int) $summary['pendapatan_saldo']);
        $biayaOperasional = (int) $summary['pengeluaran_kredit'];
        $rasioBiaya = $pendapatanNeto > 0 ? round(($biayaOperasional / $pendapatanNeto) * 100, 1) : null;
        $marginSaldo = $pendapatanNeto > 0 ? round(((int) $summary['saldo_akhir'] / $pendapatanNeto) * 100, 1) : null;
        $rataRataBiaya = $summary['jumlah_pengeluaran'] > 0
            ? (int) round($biayaOperasional / $summary['jumlah_pengeluaran'])
            : 0;

        $biayaPerKategori = $this->biayaPerKategori($tahun, $bulan, $biayaOperasional);
        $topBiayaOperasional = $this->topBiayaOperasional($tahun, $bulan);
        $komposisiPendapatan = $this->komposisiPendapatan($tahun, $bulan);
        $trenBulanan = $this->trenBulanan($tahun, $bulanList);
        $transaksiTerbaru = $this->transaksiTerbaru($tahun, $bulan);
        $tagihanOperasional = $this->tagihanOperasional($tahun, $bulan);

        return view('dashboard', compact(
            'tahun',
            'bulan',
            'bulanList',
            'tahunList',
            'summary',
            'periodeLabel',
            'pendapatanNeto',
            'biayaOperasional',
            'rasioBiaya',
            'marginSaldo',
            'rataRataBiaya',
            'biayaPerKategori',
            'topBiayaOperasional',
            'komposisiPendapatan',
            'trenBulanan',
            'transaksiTerbaru',
            'tagihanOperasional'
        ));
    }

    private function biayaPerKategori(int $tahun, ?int $bulan, int $totalBiaya): Collection
    {
        return KeuanganLedgerService::pengeluaranPerKategori($tahun, $bulan)
            ->map(function ($row) use ($totalBiaya) {
                $total = (int) $row->total_jumlah;
                $slug = $this->slugForCategoryNumber($row->nomor_kategori);

                return [
                    'nomor' => $row->nomor_kategori,
                    'kategori' => $row->kategori,
                    'total' => $total,
                    'persen' => $totalBiaya > 0 ? round(($total / $totalBiaya) * 100, 1) : 0,
                    'url' => $slug ? route('pengeluaran.kategori', $slug) : route('pengeluaran.index'),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function topBiayaOperasional(int $tahun, ?int $bulan): Collection
    {
        return $this->pengeluaranPeriodQuery($tahun, $bulan)
            ->with(['kategori', 'sub'])
            ->get()
            ->groupBy(fn (Pengeluaran $row) => $row->sub_id ?: 'kategori-' . $row->kategori_id)
            ->map(function (Collection $items) {
                $first = $items->first();

                return [
                    'nama' => $first->sub?->nama_sub ?? $first->kategori?->nama_kategori ?? 'Pengeluaran',
                    'kategori' => $first->kategori?->nama_kategori ?? 'Tanpa kategori',
                    'total' => (int) $items->sum('jumlah'),
                    'transaksi' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();
    }

    private function komposisiPendapatan(int $tahun, ?int $bulan): Collection
    {
        $rows = $this->pendapatanPeriodQuery($tahun, $bulan)->get();
        $total = max(1, (int) $rows->sum(fn (Pendapatan $row) => max(0, $row->debet - $row->kredit)));

        return $rows
            ->groupBy(fn (Pendapatan $row) => $row->sub_kategori ?: $row->kategori ?: 'Pendapatan')
            ->map(function (Collection $items, string $label) use ($total) {
                $nominal = (int) $items->sum(fn (Pendapatan $row) => max(0, $row->debet - $row->kredit));

                return [
                    'label' => $label,
                    'total' => $nominal,
                    'persen' => round(($nominal / $total) * 100, 1),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();
    }

    private function trenBulanan(int $tahun, array $bulanList): Collection
    {
        $tren = collect(range(1, 12))->map(function (int $bulan) use ($tahun, $bulanList) {
            $summary = KeuanganLedgerService::summary($tahun, $bulan);
            $pendapatan = max(0, (int) $summary['pendapatan_saldo']);
            $pengeluaran = (int) $summary['pengeluaran_kredit'];

            return [
                'bulan' => substr($bulanList[$bulan], 0, 3),
                'pendapatan' => $pendapatan,
                'pengeluaran' => $pengeluaran,
                'saldo' => (int) $summary['saldo_akhir'],
            ];
        });

        $maxValue = max(1, (int) $tren->max(fn (array $row) => max($row['pendapatan'], $row['pengeluaran'])));

        return $tren->map(function (array $row) use ($maxValue) {
            $row['pendapatan_persen'] = round(($row['pendapatan'] / $maxValue) * 100, 1);
            $row['pengeluaran_persen'] = round(($row['pengeluaran'] / $maxValue) * 100, 1);

            return $row;
        });
    }

    private function transaksiTerbaru(int $tahun, ?int $bulan): Collection
    {
        $pendapatan = $this->pendapatanPeriodQuery($tahun, $bulan)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->toBase()
            ->map(fn (Pendapatan $row) => [
                'tanggal' => $row->tanggal,
                'jenis' => 'Pendapatan',
                'arah' => 'masuk',
                'label' => $row->sub_kategori ?: $row->kategori,
                'keterangan' => $row->keterangan,
                'nominal' => max(0, (int) $row->debet - (int) $row->kredit),
                'sort_key' => ($row->tanggal?->timestamp ?? 0) * 100000 + $row->id,
            ]);

        $pengeluaran = $this->pengeluaranPeriodQuery($tahun, $bulan)
            ->with(['kategori', 'sub'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->toBase()
            ->map(fn (Pengeluaran $row) => [
                'tanggal' => $row->tanggal,
                'jenis' => 'Pengeluaran',
                'arah' => 'keluar',
                'label' => $row->sub?->nama_sub ?? $row->kategori?->nama_kategori ?? 'Pengeluaran',
                'keterangan' => $row->keterangan,
                'nominal' => (int) $row->jumlah,
                'sort_key' => ($row->tanggal?->timestamp ?? 0) * 100000 + $row->id,
            ]);

        return $pendapatan
            ->merge($pengeluaran)
            ->sortByDesc('sort_key')
            ->take(8)
            ->values();
    }

    private function tagihanOperasional(int $tahun, ?int $bulan): array
    {
        $query = $this->pengeluaranPeriodQuery($tahun, $bulan)
            ->where('sudah_bayar', false);

        return [
            'jumlah' => (int) (clone $query)->count(),
            'total' => (int) (clone $query)->sum('jumlah'),
        ];
    }

    private function pendapatanPeriodQuery(int $tahun, ?int $bulan): Builder
    {
        return Pendapatan::query()
            ->where('tahun', $tahun)
            ->when($bulan, fn (Builder $query) => $query->whereMonth('tanggal', $bulan));
    }

    private function pengeluaranPeriodQuery(int $tahun, ?int $bulan): Builder
    {
        return Pengeluaran::query()
            ->whereYear('tanggal', $tahun)
            ->when($bulan, fn (Builder $query) => $query->whereMonth('tanggal', $bulan));
    }

    private function periodInput(Request $request): array
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $tahun = $tahun >= 2000 && $tahun <= 2100 ? $tahun : now()->year;

        $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : null;
        $bulan = $bulan >= 1 && $bulan <= 12 ? $bulan : null;

        return [$tahun, $bulan];
    }

    private function slugForCategoryNumber(?string $categoryNumber): ?string
    {
        foreach (ActorAccessService::MENUS as $slug => $menu) {
            if (($menu['category_number'] ?? null) === $categoryNumber) {
                return $slug;
            }
        }

        return null;
    }
}
