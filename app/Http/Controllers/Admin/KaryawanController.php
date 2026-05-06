<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $karyawan = $query->orderBy('nama')->paginate(20)->withQueryString();

        $totalAktif = Karyawan::where('status', 'aktif')->count();
        $totalNonaktif = Karyawan::where('status', 'nonaktif')->count();
        $totalCuti = Karyawan::where('status', 'cuti')->count();
        $totalSemua = Karyawan::count();

        return view('karyawan.index', compact(
            'karyawan',
            'totalAktif',
            'totalNonaktif',
            'totalCuti',
            'totalSemua'
        ));
    }

    public function create()
    {
        return view('karyawan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Karyawan::create($validated);

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function show(Karyawan $karyawan)
    {
        return view('karyawan.show', compact('karyawan'));
    }

    public function edit(Karyawan $karyawan)
    {
        return view('karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $validated = $request->validate($this->rules());

        $karyawan->update($validated);

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'nama'          => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'tanggal_masuk' => 'nullable|date',
            'status'        => 'required|in:aktif,nonaktif,cuti',
            'keterangan'    => 'nullable|string|max:500',
        ];
    }
}
