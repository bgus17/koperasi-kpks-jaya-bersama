{{-- FILE: resources/views/pengeluaran/kategori.blade.php --}}

@extends('layouts.app')

@section('title', $kat['nama'] . ' — Pengeluaran')
@section('page-title', $kat['nama'])

@section('content')

{{-- BREADCRUMB --}}
<div class="breadcrumb">
    <a href="{{ route('pengeluaran.index') }}">Pengeluaran</a>
    <span>›</span>
    <span>{{ $kat['nama'] }}</span>
</div>

{{-- HEADER --}}
<div class="page-header">
    <div style="display:flex;align-items:center;gap:16px;">
        <div class="kategori-badge">{{ $kat['nomor'] }}</div>
        <div>
            <h2>{{ $kat['nama'] }}</h2>
            <p>{{ $subList->count() }} kegiatan tersedia — klik kartu untuk input transaksi harian</p>
        </div>
    </div>
    <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline">← Semua Pengeluaran</a>
</div>

{{-- STAT RINGKASAN --}}
<div class="kategori-stats">
    <div class="kategori-stat">
        <div class="kategori-stat-label">Total Pengeluaran</div>
        <div class="kategori-stat-value merah">
            Rp {{ number_format($totalKategori, 0, ',', '.') }}
        </div>
    </div>
    <div class="kategori-stat">
        <div class="kategori-stat-label">Jumlah Transaksi</div>
        <div class="kategori-stat-value">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
    </div>
    <div class="kategori-stat">
        <div class="kategori-stat-label">Kegiatan Aktif</div>
        <div class="kategori-stat-value">
            {{ $totals->filter(fn($t) => $t->jml_transaksi > 0)->count() }}
            <span style="font-size:14px;color:var(--abu);font-family:'DM Sans',sans-serif;">
                / {{ $subList->count() }}
            </span>
        </div>
    </div>
</div>

<p style="font-size:13px;color:var(--abu);margin-bottom:20px;">
    Pilih kegiatan di bawah untuk melihat riwayat atau menambah transaksi harian:
</p>

{{-- CARD GRID SUB-ITEM --}}
<div class="sub-menu-grid">
    @forelse($subList as $index => $subNama)
        @php
            $info    = $totals->get($subNama);
            $total   = $info?->total ?? 0;
            $jml     = $info?->jml_transaksi ?? 0;
            $last    = $info?->transaksi_terakhir;
            $hasData = $jml > 0;
        @endphp

        <a href="{{ route('pengeluaran.sub.index', [$slug, urlencode($subNama)]) }}"
           class="sub-menu-card {{ $hasData ? 'has-data' : '' }}">

            <div class="sub-card-nomor">{{ $index + 1 }}</div>
            <div class="sub-card-nama">{{ $subNama }}</div>

            @if($hasData)
                <div class="sub-card-total">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </div>
                <div class="sub-card-meta">
                    <span>{{ $jml }} transaksi</span>
                    @if($last)
                        <span>·</span>
                        <span>Terakhir: {{ \Carbon\Carbon::parse($last)->format('d/m/Y') }}</span>
                    @endif
                </div>
            @else
                <div class="sub-card-meta">Belum ada transaksi</div>
            @endif

            <div class="sub-card-arrow">→</div>
        </a>
    @empty
        <p style="color:var(--abu);grid-column:1/-1;">Tidak ada kegiatan untuk kategori ini.</p>
    @endforelse
</div>

@endsection
