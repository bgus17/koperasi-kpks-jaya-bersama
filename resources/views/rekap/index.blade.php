{{-- resources/views/rekap/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Rekap Keuangan ' . $laporan['periode']['label'])
@section('page-title', 'Rekap Keuangan')

@section('content')
@php
    $money = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
@endphp

<div class="page-header">
    <div>
        <h2>{{ $laporan['periode']['judul'] }}</h2>
        <p>{{ $laporan['periode']['subjudul'] }}. Data dihitung langsung dari pendapatan dan pengeluaran terbaru.</p>
    </div>
    <div class="rekap-actions">
        <a href="{{ route('rekap.export.pdf', $exportQuery) }}" class="btn btn-outline btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <path d="M14 2v6h6M8 13h8M8 17h5"/>
            </svg>
            PDF
        </a>
        <a href="{{ route('rekap.export.excel', $exportQuery) }}" class="btn btn-outline btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <path d="M7 10l5 5 5-5M12 15V3"/>
            </svg>
            Excel
        </a>
        @if($rekap)
            <a href="{{ route('rekap.edit', $rekap) }}" class="btn btn-outline btn-sm">Edit Pengurus</a>
        @else
            <a href="{{ route('rekap.create') }}" class="btn btn-primary btn-sm">Data Pengurus</a>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <div class="filter-bar">
            <form method="GET" action="{{ route('rekap.index') }}" class="rekap-filter-form">
                <div class="form-group">
                    <label for="tahun">Tahun Buku</label>
                    <select id="tahun" name="tahun">
                        @foreach($tahunList as $item)
                            <option value="{{ $item }}" @selected($tahun === (int) $item)>Tahun {{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="bulan">Periode</label>
                    <select id="bulan" name="bulan">
                        <option value="">Tahunan</option>
                        @foreach($bulanList as $value => $label)
                            <option value="{{ $value }}" @selected($bulan === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 4h18M6 12h12M10 20h4"/>
                    </svg>
                    Terapkan
                </button>
            </form>
            <form method="POST" action="{{ route('rekap.hitung') }}">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <button type="submit" class="btn btn-outline" onclick="return confirm('Sinkronkan ringkasan tahunan dari data aktual?')">
                    Sinkronkan Tahunan
                </button>
            </form>
        </div>
    </div>
</div>

<div class="stats-grid" style="margin-bottom:18px;">
    <div class="stat-card">
        <div class="stat-label">Grand Total Debet</div>
        <div class="stat-value rp">{{ $money($ledgerSummary['total_debet']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Grand Total Kredit</div>
        <div class="stat-value kredit rp">{{ $money($ledgerSummary['total_kredit']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Saldo Akhir</div>
        <div class="stat-value saldo rp">{{ $money($ledgerSummary['saldo_akhir']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Transaksi Aktual</div>
        <div class="stat-value rp">{{ number_format($ledgerSummary['jumlah_record'], 0, ',', '.') }}</div>
    </div>
</div>

<div class="ledger-shell">
    <div class="ledger-head">
        <div>
            <h3>{{ strtoupper($laporan['periode']['judul']) }}</h3>
            <p>{{ strtoupper($laporan['periode']['subjudul']) }}</p>
        </div>
        <div class="ledger-period">
            {{ $bulan ? $bulanList[$bulan] . ' ' . $tahun : 'Tahunan' }}
        </div>
    </div>

    @include('rekap.partials.ledger-table', ['laporan' => $laporan])

    <div class="ledger-signatures">
        <div>
            <strong>Badan Pengawas</strong>
            <span>{{ $rekap->ketua_badan_pengawas ?? 'Ketua' }}</span>
        </div>
        <div>
            <strong>{{ $rekap->lokasi ?? 'Cahaya Mulya' }}, {{ $laporan['periode']['tanggal_tutup']->translatedFormat('d F Y') }}</strong>
            <span>Pengurus</span>
            <span>{{ $rekap->ketua_pengurus ?? 'Ketua' }}</span>
            <span>{{ $rekap->sekretaris ?? 'Sekretaris' }}</span>
            <span>{{ $rekap->bendahara ?? 'Bendahara' }}</span>
        </div>
    </div>
</div>

@endsection
