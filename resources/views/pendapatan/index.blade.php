{{-- resources/views/pendapatan/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Dana Kebun — Pendapatan')
@section('page-title', 'Dana Kebun & Pendapatan')

@section('content')

<div class="page-header">
    <div>
        <h2>Dana Kebun (Pendapatan)</h2>
        <p>Catatan debet, kredit, dan saldo per kategori tahun buku.</p>
    </div>
    <a href="{{ route('pendapatan.create') }}" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Data
    </a>
</div>

{{-- FILTER --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 20px;">
        <form method="GET" action="{{ route('pendapatan.index') }}" class="filter-bar">
            <div class="form-group">
                <label>Tahun</label>
                <select name="tahun">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k }}" {{ request('kategori') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="max-width:280px;">
                <label>Cari Keterangan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik keterangan...">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:1px;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('pendapatan.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- RINGKASAN --}}
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-label">Total Debet (Semua Data)</div>
        <div class="stat-value rp">Rp {{ number_format($totalDebetAll, 0, ',', '.') }}</div>
        <div class="stat-icon">📈</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Kredit (Semua Data)</div>
        <div class="stat-value kredit rp">Rp {{ number_format($totalKreditAll, 0, ',', '.') }}</div>
        <div class="stat-icon">📉</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Saldo (Semua Data)</div>
        <div class="stat-value saldo rp">Rp {{ number_format($totalSaldoAll, 0, ',', '.') }}</div>
        <div class="stat-icon">💰</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Jumlah Record (Database)</div>
        <div class="stat-value">{{ $jumlahRecordAll }}</div>
        <div class="stat-icon">📋</div>
    </div>
</div>

{{-- TABEL --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Daftar Pendapatan</span>
        <span style="font-size:13px;color:var(--abu);">{{ $pendapatan->firstItem() }}–{{ $pendapatan->lastItem() }} dari {{ $pendapatan->total() }} data</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No. Kat</th>
                    <th>Kategori</th>
                    <th class="text-right">Debet (Rp)</th>
                    <th class="text-right">Kredit (Rp)</th>
                    <th class="text-right">Saldo (Rp)</th>
                    <th class="text-center">Tahun</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center" style="width:120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendapatan as $row)
                    <tr>
                        <td><span class="badge-nomkat">{{ $row->nomor_kategori }}</span></td>
                        <td style="max-width:220px;">{{ $row->kategori }}</td>
                        <td class="text-right rp {{ $row->debet > 0 ? 'rp-debet' : '' }}">
                            {{ $row->debet > 0 ? number_format($row->debet, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right rp {{ $row->kredit > 0 ? 'rp-kredit' : '' }}">
                            {{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right rp rp-saldo">{{ number_format($row->saldo, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row->tahun }}</td>
                        <td class="text-center" style="font-size:12px;">{{ $row->tanggal?->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <a href="{{ route('pendapatan.edit', $row) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST" action="{{ route('pendapatan.destroy', $row) }}"
                                      onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding:40px;color:var(--abu);font-style:italic;">
                            Tidak ada data pendapatan ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pendapatan->hasPages())
        <div style="padding:12px 20px; border-top:1px solid var(--krem-drk);">
            {{ $pendapatan->links() }}
        </div>
    @endif
</div>

@endsection
