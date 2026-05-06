<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengeluaranRequest;
use App\Models\Karyawan;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PengeluaranController extends Controller
{
    /**
     * GET /api/pengeluaran
     * Ambil semua data pengeluaran dengan filter opsional.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pengeluaran::with($this->pengeluaranRelations());

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
            $this->applySearch($query, $request->search);
        }

        $perPage     = $request->input('per_page', 20);
        $pengeluaran = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $pengeluaran->items(),
            'meta'    => [
                'current_page' => $pengeluaran->currentPage(),
                'last_page'    => $pengeluaran->lastPage(),
                'per_page'     => $pengeluaran->perPage(),
                'total'        => $pengeluaran->total(),
            ],
        ]);
    }

    /**
     * POST /api/pengeluaran
     * Tambah data pengeluaran baru.
     */
    public function store(PengeluaranRequest $request): JsonResponse
    {
        $pengeluaran = null;

        DB::transaction(function () use ($request, &$pengeluaran) {
            $pengeluaran = Pengeluaran::create($request->pengeluaranData());
            $this->syncDetail($pengeluaran, $request->detailData());
            $this->syncPekerja($pengeluaran, $request->workerDetails());
        });

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil ditambahkan.',
            'data'    => $pengeluaran->load($this->pengeluaranRelations()),
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
            'data'    => $pengeluaran->load($this->pengeluaranRelations()),
        ]);
    }

    /**
     * PUT/PATCH /api/pengeluaran/{id}
     * Update data pengeluaran.
     */
    public function update(PengeluaranRequest $request, Pengeluaran $pengeluaran): JsonResponse
    {
        DB::transaction(function () use ($request, $pengeluaran) {
            $pengeluaran->update($request->pengeluaranData());
            $this->syncDetail($pengeluaran, $request->detailData());
            $this->syncPekerja($pengeluaran, $request->workerDetails());
        });

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil diperbarui.',
            'data'    => $pengeluaran->fresh($this->pengeluaranRelations()),
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

        $summary = DB::table('pengeluaran')
            ->whereYear('tanggal', $tahun)
            ->join('kategori_pengeluaran as kp', 'pengeluaran.kategori_id', '=', 'kp.id')
            ->selectRaw('kp.nomor_kategori, kp.nama_kategori as kategori, SUM(pengeluaran.jumlah) as total_jumlah')
            ->groupBy('kp.nomor_kategori', 'kp.nama_kategori')
            ->orderBy('kp.nomor_kategori')
            ->get();

        $grandTotal = $summary->sum('total_jumlah');

        return response()->json([
            'success'     => true,
            'tahun'       => $tahun,
            'data'        => $summary,
            'grand_total' => $grandTotal,
        ]);
    }

    private function pengeluaranRelations(): array
    {
        return array_merge(['kategori', 'sub', 'pekerjaDetail'], Pengeluaran::detailRelations());
    }

    private function applySearch($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->whereHas('sub', fn ($sub) => $sub->where('nama_sub', 'like', "%{$search}%"))
                ->orWhereHas('kategori', fn ($kat) => $kat->where('nama_kategori', 'like', "%{$search}%"))
                ->orWhereHas('pekerjaDetail', fn ($detail) => $detail->where('nama_karyawan_snapshot', 'like', "%{$search}%"))
                ->orWhere('keterangan', 'like', "%{$search}%");

            foreach ($this->detailSearchColumns() as $relation => $columns) {
                $q->orWhereHas($relation, function ($detail) use ($columns, $search) {
                    $detail->where(function ($detailQuery) use ($columns, $search) {
                        foreach ($columns as $column) {
                            $detailQuery->orWhere($column, 'like', "%{$search}%");
                        }
                    });
                });
            }
        });
    }

    private function detailSearchColumns(): array
    {
        return [
            'angkutanDetail' => ['blok', 'supplier_vendor', 'no_referensi'],
            'panenDetail' => ['blok', 'mandor'],
            'kutipBerondolDetail' => ['blok', 'mandor'],
            'perawatanDetail' => ['blok', 'mandor'],
            'pupukDetail' => ['blok', 'supplier_vendor', 'no_referensi'],
            'umumDetail' => ['supplier_vendor', 'no_referensi'],
        ];
    }

    private function syncDetail(Pengeluaran $pengeluaran, array $detailData): void
    {
        $pengeluaran->loadMissing(['kategori', 'sub']);

        $activeRelation = Pengeluaran::detailRelationForProfile($pengeluaran->detailProfile());

        foreach (Pengeluaran::detailRelations() as $relation) {
            if ($relation !== $activeRelation) {
                $pengeluaran->{$relation}()->delete();
            }
        }

        $pengeluaran->{$activeRelation}()->updateOrCreate(
            ['pengeluaran_id' => $pengeluaran->id],
            $detailData
        );
    }

    private function syncPekerja(Pengeluaran $pengeluaran, array $details): void
    {
        $pengeluaran->pekerjaDetail()->delete();

        if (empty($details)) {
            return;
        }

        $karyawan = Karyawan::whereIn('id', collect($details)->pluck('karyawan_id'))
            ->get()
            ->keyBy('id');

        foreach ($details as $detail) {
            $worker = $karyawan->get($detail['karyawan_id']);

            if (!$worker) {
                continue;
            }

            $pengeluaran->pekerjaDetail()->create(array_merge($detail, [
                'nama_karyawan_snapshot' => $worker->nama,
            ]));
        }
    }
}
