<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RekapResource;
use App\Models\Rekap;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Services\KeuanganLedgerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RekapController extends Controller
{
    /**
     * GET /api/rekap
     * Ambil semua data rekap per tahun.
     */
    public function index(Request $request): JsonResponse
    {
        $rekap = Rekap::orderBy('tahun', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => RekapResource::collection($rekap),
        ]);
    }

    /**
     * GET /api/rekap/{tahun}
     * Ambil rekap untuk tahun tertentu beserta breakdown detail.
     */
    public function show(Request $request, int $tahun): JsonResponse
    {
        $rekap = Rekap::where('tahun', $tahun)->firstOrFail();
        $bulan = $this->bulanInput($request);

        // Ringkasan pendapatan per kategori
        $pendapatanSummary = Pendapatan::where('tahun', $tahun)
            ->when($bulan, fn ($query) => $query->whereMonth('tanggal', $bulan))
            ->selectRaw('nomor_kategori, kategori, SUM(debet) as total_debet, SUM(kredit) as total_kredit')
            ->groupBy('nomor_kategori', 'kategori')
            ->orderBy('nomor_kategori')
            ->get();

        // Ringkasan pengeluaran per kategori
        $pengeluaranSummary = KeuanganLedgerService::pengeluaranPerKategori($tahun, $bulan);
        $ledgerSummary = KeuanganLedgerService::summary($tahun, $bulan);

        return response()->json([
            'success' => true,
            'data'    => [
                'rekap'              => new RekapResource($rekap),
                'pendapatan_summary' => $pendapatanSummary,
                'pengeluaran_summary'=> $pengeluaranSummary,
                'ledger_summary'     => $ledgerSummary,
                'laporan'            => KeuanganLedgerService::laporan($tahun, $bulan),
            ],
        ]);
    }

    /**
     * GET /api/rekap/laporan-lengkap
     * Laporan lengkap: semua baris pendapatan & pengeluaran + rekap grand total.
     */
    public function laporanLengkap(Request $request): JsonResponse
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $bulan = $this->bulanInput($request);

        $rekap = Rekap::where('tahun', $tahun)->first();

        $pendapatan = Pendapatan::where('tahun', $tahun)
            ->when($bulan, fn ($query) => $query->whereMonth('tanggal', $bulan))
            ->orderBy('nomor_kategori')
            ->orderBy('id')
            ->get();

        $pengeluaran = Pengeluaran::with(array_merge(['kategori', 'sub'], Pengeluaran::detailRelations()))
            ->whereYear('tanggal', $tahun)
            ->when($bulan, fn ($query) => $query->whereMonth('tanggal', $bulan))
            ->join('kategori_pengeluaran as kp', 'pengeluaran.kategori_id', '=', 'kp.id')
            ->join('sub_pengeluaran as sp', 'pengeluaran.sub_id', '=', 'sp.id')
            ->select('pengeluaran.*')
            ->orderBy('kp.nomor_kategori')
            ->orderBy('sp.nomor_sub')
            ->orderBy('pengeluaran.id')
            ->get();

        return response()->json([
            'success' => true,
            'tahun'   => $tahun,
            'bulan'   => $bulan,
            'rekap'   => $rekap ? new RekapResource($rekap) : null,
            'ledger_summary' => KeuanganLedgerService::summary($tahun, $bulan),
            'laporan' => KeuanganLedgerService::laporan($tahun, $bulan),
            'pendapatan'  => $pendapatan,
            'pengeluaran' => $pengeluaran,
        ]);
    }

    private function bulanInput(Request $request): ?int
    {
        $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : null;

        return $bulan >= 1 && $bulan <= 12 ? $bulan : null;
    }
}
