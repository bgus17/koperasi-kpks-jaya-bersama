<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rekap;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    /**
     * Tampilkan halaman rekap keuangan lengkap per tahun.
     */
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);

        // Rekap grand total dari tabel rekap
        $rekap = Rekap::where('tahun', $tahun)->first();

        // Ringkasan pendapatan per kategori
        $pendapatanPerKategori = Pendapatan::where('tahun', $tahun)
            ->select('nomor_kategori', 'kategori',
                DB::raw('SUM(debet) as total_debet'),
                DB::raw('SUM(kredit) as total_kredit')
            )
            ->groupBy('nomor_kategori', 'kategori')
            ->orderBy('nomor_kategori')
            ->get();

        // Ringkasan pengeluaran per kategori
        $pengeluaranPerKategori = DB::table('pengeluaran')
            ->whereYear('tanggal', $tahun)
            ->join('kategori_pengeluaran as kp', 'pengeluaran.kategori_id', '=', 'kp.id')
            ->select('kp.nomor_kategori', 'kp.nama_kategori as kategori',
                DB::raw('SUM(jumlah) as total_jumlah')
            )
            ->groupBy('kp.nomor_kategori', 'kp.nama_kategori')
            ->orderBy('kp.nomor_kategori')
            ->get();

        // Semua data pendapatan detail untuk tabel lengkap
        $pendapatanDetail = Pendapatan::where('tahun', $tahun)
            ->orderBy('nomor_kategori')
            ->orderBy('id')
            ->get();

        // Semua data pengeluaran detail untuk tabel lengkap
        $pengeluaranDetail = Pengeluaran::with(array_merge(['kategori', 'sub'], Pengeluaran::detailRelations()))
            ->whereYear('tanggal', $tahun)
            ->join('kategori_pengeluaran as kp', 'pengeluaran.kategori_id', '=', 'kp.id')
            ->join('sub_pengeluaran as sp', 'pengeluaran.sub_id', '=', 'sp.id')
            ->select('pengeluaran.*')
            ->orderBy('kp.nomor_kategori')
            ->orderBy('sp.nomor_sub')
            ->orderBy('pengeluaran.id')
            ->get();

        // Daftar tahun yang tersedia
        $tahunList = Rekap::select('tahun')->orderBy('tahun', 'desc')->pluck('tahun');

        return view('rekap.index', compact(
            'rekap',
            'tahun',
            'tahunList',
            'pendapatanPerKategori',
            'pengeluaranPerKategori',
            'pendapatanDetail',
            'pengeluaranDetail'
        ));
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

        $totalDebet  = Pendapatan::where('tahun', $tahun)->sum('debet');
        $totalKredit = Pendapatan::where('tahun', $tahun)->sum('kredit');
        $totalBiaya  = Pengeluaran::whereYear('tanggal', $tahun)->sum('jumlah');

        $grandTotalKredit = $totalKredit + $totalBiaya;
        $saldoAkhir       = $totalDebet - $grandTotalKredit;

        Rekap::updateOrCreate(
            ['tahun' => $tahun],
            [
                'tanggal_tutup'      => now()->setYear($tahun)->endOfYear()->toDateString(),
                'grand_total_debet'  => $totalDebet,
                'grand_total_kredit' => $grandTotalKredit,
                'saldo_akhir'        => max(0, $saldoAkhir),
            ]
        );

        return redirect()->route('rekap.index', ['tahun' => $tahun])
            ->with('success', "Rekap tahun {$tahun} berhasil dihitung ulang.");
    }
}
