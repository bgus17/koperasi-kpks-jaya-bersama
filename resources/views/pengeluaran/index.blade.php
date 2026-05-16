@extends('layouts.app')

@section('title', 'Pengeluaran & Biaya')
@section('page-title', 'Pengeluaran & Biaya')

@section('content')

<div class="page-header">
    <div>
        <h2>Pengeluaran & Biaya</h2>
    </div>
</div>

{{-- FILTER --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 20px;">
        <form method="GET" action="{{ route('pengeluaran.index') }}" class="filter-bar">
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
                <select name="kategori_id">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nomor_kategori }} - {{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="max-width:280px;">
                <label>Cari Kegiatan / Pekerja</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kegiatan, mandor, pekerja...">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:1px;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- RINGKASAN --}}
@php
    $summary = $pengeluaranSummary ?? [
        'jumlah' => $pengeluaran->sum('jumlah'),
        'debet' => $pengeluaran->sum('debet'),
        'kredit' => $pengeluaran->sum('kredit'),
        'saldo' => $pengeluaran->sum('saldo'),
        'record' => $pengeluaran->total(),
    ];
@endphp

<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-label">Total Kredit Pengeluaran</div>
        <div class="stat-value kredit rp">Rp {{ number_format($summary['kredit'], 0, ',', '.') }}</div>
        <div class="stat-icon">📤</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Debet Pengeluaran</div>
        <div class="stat-value rp">Rp {{ number_format($summary['debet'], 0, ',', '.') }}</div>
        <div class="stat-icon">📥</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Mutasi Saldo</div>
        <div class="stat-value {{ $summary['saldo'] < 0 ? 'kredit' : 'saldo' }} rp">
            Rp {{ number_format($summary['saldo'], 0, ',', '.') }}
        </div>
        <div class="stat-icon">💰</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Jumlah Record</div>
        <div class="stat-value">{{ $summary['record'] }}</div>
        <div class="stat-icon">📋</div>
    </div>
</div>

{{-- TABEL --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Daftar Pengeluaran</span>
        <span style="font-size:13px;color:var(--abu);">{{ $pengeluaran->firstItem() }}–{{ $pengeluaran->lastItem() }} dari {{ $pengeluaran->total() }} data</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No. Kat</th>
                    <th>Kategori</th>
                    <th>Kegiatan</th>
                    <th>Lapangan</th>
                    <th>Output</th>
                    <th class="text-right">Jumlah (Rp)</th>
                    <th>Transaksi Kas</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Tahun</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center" style="width:120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengeluaran as $row)
                    <tr>
                        <td><span class="badge-nomkat" style="background:var(--emas);color:var(--hijau);">{{ $row->kategori?->nomor_kategori }}</span></td>
                        <td style="font-size:12px;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $row->kategori?->nama_kategori }}</td>
                        <td style="max-width:180px;">{{ $row->sub?->nama_sub }}</td>
                        <td style="font-size:12px;color:var(--abu);max-width:190px;">
                            <div>{{ $row->blok ?: '-' }}</div>
                            @if($row->mandor)<div>Mandor: {{ $row->mandor }}</div>@endif
                            @if($row->pekerjaDetail->isNotEmpty())
                                <div>{{ $row->pekerjaDetail->count() }} pekerja:
                                    {{ $row->pekerjaDetail->take(3)->pluck('nama_karyawan_snapshot')->join(', ') }}{{ $row->pekerjaDetail->count() > 3 ? ', ...' : '' }}
                                </div>
                            @endif
                            @if($row->supplier_vendor)<div>{{ $row->supplier_vendor }}</div>@endif
                        </td>
                        <td style="font-size:12px;color:var(--abu);max-width:190px;">
                            @if($row->tonase_kg)
                                <div>TBS {{ number_format($row->tonase_kg, 0, ',', '.') }} kg</div>
                            @endif
                            @if($row->brondolan_kg)
                                <div>Brondolan {{ number_format($row->brondolan_kg, 0, ',', '.') }} kg</div>
                            @endif
                            @if($row->luas_ha)
                                <div>{{ number_format($row->luas_ha, 2, ',', '.') }} ha</div>
                            @endif
                            @if($row->volume)
                                <div>{{ number_format($row->volume, 2, ',', '.') }} {{ $row->satuan }}</div>
                            @endif
                            @if(!$row->tonase_kg && !$row->brondolan_kg && !$row->luas_ha && !$row->volume)
                                -
                            @endif
                        </td>
                        <td class="text-right rp {{ $row->jumlah > 0 ? 'rp-kredit' : '' }}">
                            {{ $row->jumlah > 0 ? number_format($row->jumlah, 0, ',', '.') : '—' }}
                        </td>
                        <td style="font-size:12px;color:var(--abu);white-space:nowrap;">
                            <span class="badge-status" style="background:var(--krem);color:var(--hijau);">
                                {{ $row->jenis_transaksi_label }}
                            </span>
                            <div class="rp {{ $row->debet > 0 ? 'rp-debet' : '' }}">
                                Debet: {{ $row->debet > 0 ? number_format($row->debet, 0, ',', '.') : '—' }}
                            </div>
                            <div class="rp {{ $row->kredit > 0 ? 'rp-kredit' : '' }}">
                                Kredit: {{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '—' }}
                            </div>
                            <div class="rp {{ $row->saldo < 0 ? 'rp-kredit' : 'rp-saldo' }}">
                                Saldo: {{ $row->saldo > 0 ? '+' : '' }}{{ number_format($row->saldo, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge-status {{ $row->sudah_bayar ? 'status-paid' : 'status-unpaid' }}">
                                {{ $row->sudah_bayar ? 'Lunas' : 'Belum' }}
                            </span>
                        </td>
                        <td class="text-center">{{ $row->tahun }}</td>
                        <td class="text-center" style="font-size:12px;">{{ $row->tanggal?->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <a href="{{ route('pengeluaran.edit', $row) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST" action="{{ route('pengeluaran.destroy', $row) }}"
                                      onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center" style="padding:40px;color:var(--abu);font-style:italic;">
                            Tidak ada data pengeluaran ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengeluaran->hasPages())
        <div style="padding:12px 20px; border-top:1px solid var(--krem-drk);">
            {{ $pengeluaran->links() }}
        </div>
    @endif
</div>

@endsection
