<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PendapatanRequest;
use App\Http\Resources\PendapatanResource;
use App\Models\Pendapatan;
use App\Services\KeuanganLedgerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PendapatanController extends Controller
{
    /**
     * GET /api/pendapatan
     * Ambil semua data pendapatan dengan filter opsional.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pendapatan::query();

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('nomor_kategori')) {
            $query->where('nomor_kategori', $request->nomor_kategori);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sub_kategori', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $perPage    = $request->input('per_page', 20);
        $pendapatan = $query->orderBy('nomor_kategori')->orderBy('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => PendapatanResource::collection($pendapatan),
            'meta'    => [
                'current_page' => $pendapatan->currentPage(),
                'last_page'    => $pendapatan->lastPage(),
                'per_page'     => $pendapatan->perPage(),
                'total'        => $pendapatan->total(),
            ],
        ]);
    }

    /**
     * POST /api/pendapatan
     * Tambah data pendapatan baru.
     */
    public function store(PendapatanRequest $request): JsonResponse
    {
        $pendapatan = Pendapatan::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data pendapatan berhasil ditambahkan.',
            'data'    => new PendapatanResource($pendapatan),
        ], 201);
    }

    /**
     * GET /api/pendapatan/{id}
     * Tampilkan satu data pendapatan.
     */
    public function show(Pendapatan $pendapatan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new PendapatanResource($pendapatan),
        ]);
    }

    /**
     * PUT/PATCH /api/pendapatan/{id}
     * Update data pendapatan.
     */
    public function update(PendapatanRequest $request, Pendapatan $pendapatan): JsonResponse
    {
        $pendapatan->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data pendapatan berhasil diperbarui.',
            'data'    => new PendapatanResource($pendapatan->fresh()),
        ]);
    }

    /**
     * DELETE /api/pendapatan/{id}
     * Hapus data pendapatan.
     */
    public function destroy(Pendapatan $pendapatan): JsonResponse
    {
        $pendapatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pendapatan berhasil dihapus.',
        ]);
    }

    /**
     * GET /api/pendapatan/summary
     * Ringkasan total debet, kredit, saldo per kategori untuk tahun tertentu.
     */
    public function summary(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', now()->year);

        $summary = Pendapatan::where('tahun', $tahun)
            ->selectRaw('nomor_kategori, kategori, SUM(debet) as total_debet, SUM(kredit) as total_kredit, MAX(saldo) as saldo_akhir')
            ->groupBy('nomor_kategori', 'kategori')
            ->orderBy('nomor_kategori')
            ->get();

        $grandDebet  = $summary->sum('total_debet');
        $grandKredit = $summary->sum('total_kredit');
        $ledgerSummary = KeuanganLedgerService::summary((int) $tahun);

        return response()->json([
            'success' => true,
            'tahun'   => $tahun,
            'data'    => $summary,
            'grand_total' => [
                'debet'  => $grandDebet,
                'kredit' => $grandKredit,
                'saldo' => $ledgerSummary['pendapatan_saldo'],
            ],
            'grand_total_terintegrasi' => [
                'debet' => $ledgerSummary['total_debet'],
                'kredit' => $ledgerSummary['total_kredit'],
                'saldo_akhir' => $ledgerSummary['saldo_akhir'],
                'pengeluaran_kredit' => $ledgerSummary['pengeluaran_kredit'],
            ],
        ]);
    }
}
