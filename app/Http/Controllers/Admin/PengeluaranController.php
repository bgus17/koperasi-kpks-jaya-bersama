<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengeluaranRequest;
use App\Models\KategoriPengeluaran;
use App\Models\Karyawan;
use App\Models\Pengeluaran;
use App\Models\SubPengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengeluaranController extends Controller
{
    private array $kategoriSlug = [
        'biaya-produksi'  => 'II',
        'biaya-perawatan' => 'III',
        'pembelian-pupuk' => 'IV',
        'biaya-umum'      => 'V',
    ];

    public function index(Request $request)
    {
        $query = Pengeluaran::with($this->pengeluaranRelations());

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('search')) {
            $this->applySearch($query, $request->search);
        }

        $pengeluaran = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $tahunList = Pengeluaran::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $kategoriList = KategoriPengeluaran::orderBy('nomor_kategori')->get();

        return view('pengeluaran.index', compact('pengeluaran', 'tahunList', 'kategoriList'));
    }

    public function create()
    {
        $kategoriList = $this->kategoriListWithSub();
        $formContext = $this->activityFormContext();
        $karyawanAktif = $this->karyawanAktif();

        return view('pengeluaran.create', compact('kategoriList', 'formContext', 'karyawanAktif'));
    }

    public function store(PengeluaranRequest $request)
    {
        DB::transaction(function () use ($request) {
            $pengeluaran = Pengeluaran::create($request->pengeluaranData());
            $this->syncDetail($pengeluaran, $request->detailData());
            $this->syncPekerja($pengeluaran, $request->workerDetails());
        });

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Data pengeluaran berhasil ditambahkan.');
    }

    public function show(Pengeluaran $pengeluaran)
    {
        return redirect()->route('pengeluaran.edit', $pengeluaran);
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        $pengeluaran->load($this->pengeluaranRelations(['pekerjaDetail.karyawan']));

        $kategoriList = $this->kategoriListWithSub();
        $formContext = $this->activityFormContext($pengeluaran->sub?->nama_sub, $pengeluaran->kategori, $pengeluaran->sub?->jenis_detail);
        $karyawanAktif = $this->karyawanAktif($pengeluaran);

        return view('pengeluaran.edit', compact('pengeluaran', 'kategoriList', 'formContext', 'karyawanAktif'));
    }

    public function update(PengeluaranRequest $request, Pengeluaran $pengeluaran)
    {
        DB::transaction(function () use ($request, $pengeluaran) {
            $pengeluaran->update($request->pengeluaranData());
            $this->syncDetail($pengeluaran, $request->detailData());
            $this->syncPekerja($pengeluaran, $request->workerDetails());
        });

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Data pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $pengeluaran->delete();

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Data pengeluaran berhasil dihapus.');
    }

    public function kategori(string $slug)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $kat = $this->kategoriViewData($kategori);

        $subList = $kategori->subPengeluaran()
            ->orderBy('nomor_sub')
            ->pluck('nama_sub');

        $totals = Pengeluaran::with('sub')
            ->where('kategori_id', $kategori->id)
            ->selectRaw('sub_id, SUM(jumlah) as total, COUNT(*) as jml_transaksi, MAX(tanggal) as transaksi_terakhir')
            ->groupBy('sub_id')
            ->get()
            ->keyBy(fn ($row) => $row->sub?->nama_sub);

        $totalKategori  = $totals->sum('total');
        $totalTransaksi = $totals->sum('jml_transaksi');

        return view('pengeluaran.kategori', compact(
            'slug',
            'kat',
            'subList',
            'totals',
            'totalKategori',
            'totalTransaksi'
        ));
    }

    public function subIndex(Request $request, string $slug, string $subKategori)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $kat = $this->kategoriViewData($kategori);
        $subKategori = $sub->nama_sub;

        $query = Pengeluaran::with($this->pengeluaranRelations(['pekerjaDetail.karyawan']))
            ->where('kategori_id', $kategori->id)
            ->where('sub_id', $sub->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        $totalAll = (clone $query)->sum('jumlah');
        $summaryRows = (clone $query)->get();
        $totalVolume = $summaryRows->sum(fn ($row) => (float) $row->volume);
        $totalTonase = $summaryRows->sum(fn ($row) => (float) $row->tonase_kg);
        $totalLuas = $summaryRows->sum(fn ($row) => (float) $row->luas_ha);
        $totalBrondolan = $summaryRows->sum(fn ($row) => (float) $row->brondolan_kg);
        $pengeluaran = $query->paginate(20)->withQueryString();

        $tahunList = Pengeluaran::where('kategori_id', $kategori->id)
            ->where('sub_id', $sub->id)
            ->selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $bulanList = [
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

        $formContext = $this->activityFormContext($sub->nama_sub, $kategori, $sub->jenis_detail);

        return view('pengeluaran.sub-index', compact(
            'slug',
            'kat',
            'subKategori',
            'sub',
            'pengeluaran',
            'totalAll',
            'totalVolume',
            'totalTonase',
            'totalLuas',
            'totalBrondolan',
            'tahunList',
            'bulanList',
            'formContext'
        ));
    }

    public function subCreate(string $slug, string $subKategori)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $kat = $this->kategoriViewData($kategori);
        $subKategori = $sub->nama_sub;
        $formContext = $this->activityFormContext($sub->nama_sub, $kategori, $sub->jenis_detail);
        $karyawanAktif = $this->karyawanAktif();

        return view('pengeluaran.sub-create', compact('slug', 'kat', 'subKategori', 'kategori', 'sub', 'formContext', 'karyawanAktif'));
    }

    public function subStore(PengeluaranRequest $request, string $slug, string $subKategori)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $validated = $request->pengeluaranData();

        abort_if((int) $validated['kategori_id'] !== $kategori->id || (int) $validated['sub_id'] !== $sub->id, 422);

        DB::transaction(function () use ($request, $validated) {
            $pengeluaran = Pengeluaran::create($validated);
            $this->syncDetail($pengeluaran, $request->detailData());
            $this->syncPekerja($pengeluaran, $request->workerDetails());
        });

        return redirect()
            ->route('pengeluaran.sub.index', [$slug, urlencode($sub->nama_sub)])
            ->with('success', 'Data transaksi lapangan berhasil ditambahkan.');
    }

    public function subEdit(string $slug, string $subKategori, Pengeluaran $pengeluaran)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $this->abortIfPengeluaranMismatch($pengeluaran, $kategori, $sub);

        $kat = $this->kategoriViewData($kategori);
        $subKategori = $sub->nama_sub;
        $pengeluaran->load($this->pengeluaranRelations(['pekerjaDetail.karyawan']));
        $formContext = $this->activityFormContext($sub->nama_sub, $kategori, $sub->jenis_detail);
        $karyawanAktif = $this->karyawanAktif($pengeluaran);

        return view('pengeluaran.sub-edit', compact('slug', 'kat', 'subKategori', 'kategori', 'sub', 'pengeluaran', 'formContext', 'karyawanAktif'));
    }

    public function subUpdate(PengeluaranRequest $request, string $slug, string $subKategori, Pengeluaran $pengeluaran)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $this->abortIfPengeluaranMismatch($pengeluaran, $kategori, $sub);

        $validated = $request->pengeluaranData();
        abort_if((int) $validated['kategori_id'] !== $kategori->id || (int) $validated['sub_id'] !== $sub->id, 422);

        DB::transaction(function () use ($request, $pengeluaran, $validated) {
            $pengeluaran->update($validated);
            $this->syncDetail($pengeluaran, $request->detailData());
            $this->syncPekerja($pengeluaran, $request->workerDetails());
        });

        return redirect()
            ->route('pengeluaran.sub.index', [$slug, urlencode($sub->nama_sub)])
            ->with('success', 'Data transaksi lapangan berhasil diperbarui.');
    }

    public function subDestroy(string $slug, string $subKategori, Pengeluaran $pengeluaran)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $this->abortIfPengeluaranMismatch($pengeluaran, $kategori, $sub);

        $pengeluaran->delete();

        return redirect()
            ->route('pengeluaran.sub.index', [$slug, urlencode($sub->nama_sub)])
            ->with('success', 'Data transaksi lapangan berhasil dihapus.');
    }

    private function kategoriListWithSub()
    {
        return KategoriPengeluaran::with(['subPengeluaran' => fn ($query) => $query->orderBy('nomor_sub')])
            ->orderBy('nomor_kategori')
            ->get();
    }

    private function pengeluaranRelations(array $extra = []): array
    {
        return array_values(array_unique(array_merge([
            'kategori',
            'sub',
            'pekerjaDetail',
        ], Pengeluaran::detailRelations(), $extra)));
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

    private function karyawanAktif(?Pengeluaran $pengeluaran = null)
    {
        $includeIds = collect();

        if ($pengeluaran) {
            $includeIds = $pengeluaran->pekerjaDetail->pluck('karyawan_id')
                ->filter()
                ->unique()
                ->values();
        }

        return Karyawan::query()
            ->where(function ($query) use ($includeIds) {
                $query->where('status', 'aktif');

                if ($includeIds->isNotEmpty()) {
                    $query->orWhereIn('id', $includeIds);
                }
            })
            ->orderBy('nama')
            ->get();
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

        $pengeluaran->unsetRelation($activeRelation);
    }

    private function kategoriFromSlug(string $slug): KategoriPengeluaran
    {
        abort_unless(isset($this->kategoriSlug[$slug]), 404);

        return KategoriPengeluaran::where('nomor_kategori', $this->kategoriSlug[$slug])->firstOrFail();
    }

    private function subFromRoute(KategoriPengeluaran $kategori, string $subKategori): SubPengeluaran
    {
        return SubPengeluaran::where('kategori_id', $kategori->id)
            ->where('nama_sub', urldecode($subKategori))
            ->firstOrFail();
    }

    private function kategoriViewData(KategoriPengeluaran $kategori): array
    {
        return [
            'nomor' => $kategori->nomor_kategori,
            'nama' => $kategori->nama_kategori,
        ];
    }

    private function abortIfPengeluaranMismatch(Pengeluaran $pengeluaran, KategoriPengeluaran $kategori, SubPengeluaran $sub): void
    {
        abort_if($pengeluaran->kategori_id !== $kategori->id || $pengeluaran->sub_id !== $sub->id, 404);
    }

    private function activityFormContext(?string $subKategori = null, ?KategoriPengeluaran $kategori = null, ?string $jenisDetail = null): array
    {
        $profile = $jenisDetail ?: Pengeluaran::resolveDetailProfile($subKategori, $kategori?->nomor_kategori);
        $title = 'Detail Operasional';
        $metricLabel = 'Volume';
        $metricHint = 'Isi volume kerja, unit barang, ritase, atau jumlah transaksi sesuai kegiatan.';
        $defaultSatuan = '';

        if ($profile === 'panen') {
            $title = 'Aktivitas Panen';
            $metricLabel = 'Basis/Output Panen';
            $metricHint = 'Umumnya dihitung dari tonase TBS, jumlah janjang, brondolan, HK panen, dan tarif panen.';
            $defaultSatuan = 'kg';
        } elseif ($profile === 'angkutan') {
            $title = 'Distribusi dan Angkutan';
            $metricLabel = 'Ritase / Tonase Angkut';
            $metricHint = 'Catat tonase TBS, ritase kendaraan, nomor SPB/nota, dan vendor atau sopir.';
            $defaultSatuan = 'rit';
        } elseif ($profile === 'berondol') {
            $title = 'Kutip Berondol';
            $metricLabel = 'Berondolan Terkutip';
            $metricHint = 'Catat kilogram brondolan, jumlah pekerja, blok, dan tarif kutip.';
            $defaultSatuan = 'kg';
        } elseif ($profile === 'pupuk') {
            $title = 'Pembelian / Distribusi Pupuk';
            $metricLabel = 'Jumlah Pupuk';
            $metricHint = 'Gunakan sak, kg, liter, atau ton sesuai bukti pembelian/distribusi.';
            $defaultSatuan = 'sak';
        } elseif ($profile === 'perawatan') {
            $title = 'Aktivitas Perawatan';
            $metricLabel = 'HK / Luas Kerja';
            $metricHint = 'Catat hektar, HK, pekerja, blok, dan tarif pekerjaan perawatan.';
            $defaultSatuan = 'HK';
        }

        return compact('profile', 'title', 'metricLabel', 'metricHint', 'defaultSatuan');
    }
}
