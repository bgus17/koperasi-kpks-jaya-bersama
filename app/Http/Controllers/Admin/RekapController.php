<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapKeuanganExport;
use App\Http\Controllers\Controller;
use App\Models\Rekap;
use App\Services\KeuanganLedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RekapController extends Controller
{
    /**
     * Tampilkan halaman rekap keuangan realtime per tahun atau bulan.
     */
    public function index(Request $request)
    {
        return view('rekap.index', $this->reportContext($request));
    }

    public function exportPdf(Request $request)
    {
        $context = $this->reportContext($request);
        $pdf = Pdf::loadView('rekap.pdf', $context)
            ->setPaper('a4', 'portrait');

        return $pdf->download($this->exportFileName($context, 'pdf'));
    }

    public function exportExcel(Request $request)
    {
        $context = $this->reportContext($request);

        return Excel::download(
            new RekapKeuanganExport($context),
            $this->exportFileName($context, 'xlsx')
        );
    }

    /**
     * Form tambah rekap baru.
     */
    public function create()
    {
        return view('rekap.create');
    }

    /**
     * Simpan rekap baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun'                => 'required|integer|min:2000|max:2100',
            'tanggal_tutup'        => 'required|date',
            'grand_total_debet'    => 'required|integer|min:0',
            'grand_total_kredit'   => 'required|integer|min:0',
            'saldo_akhir'          => 'required|integer|min:0',
            'ketua_pengurus'       => 'nullable|string|max:100',
            'sekretaris'           => 'nullable|string|max:100',
            'bendahara'            => 'nullable|string|max:100',
            'ketua_badan_pengawas' => 'nullable|string|max:100',
            'lokasi'               => 'nullable|string|max:100',
        ]);

        Rekap::create($validated);

        return redirect()->route('rekap.index')
            ->with('success', 'Data rekap berhasil ditambahkan.');
    }

    /**
     * Form edit rekap.
     */
    public function edit(Rekap $rekap)
    {
        return view('rekap.edit', compact('rekap'));
    }

    /**
     * Update data rekap.
     */
    public function update(Request $request, Rekap $rekap)
    {
        $validated = $request->validate([
            'tahun'                => 'required|integer|min:2000|max:2100',
            'tanggal_tutup'        => 'required|date',
            'grand_total_debet'    => 'required|integer|min:0',
            'grand_total_kredit'   => 'required|integer|min:0',
            'saldo_akhir'          => 'required|integer|min:0',
            'ketua_pengurus'       => 'nullable|string|max:100',
            'sekretaris'           => 'nullable|string|max:100',
            'bendahara'            => 'nullable|string|max:100',
            'ketua_badan_pengawas' => 'nullable|string|max:100',
            'lokasi'               => 'nullable|string|max:100',
        ]);

        $rekap->update($validated);

        return redirect()->route('rekap.index')
            ->with('success', 'Data rekap berhasil diperbarui.');
    }

    /**
     * Hapus rekap.
     */
    public function destroy(Rekap $rekap)
    {
        $rekap->delete();

        return redirect()->route('rekap.index')
            ->with('success', 'Data rekap berhasil dihapus.');
    }

    /**
     * Hitung ulang rekap otomatis dari data pendapatan & pengeluaran.
     */
    public function hitung(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);

        $ledgerSummary = KeuanganLedgerService::summary((int) $tahun);

        Rekap::updateOrCreate(
            ['tahun' => $tahun],
            [
                'tanggal_tutup'      => now()->setYear($tahun)->endOfYear()->toDateString(),
                'grand_total_debet'  => $ledgerSummary['total_debet'],
                'grand_total_kredit' => $ledgerSummary['total_kredit'],
                'saldo_akhir'        => max(0, $ledgerSummary['saldo_akhir']),
            ]
        );

        return redirect()->route('rekap.index', ['tahun' => $tahun])
            ->with('success', "Rekap tahun {$tahun} berhasil dihitung ulang.");
    }

    private function reportContext(Request $request): array
    {
        [$tahun, $bulan] = $this->periodInput($request);

        $laporan = KeuanganLedgerService::laporan($tahun, $bulan);
        $ledgerSummary = $laporan['summary'];
        $rekap = Rekap::where('tahun', $tahun)->first();
        $tahunList = KeuanganLedgerService::tahunList();
        $bulanList = KeuanganLedgerService::bulanList();
        $exportQuery = array_filter([
            'tahun' => $tahun,
            'bulan' => $bulan,
        ], fn ($value) => $value !== null && $value !== '');

        return compact(
            'rekap',
            'tahun',
            'bulan',
            'tahunList',
            'bulanList',
            'laporan',
            'ledgerSummary',
            'exportQuery'
        );
    }

    private function periodInput(Request $request): array
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $tahun = $tahun >= 2000 && $tahun <= 2100 ? $tahun : now()->year;
        $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : null;
        $bulan = $bulan >= 1 && $bulan <= 12 ? $bulan : null;

        return [$tahun, $bulan];
    }

    private function exportFileName(array $context, string $extension): string
    {
        $periode = $context['laporan']['periode'];
        $suffix = $periode['bulan']
            ? $periode['tahun'] . '-' . str_pad((string) $periode['bulan'], 2, '0', STR_PAD_LEFT)
            : (string) $periode['tahun'];

        return "rekap-keuangan-{$suffix}.{$extension}";
    }
}
