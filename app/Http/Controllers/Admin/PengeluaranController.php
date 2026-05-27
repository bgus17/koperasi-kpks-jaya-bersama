<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengeluaranRequest;
use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use App\Models\SubPengeluaran;
use App\Services\ActorAccessService;
use App\Services\PengeluaranService;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function __construct(private PengeluaranService $pengeluaranService) {}

    public function index(Request $request)
    {
        $query = Pengeluaran::with($this->pengeluaranService->relations());

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('search')) {
            $this->pengeluaranService->applySearch($query, $request->search);
        }

        $summaryQuery = clone $query;
        $pengeluaranSummary = [
            'jumlah' => (int) (clone $summaryQuery)->sum('jumlah'),
            'debet' => (int) (clone $summaryQuery)->sum('debet'),
            'kredit' => (int) (clone $summaryQuery)->sum('kredit'),
            'saldo' => (int) (clone $summaryQuery)->sum('saldo'),
            'record' => (int) (clone $summaryQuery)->count(),
        ];

        $pengeluaran = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $tahunList = Pengeluaran::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $kategoriList = KategoriPengeluaran::orderBy('nomor_kategori')->get();

        return view('pengeluaran.index', compact('pengeluaran', 'tahunList', 'kategoriList', 'pengeluaranSummary'));
    }

    public function create()
    {
        $kategoriList = $this->kategoriListWithSub();
        $formContext = $this->activityFormContext();
        $karyawanAktif = $this->pengeluaranService->activeWorkers();

        return view('pengeluaran.create', compact('kategoriList', 'formContext', 'karyawanAktif'));
    }

    public function store(PengeluaranRequest $request)
    {
        $this->pengeluaranService->create(
            $request->pengeluaranData(),
            $request->detailData(),
            $request->workerDetails()
        );

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Data pengeluaran berhasil ditambahkan.');
    }

    public function show(Pengeluaran $pengeluaran)
    {
        return redirect()->route('pengeluaran.edit', $pengeluaran);
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        $pengeluaran->load($this->pengeluaranService->relations(['pekerjaDetail.karyawan']));

        $kategoriList = $this->kategoriListWithSub();
        $formContext = $this->activityFormContext($pengeluaran->sub?->nama_sub, $pengeluaran->kategori, $pengeluaran->sub?->jenis_detail);
        $karyawanAktif = $this->pengeluaranService->activeWorkers($pengeluaran);

        return view('pengeluaran.edit', compact('pengeluaran', 'kategoriList', 'formContext', 'karyawanAktif'));
    }

    public function update(PengeluaranRequest $request, Pengeluaran $pengeluaran)
    {
        $this->pengeluaranService->update(
            $pengeluaran,
            $request->pengeluaranData(),
            $request->detailData(),
            $request->workerDetails()
        );

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
            ->get(['id', 'nama_sub']);

        $totals = Pengeluaran::with('sub')
            ->where('kategori_id', $kategori->id)
            ->selectRaw('sub_id, SUM(jumlah) as total, SUM(debet) as total_debet, SUM(kredit) as total_kredit, SUM(saldo) as total_saldo, COUNT(*) as jml_transaksi, MAX(tanggal) as transaksi_terakhir')
            ->groupBy('sub_id')
            ->get()
            ->keyBy('sub_id');

        $totalKategori = $totals->sum('total_kredit');
        $totalDebetKategori = $totals->sum('total_debet');
        $totalMutasiSaldo = $totals->sum('total_saldo');
        $totalTransaksi = $totals->sum('jml_transaksi');

        return view('pengeluaran.kategori', compact(
            'slug',
            'kat',
            'subList',
            'totals',
            'totalKategori',
            'totalDebetKategori',
            'totalMutasiSaldo',
            'totalTransaksi'
        ));
    }

    public function subIndex(Request $request, string $slug, string $subKategori)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $kat = $this->kategoriViewData($kategori);
        $subKategori = $sub->nama_sub;

        $query = Pengeluaran::with($this->pengeluaranService->relations(['pekerjaDetail.karyawan']))
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
        $totalDebet = (clone $query)->sum('debet');
        $totalKredit = (clone $query)->sum('kredit');
        $totalSaldo = (clone $query)->sum('saldo');
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
            'totalDebet',
            'totalKredit',
            'totalSaldo',
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
        $karyawanAktif = $this->pengeluaranService->activeWorkers();

        return view('pengeluaran.sub-create', compact('slug', 'kat', 'subKategori', 'kategori', 'sub', 'formContext', 'karyawanAktif'));
    }

    public function subStore(PengeluaranRequest $request, string $slug, string $subKategori)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $validated = $request->pengeluaranData();

        abort_if((int) $validated['kategori_id'] !== $kategori->id || (int) $validated['sub_id'] !== $sub->id, 422);

        $this->pengeluaranService->create(
            $validated,
            $request->detailData(),
            $request->workerDetails()
        );

        return redirect()
            ->route('pengeluaran.sub.index', [$slug, $sub->id])
            ->with('success', 'Data transaksi lapangan berhasil ditambahkan.');
    }

    public function subEdit(string $slug, string $subKategori, Pengeluaran $pengeluaran)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $this->abortIfPengeluaranMismatch($pengeluaran, $kategori, $sub);

        $kat = $this->kategoriViewData($kategori);
        $subKategori = $sub->nama_sub;
        $pengeluaran->load($this->pengeluaranService->relations(['pekerjaDetail.karyawan']));
        $formContext = $this->activityFormContext($sub->nama_sub, $kategori, $sub->jenis_detail);
        $karyawanAktif = $this->pengeluaranService->activeWorkers($pengeluaran);

        return view('pengeluaran.sub-edit', compact('slug', 'kat', 'subKategori', 'kategori', 'sub', 'pengeluaran', 'formContext', 'karyawanAktif'));
    }

    public function subUpdate(PengeluaranRequest $request, string $slug, string $subKategori, Pengeluaran $pengeluaran)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $this->abortIfPengeluaranMismatch($pengeluaran, $kategori, $sub);

        $validated = $request->pengeluaranData();
        abort_if((int) $validated['kategori_id'] !== $kategori->id || (int) $validated['sub_id'] !== $sub->id, 422);

        $this->pengeluaranService->update(
            $pengeluaran,
            $validated,
            $request->detailData(),
            $request->workerDetails()
        );

        return redirect()
            ->route('pengeluaran.sub.index', [$slug, $sub->id])
            ->with('success', 'Data transaksi lapangan berhasil diperbarui.');
    }

    public function subDestroy(string $slug, string $subKategori, Pengeluaran $pengeluaran)
    {
        $kategori = $this->kategoriFromSlug($slug);
        $sub = $this->subFromRoute($kategori, $subKategori);
        $this->abortIfPengeluaranMismatch($pengeluaran, $kategori, $sub);

        $pengeluaran->delete();

        return redirect()
            ->route('pengeluaran.sub.index', [$slug, $sub->id])
            ->with('success', 'Data transaksi lapangan berhasil dihapus.');
    }

    private function kategoriListWithSub()
    {
        return KategoriPengeluaran::with(['subPengeluaran' => fn ($query) => $query->orderBy('nomor_sub')])
            ->orderBy('nomor_kategori')
            ->get();
    }

    private function kategoriFromSlug(string $slug): KategoriPengeluaran
    {
        $categoryNumber = ActorAccessService::categoryNumberForSlug($slug);

        abort_unless($categoryNumber, 404);

        return KategoriPengeluaran::where('nomor_kategori', $categoryNumber)->firstOrFail();
    }

    private function subFromRoute(KategoriPengeluaran $kategori, string $subKategori): SubPengeluaran
    {
        if (ctype_digit($subKategori)) {
            return SubPengeluaran::where('kategori_id', $kategori->id)
                ->whereKey((int) $subKategori)
                ->firstOrFail();
        }

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
            $title = 'Pembelian / Distribusi Pupuk & Racun';
            $metricLabel = 'Jumlah Pupuk/Racun';
            $metricHint = 'Gunakan sak, kg, liter, atau ton sesuai bukti pembelian/distribusi pupuk atau racun.';
            $defaultSatuan = 'sak';
        } elseif ($profile === 'alat_berat') {
            $title = 'Pemakaian Alat Berat';
            $metricLabel = 'Jam Kerja / HM';
            $metricHint = 'Catat HM/jam kerja alat, lokasi blok, vendor atau operator, dan biaya per jam atau rit sesuai pekerjaan kebun.';
            $defaultSatuan = 'HM';
        } elseif ($profile === 'perlengkapan') {
            $title = 'Perlengkapan Operasional Kebun';
            $metricLabel = 'Jumlah Perlengkapan';
            $metricHint = 'Catat jumlah unit, set, meter, atau paket barang, supplier, nota, dan lokasi penggunaan perlengkapan kebun.';
            $defaultSatuan = 'unit';
        } elseif ($profile === 'insentive') {
            $title = 'Insentive Operasional';
            $metricLabel = 'Jumlah Penerima';
            $metricHint = 'Catat periode, penerima atau kelompok penerima, jumlah orang, dan nilai insentive sesuai peran di kebun.';
            $defaultSatuan = 'orang';
        } elseif ($profile === 'perawatan') {
            $title = 'Aktivitas Perawatan';
            $metricLabel = 'HK / Luas Kerja';
            $metricHint = 'Catat hektar, HK, pekerja, blok, dan tarif pekerjaan perawatan.';
            $defaultSatuan = 'HK';
        }

        return compact('profile', 'title', 'metricLabel', 'metricHint', 'defaultSatuan');
    }
}
