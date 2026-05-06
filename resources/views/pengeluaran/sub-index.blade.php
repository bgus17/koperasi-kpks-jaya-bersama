{{-- FILE: resources/views/pengeluaran/sub-index.blade.php --}}

@extends('layouts.app')

@section('title', $subKategori)
@section('page-title', $subKategori)

@section('content')

<div class="breadcrumb">
    <a href="{{ route('pengeluaran.index') }}">Pengeluaran</a>
    <span>›</span>
    <a href="{{ route('pengeluaran.kategori', $slug) }}">{{ $kat['nama'] }}</a>
    <span>›</span>
    <span>{{ $subKategori }}</span>
</div>

<div class="page-header">
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
            <span class="badge-nomkat">{{ $kat['nomor'] }}</span>
            <h2 style="font-size:20px;">{{ $subKategori }}</h2>
        </div>
        <p>Transaksi harian — {{ $kat['nama'] }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('pengeluaran.kategori', $slug) }}" class="btn btn-outline">← Kembali</a>
        <a href="{{ route('pengeluaran.sub.create', [$slug, urlencode($subKategori)]) }}"
           class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Transaksi
        </a>
    </div>
</div>

{{-- FILTER --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 20px;">
        <form method="GET"
              action="{{ route('pengeluaran.sub.index', [$slug, urlencode($subKategori)]) }}"
              class="filter-bar">
            <div class="form-group" style="min-width:130px;max-width:160px;">
                <label>Tahun</label>
                <select name="tahun">
                    <option value="">Semua</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="min-width:150px;max-width:180px;">
                <label>Bulan</label>
                <select name="bulan">
                    <option value="">Semua</option>
                    @foreach($bulanList as $no => $nm)
                        <option value="{{ $no }}" {{ request('bulan') == $no ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="min-width:140px;max-width:170px;">
                <label>Dari Tanggal</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="form-group" style="min-width:140px;max-width:170px;">
                <label>Sampai</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:1px;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('pengeluaran.sub.index', [$slug, urlencode($subKategori)]) }}"
                   class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- STAT --}}
@php
    $profile = $formContext['profile'] ?? 'umum';
    if (in_array($profile, ['panen', 'angkutan'])) {
        $outputLabel = 'Total Tonase TBS';
        $outputValue = number_format($totalTonase, 0, ',', '.') . ' kg';
    } elseif ($profile === 'berondol') {
        $outputLabel = 'Total Brondolan';
        $outputValue = number_format($totalBrondolan, 0, ',', '.') . ' kg';
    } elseif ($profile === 'perawatan') {
        $outputLabel = 'Total Luas Kerja';
        $outputValue = number_format($totalLuas, 2, ',', '.') . ' ha';
    } else {
        $outputLabel = 'Total Volume';
        $outputValue = number_format($totalVolume, 2, ',', '.');
    }
@endphp

<div class="stats-grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
    <div class="stat-card">
        <div class="stat-label">Total Pengeluaran</div>
        <div class="stat-value kredit rp">Rp {{ number_format($totalAll, 0, ',', '.') }}</div>
        <div class="stat-icon">📉</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Jumlah Transaksi</div>
        <div class="stat-value">{{ $pengeluaran->total() }}</div>
        <div class="stat-icon">📋</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Rata-rata / Transaksi</div>
        <div class="stat-value rp" style="font-size:1rem;">
            Rp {{ $pengeluaran->total() > 0
                    ? number_format($totalAll / $pengeluaran->total(), 0, ',', '.')
                    : '0' }}
        </div>
        <div class="stat-icon">📊</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ $outputLabel }}</div>
        <div class="stat-value" style="font-size:1rem;">{{ $outputValue }}</div>
        <div class="stat-icon">▦</div>
    </div>
</div>

{{-- TABEL --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Daftar Transaksi</span>
        <span style="font-size:13px;color:var(--abu);">
            {{ $pengeluaran->firstItem() }}–{{ $pengeluaran->lastItem() }}
            dari {{ $pengeluaran->total() }} data
        </span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width:100px;">Tanggal</th>
                    <th>Lapangan</th>
                    <th>Output / Volume</th>
                    <th class="text-right">Biaya (Rp)</th>
                    <th class="text-center">Status</th>
                    <th>Keterangan</th>
                    <th class="text-center" style="width:130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengeluaran as $row)
                    <tr>
                        <td class="text-center" style="font-size:12.5px;">
                            {{ $row->tanggal->format('d/m/Y') }}<br>
                            <small style="color:var(--abu);">
                                {{ $row->tanggal->locale('id')->isoFormat('ddd') }}
                            </small>
                        </td>
                        <td style="font-size:12.5px;">
                            <div><strong>{{ $row->blok ?: 'Lokasi belum diisi' }}</strong></div>
                            @if($row->mandor)
                                <div style="color:var(--abu);">Mandor: {{ $row->mandor }}</div>
                            @endif
                            @if($row->jumlah_pekerja)
                                <div style="color:var(--abu);">{{ $row->jumlah_pekerja }} pekerja/HK</div>
                            @endif
                            @if($row->pekerjaDetail->isNotEmpty())
                                <div style="color:var(--abu);">
                                    {{ $row->pekerjaDetail->take(3)->pluck('nama_karyawan_snapshot')->join(', ') }}{{ $row->pekerjaDetail->count() > 3 ? ', ...' : '' }}
                                </div>
                            @endif
                            @if($row->supplier_vendor)
                                <div style="color:var(--abu);">{{ $row->supplier_vendor }}</div>
                            @endif
                        </td>
                        <td style="font-size:12.5px;color:var(--abu);">
                            @if($row->tonase_kg)
                                <div>TBS: {{ number_format($row->tonase_kg, 0, ',', '.') }} kg</div>
                            @endif
                            @if($row->jumlah_janjang)
                                <div>Janjang: {{ number_format($row->jumlah_janjang, 0, ',', '.') }}</div>
                            @endif
                            @if($row->brondolan_kg)
                                <div>Brondolan: {{ number_format($row->brondolan_kg, 0, ',', '.') }} kg</div>
                            @endif
                            @if($row->luas_ha)
                                <div>Luas: {{ number_format($row->luas_ha, 2, ',', '.') }} ha</div>
                            @endif
                            @if($row->volume)
                                <div>Volume: {{ number_format($row->volume, 2, ',', '.') }} {{ $row->satuan }}</div>
                            @endif
                            @if($row->harga_satuan)
                                <div>Tarif: Rp {{ number_format($row->harga_satuan, 0, ',', '.') }}</div>
                            @endif
                            @if($row->no_referensi)
                                <div>Ref: {{ $row->no_referensi }}</div>
                            @endif
                            @if($row->pekerjaDetail->isNotEmpty())
                                <div style="margin-top:6px;border-top:1px solid var(--krem-drk);padding-top:5px;">
                                    @foreach($row->pekerjaDetail->take(4) as $detail)
                                        <div>
                                            {{ $detail->nama_karyawan_snapshot }}:
                                            Rp {{ number_format($detail->upah, 0, ',', '.') }}
                                        </div>
                                    @endforeach
                                    @if($row->pekerjaDetail->count() > 4)
                                        <div>+{{ $row->pekerjaDetail->count() - 4 }} pekerja lain</div>
                                    @endif
                                </div>
                            @endif
                            @if(!$row->tonase_kg && !$row->jumlah_janjang && !$row->brondolan_kg && !$row->luas_ha && !$row->volume && !$row->no_referensi && $row->pekerjaDetail->isEmpty())
                                -
                            @endif
                        </td>
                        <td class="text-right rp rp-kredit" style="font-weight:600;font-size:14px;">
                            {{ number_format($row->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="badge-status {{ $row->sudah_bayar ? 'status-paid' : 'status-unpaid' }}">
                                {{ $row->sudah_bayar ? 'Lunas' : 'Belum' }}
                            </span>
                        </td>
                        <td style="font-size:12.5px;color:var(--abu);max-width:260px;">
                            {{ $row->keterangan ?: '—' }}
                        </td>
                        <td class="text-center">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <a href="{{ route('pengeluaran.sub.edit', [$slug, urlencode($subKategori), $row]) }}"
                                   class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('pengeluaran.sub.destroy', [$slug, urlencode($subKategori), $row]) }}"
                                      onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center"
                            style="padding:48px;color:var(--abu);font-style:italic;">
                            Belum ada transaksi untuk kegiatan ini.<br>
                            <a href="{{ route('pengeluaran.sub.create', [$slug, urlencode($subKategori)]) }}"
                               style="color:var(--hijau-mid);text-decoration:none;font-weight:500;font-style:normal;">
                                + Tambah sekarang
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengeluaran->hasPages())
        <div style="padding:12px 20px;border-top:1px solid var(--krem-drk);">
            {{ $pengeluaran->links() }}
        </div>
    @endif
</div>

@endsection
