<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengeluaranRequest;
use App\Models\Karyawan;
use App\Models\KategoriPengeluaran;
use App\Models\SubPengeluaran;
use App\Services\ActorAccessService;
use App\Services\PengeluaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActorFormController extends Controller
{
    public function __construct(private PengeluaranService $pengeluaranService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'actor' => ActorAccessService::actorPayload($user),
            'menus' => ActorAccessService::menusForUser($user),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $menu = $this->accessibleMenu($request, $slug);

        if ($menu['type'] !== 'pengeluaran') {
            return response()->json([
                'success' => true,
                'actor' => ActorAccessService::actorPayload($request->user()),
                'menu' => $menu,
                'form' => [
                    'mode' => 'read_only',
                    'available_endpoints' => $this->readOnlyEndpoints($menu['type']),
                ],
            ]);
        }

        $kategori = $this->categoryForMenu($menu);
        $subOptions = $kategori->subPengeluaran()
            ->orderBy('nomor_sub')
            ->get()
            ->map(fn (SubPengeluaran $sub) => [
                'id' => $sub->id,
                'nomor_sub' => $sub->nomor_sub,
                'nama_sub' => $sub->nama_sub,
                'jenis_detail' => $sub->jenis_detail,
                'submit_endpoint' => route('api.aktor.forms.store', [$menu['slug'], $sub->id], false),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'actor' => ActorAccessService::actorPayload($request->user()),
            'menu' => $menu,
            'category' => [
                'id' => $kategori->id,
                'nomor_kategori' => $kategori->nomor_kategori,
                'nama_kategori' => $kategori->nama_kategori,
            ],
            'sub_options' => $subOptions,
            'form' => [
                'mode' => 'create',
                'method' => 'POST',
                'schemas_by_profile' => ActorAccessService::formSchemasByProfile(),
                'satuan_options' => ['kg', 'ton', 'janjang', 'HK', 'ha', 'liter', 'sak', 'rit', 'HM', 'jam', 'unit', 'pcs', 'set', 'meter', 'batang', 'roll', 'paket', 'orang', 'bulan', 'hari'],
            ],
            'workers' => Karyawan::aktif()
                ->orderBy('nama')
                ->get(['id', 'nama', 'jenis_kelamin', 'no_hp', 'status']),
        ]);
    }

    public function store(PengeluaranRequest $request, string $slug, string $subKategori): JsonResponse
    {
        $menu = $this->accessibleMenu($request, $slug, 'pengeluaran');
        $kategori = $this->categoryForMenu($menu);
        $sub = $this->subFromRoute($kategori, $subKategori);

        $validated = $request->pengeluaranData();

        abort_if((int) $validated['kategori_id'] !== $kategori->id || (int) $validated['sub_id'] !== $sub->id, 422);

        $pengeluaran = $this->pengeluaranService->create(
            $validated,
            $request->detailData(),
            $request->workerDetails()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil disimpan.',
            'data' => $pengeluaran->load($this->pengeluaranService->relations()),
        ], 201);
    }

    private function accessibleMenu(Request $request, string $slug, ?string $requiredType = null): array
    {
        $menu = ActorAccessService::menuForSlug($slug);

        abort_if(! $menu, 404, 'Menu tidak ditemukan.');
        abort_if(! ActorAccessService::canAccess($request->user(), $slug), 403, 'Aktor tidak memiliki akses ke menu ini.');

        if ($requiredType !== null) {
            abort_if($menu['type'] !== $requiredType, 404, 'Menu ini tidak memiliki form transaksi.');
        }

        return $menu;
    }

    private function categoryForMenu(array $menu): KategoriPengeluaran
    {
        return KategoriPengeluaran::where('nomor_kategori', $menu['category_number'])->firstOrFail();
    }

    private function subFromRoute(KategoriPengeluaran $kategori, string $subKategori): SubPengeluaran
    {
        $query = SubPengeluaran::where('kategori_id', $kategori->id);

        if (ctype_digit($subKategori)) {
            return $query->whereKey((int) $subKategori)->firstOrFail();
        }

        return $query->where('nama_sub', urldecode($subKategori))->firstOrFail();
    }

    private function readOnlyEndpoints(string $type): array
    {
        return match ($type) {
            'rekap' => [
                ['method' => 'GET', 'path' => route('api.rekap.index', [], false)],
                ['method' => 'GET', 'path' => route('api.rekap.laporan-lengkap', [], false)],
            ],
            default => [],
        };
    }
}
