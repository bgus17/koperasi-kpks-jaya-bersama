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
        <div class="kategori-stat-label">Total Kredit Pengeluaran</div>
        <div class="kategori-stat-value merah">
            Rp {{ number_format($totalKategori, 0, ',', '.') }}
        </div>
    </div>
    <div class="kategori-stat">
        <div class="kategori-stat-label">Total Debet Pengeluaran</div>
        <div class="kategori-stat-value">
            Rp {{ number_format($totalDebetKategori, 0, ',', '.') }}
        </div>
    </div>
    <div class="kategori-stat">
        <div class="kategori-stat-label">Mutasi Saldo</div>
        <div class="kategori-stat-value {{ $totalMutasiSaldo < 0 ? 'merah' : '' }}">
            Rp {{ number_format($totalMutasiSaldo, 0, ',', '.') }}
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
@php
    $alatTulisKantor = ['Kertas,Pena,Tinta dll', 'Perlengkapan Kantor'];

    if ($slug === 'pembelian-pupuk') {
        $subGroups = collect([
            ['title' => 'Pembelian Pupuk', 'items' => $subList->filter(fn ($item) => str_starts_with($item->nama_sub, 'Pupuk'))->values()],
            ['title' => 'Pembelian Racun', 'items' => $subList->reject(fn ($item) => str_starts_with($item->nama_sub, 'Pupuk'))->values()],
        ]);
    } elseif ($slug === 'perlengkapan') {
        $subGroups = collect([
            ['title' => 'Perlengkapan', 'items' => $subList->reject(fn ($item) => in_array($item->nama_sub, $alatTulisKantor, true))->values()],
            ['title' => 'Alat Tulis Kantor', 'items' => $subList->filter(fn ($item) => in_array($item->nama_sub, $alatTulisKantor, true))->values()],
        ]);
    } else {
        $subGroups = collect([
            ['title' => null, 'items' => $subList->values()],
        ]);
    }
@endphp

@if($subList->isEmpty())
    <div class="sub-menu-grid">
        <p style="color:var(--abu);grid-column:1/-1;">Tidak ada kegiatan untuk kategori ini.</p>
    </div>
@else
    @foreach($subGroups as $group)
        @continue($group['items']->isEmpty())

        <section class="sub-menu-section">
            @if($group['title'])
                <div class="sub-menu-section-title">
                    <span>{{ $group['title'] }}</span>
                    <small>{{ $group['items']->count() }} opsi</small>
                </div>
            @endif

            <div class="sub-menu-grid {{ $group['title'] ? 'sub-menu-grid--sectioned' : '' }}">
                @foreach($group['items'] as $index => $subItem)
                    @php
                        $subNama = $subItem->nama_sub;
                        $info    = $totals->get($subItem->id);
                        $debet   = $info?->total_debet ?? 0;
                        $kredit  = $info?->total_kredit ?? 0;
                        $saldo   = $info?->total_saldo ?? 0;
                        $total   = $kredit ?: ($debet ?: abs($saldo));
                        $totalLabel = $kredit > 0 ? 'Kredit' : ($debet > 0 ? 'Debet' : 'Saldo');
                        $jml     = $info?->jml_transaksi ?? 0;
                        $last    = $info?->transaksi_terakhir;
                        $hasData = $jml > 0;
                    @endphp

                    <a href="{{ route('pengeluaran.sub.index', [$slug, $subItem->id]) }}"
                       class="sub-menu-card {{ $hasData ? 'has-data' : '' }}">

                        <div class="sub-card-nomor">{{ $index + 1 }}</div>
                        <div class="sub-card-nama">{{ $subNama }}</div>

                        @if($hasData)
                            <div class="sub-card-total">
                                {{ $totalLabel }} Rp {{ number_format($total, 0, ',', '.') }}
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
                @endforeach
            </div>
        </section>
    @endforeach
@endif

@endsection
