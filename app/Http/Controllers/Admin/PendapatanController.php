<?php
// app/Http/Controllers/Admin/PendapatanController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PendapatanRequest;
use App\Models\Pendapatan;
use Illuminate\Http\Request;

class PendapatanController extends Controller
{
    // Daftar kategori yang diizinkan untuk dropdown
    private $allowedCategories = [
        'Saldo Per 31 Des',
        'Pendapatan Diterima',
        'Penjualan Barang',
    ];

    public function index(Request $request)
    {
        $query = Pendapatan::query();

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('keterangan', 'like', "%{$search}%");
        }

        // Hitung total dari SEMUA data di database (tanpa filter)
        $totalDebetAll = Pendapatan::sum('debet');
        $totalKreditAll = Pendapatan::sum('kredit');
        $totalSaldoAll = Pendapatan::latest('id')->first()?->saldo ?? 0;

        $pendapatan = $query->orderBy('nomor_kategori')->orderBy('id')->paginate(20)->withQueryString();

        $tahunList = Pendapatan::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        
        // Hanya tampilkan 3 kategori yang diizinkan di filter
        $kategoriList = collect($this->allowedCategories);

        return view('pendapatan.index', compact('pendapatan', 'tahunList', 'kategoriList', 'totalDebetAll', 'totalKreditAll', 'totalSaldoAll'));
    }

    public function create()
    {
        // Hanya 3 kategori yang diizinkan
        $kategoriList = collect($this->allowedCategories);

        return view('pendapatan.create', compact('kategoriList'));
    }

    public function store(PendapatanRequest $request)
    {
        $validated = $request->validated();

        $validated['nomor_kategori'] = 'I';
        $validated['tahun'] = date('Y', strtotime($validated['tanggal']));
        $validated['sub_kategori'] = $validated['kategori'];

        Pendapatan::create($validated);

        return redirect()->route('pendapatan.index')
            ->with('success', 'Data pendapatan berhasil ditambahkan.');
    }

    public function show(Pendapatan $pendapatan)
    {
        return view('pendapatan.show', compact('pendapatan'));
    }

    public function edit(Pendapatan $pendapatan)
    {
        // Untuk edit, tetap pakai data yang sudah ada
        $kategoriList = collect($this->allowedCategories);

        return view('pendapatan.edit', compact('pendapatan', 'kategoriList'));
    }

    public function update(PendapatanRequest $request, Pendapatan $pendapatan)
    {
        $pendapatan->update($request->validated());

        return redirect()->route('pendapatan.index')
            ->with('success', 'Data pendapatan berhasil diperbarui.');
    }

    public function destroy(Pendapatan $pendapatan)
    {
        $pendapatan->delete();

        return redirect()->route('pendapatan.index')
            ->with('success', 'Data pendapatan berhasil dihapus.');
    }
}