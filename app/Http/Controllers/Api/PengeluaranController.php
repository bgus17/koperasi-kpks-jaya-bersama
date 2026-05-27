<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengeluaranRequest;
use App\Models\Pengeluaran;
use App\Services\KeuanganLedgerService;
use App\Services\PengeluaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function __construct(private PengeluaranService $pengeluaranService) {}

    /**
     * GET /api/pengeluaran
     * Ambil semua data pengeluaran dengan filter opsional.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pengeluaran::with($this->pengeluaranService->relations());

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('kategori')) {
            $query->whereHas('kategori', function ($q) use ($request) {
                $q->where('nama_kategori', $request->kategori);
            });
        }

        if ($request->filled('nomor_kategori')) {
            $query->whereHas('kategori', function ($q) use ($request) {
                $q->where('nomor_kategori', $request->nomor_kategori);
            });
        }

        if ($request->filled('search')) {
            $this->pengeluaranService->applySearch($query, $request->search);
        }

        $perPage = $request->input('per_page', 20);
        $pengeluaran = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pengeluaran->items(),
            'meta' => [
                'current_page' => $pengeluaran->currentPage(),
                'last_page' => $pengeluaran->lastPage(),
                'per_page' => $pengeluaran->perPage(),
                'total' => $pengeluaran->total(),
            ],
        ]);
    }

    /**
     * POST /api/pengeluaran
     * Tambah data pengeluaran baru.
     */
    public function store(PengeluaranRequest $request): JsonResponse
    {
        $pengeluaran = $this->pengeluaranService->create(
            $request->pengeluaranData(),
            $request->detailData(),
            $request->workerDetails()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil ditambahkan.',
            'data' => $pengeluaran->load($this->pengeluaranService->relations()),
        ], 201);
    }

    /**
     * GET /api/pengeluaran/{id}
     * Tampilkan satu data pengeluaran.
     */
    public function show(Pengeluaran $pengeluaran): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $pengeluaran->load($this->pengeluaranService->relations()),
        ]);
    }

    /**
     * PUT/PATCH /api/pengeluaran/{id}
     * Update data pengeluaran.
     */
    public function update(PengeluaranRequest $request, Pengeluaran $pengeluaran): JsonResponse
    {
        $pengeluaran = $this->pengeluaranService->update(
            $pengeluaran,
            $request->pengeluaranData(),
            $request->detailData(),
            $request->workerDetails()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil diperbarui.',
            'data' => $pengeluaran,
        ]);
    }

    /**
     * DELETE /api/pengeluaran/{id}
     * Hapus data pengeluaran.
     */
    public function destroy(Pengeluaran $pengeluaran): JsonResponse
    {
        $pengeluaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil dihapus.',
        ]);
    }

    /**
     * GET /api/pengeluaran/summary
     * Ringkasan total pengeluaran per kategori untuk tahun tertentu.
     */
    public function summary(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', now()->year);

        $summary = KeuanganLedgerService::pengeluaranPerKategori((int) $tahun);
        $ledgerSummary = KeuanganLedgerService::summary((int) $tahun);

        $grandTotal = $summary->sum('total_jumlah');

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'data' => $summary,
            'grand_total' => $grandTotal,
            'grand_total_ledger' => [
                'debet' => $ledgerSummary['pengeluaran_debet'],
                'kredit' => $ledgerSummary['pengeluaran_kredit'],
                'saldo' => $ledgerSummary['pengeluaran_saldo'],
            ],
        ]);
    }
}
