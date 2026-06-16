@extends('layouts.app')

@section('title', 'Dashboard Keuangan')
@section('page-title', 'Dashboard')

@section('content')
@php
    $money = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
    $number = fn ($value) => number_format((int) $value, 0, ',', '.');
    $percent = fn ($value) => $value === null ? '-' : number_format((float) $value, 1, ',', '.') . '%';
@endphp

<div class="page-header">
    <div>
        <h2>Dashboard Keuangan</h2>
        <p>Ikhtisar kondisi kas, pendapatan, biaya operasional, dan transaksi terbaru untuk {{ $periodeLabel }}.</p>
    </div>
    <div class="dashboard-actions">
        <a href="{{ route('pendapatan.create') }}" class="btn btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            Pendapatan
        </a>
        <a href="{{ route('pengeluaran.create') }}" class="btn btn-outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            Pengeluaran
        </a>
    </div>
</div>

<div class="card dashboard-filter-card">
    <div class="card-body">
        <form method="GET" action="{{ route('dashboard') }}" class="filter-bar">
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
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline">Reset</a>
        </form>
    </div>
</div>

<section class="dashboard-section">
    <div class="dashboard-section-head">
        <div>
            <span class="dashboard-eyebrow">Ringkasan</span>
            <h3>Ringkasan Finansial Utama</h3>
        </div>
        <a href="{{ route('rekap.index', array_filter(['tahun' => $tahun, 'bulan' => $bulan])) }}" class="btn btn-outline btn-sm">Lihat Rekap</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Saldo Akhir</div>
            <div class="stat-value {{ $summary['saldo_akhir'] < 0 ? 'kredit' : 'saldo' }} rp">{{ $money($summary['saldo_akhir']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Debet</div>
            <div class="stat-value rp">{{ $money($summary['total_debet']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Kredit</div>
            <div class="stat-value kredit rp">{{ $money($summary['total_kredit']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pendapatan Neto</div>
            <div class="stat-value saldo rp">{{ $money($pendapatanNeto) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Biaya Operasional</div>
            <div class="stat-value kredit rp">{{ $money($biayaOperasional) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Transaksi Aktual</div>
            <div class="stat-value">{{ $number($summary['jumlah_record']) }}</div>
        </div>
    </div>
</section>

<section class="dashboard-section">
    <div class="dashboard-section-head">
        <div>
            <span class="dashboard-eyebrow">Operasional</span>
            <h3>Analisis Biaya Operasional</h3>
        </div>
    </div>

    <div class="dashboard-grid-2">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Biaya per Kategori</span>
                <span class="dashboard-card-note">{{ $periodeLabel }}</span>
            </div>
            <div class="card-body">
                <div class="dashboard-list">
                    @forelse($biayaPerKategori as $row)
                        <a href="{{ $row['url'] }}" class="dashboard-list-row">
                            <div class="dashboard-list-main">
                                <strong>{{ $row['kategori'] }}</strong>
                                <span>Kategori {{ $row['nomor'] }} · {{ $percent($row['persen']) }} dari biaya</span>
                                <div class="dashboard-progress">
                                    <span style="width: {{ max(3, $row['persen']) }}%;"></span>
                                </div>
                            </div>
                            <div class="dashboard-list-value rp">{{ $money($row['total']) }}</div>
                        </a>
                    @empty
                        <div class="dashboard-empty">Belum ada biaya operasional pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">Rasio & Kontrol</span>
            </div>
            <div class="card-body">
                <div class="dashboard-ratio-grid">
                    <div class="dashboard-ratio">
                        <span>Rasio Biaya</span>
                        <strong>{{ $percent($rasioBiaya) }}</strong>
                        <small>Biaya operasional dibanding pendapatan neto</small>
                    </div>
                    <div class="dashboard-ratio">
                        <span>Margin Saldo</span>
                        <strong>{{ $percent($marginSaldo) }}</strong>
                        <small>Saldo akhir dibanding pendapatan neto</small>
                    </div>
                    <div class="dashboard-ratio">
                        <span>Rata-rata Biaya</span>
                        <strong class="rp">{{ $money($rataRataBiaya) }}</strong>
                        <small>Per transaksi pengeluaran</small>
                    </div>
                    <div class="dashboard-ratio">
                        <span>Belum Dibayar</span>
                        <strong class="rp">{{ $money($tagihanOperasional['total']) }}</strong>
                        <small>{{ $number($tagihanOperasional['jumlah']) }} transaksi perlu ditinjau</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="dashboard-section dashboard-grid-2">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Tren Bulanan Tahun {{ $tahun }}</span>
            <span class="dashboard-legend"><i></i> Pendapatan <b></b> Biaya</span>
        </div>
        <div class="card-body">
            <div class="dashboard-trend">
                @foreach($trenBulanan as $row)
                    <div class="dashboard-trend-item">
                        <div class="dashboard-bars" title="{{ $row['bulan'] }}">
                            <span class="dashboard-bar dashboard-bar-income" style="height: {{ max(4, $row['pendapatan_persen']) }}%;"></span>
                            <span class="dashboard-bar dashboard-bar-expense" style="height: {{ max(4, $row['pengeluaran_persen']) }}%;"></span>
                        </div>
                        <small>{{ $row['bulan'] }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Sumber Pendapatan</span>
        </div>
        <div class="card-body">
            <div class="dashboard-list compact">
                @forelse($komposisiPendapatan as $row)
                    <div class="dashboard-list-row">
                        <div class="dashboard-list-main">
                            <strong>{{ $row['label'] }}</strong>
                            <span>{{ $percent($row['persen']) }} dari pendapatan</span>
                            <div class="dashboard-progress dashboard-progress-income">
                                <span style="width: {{ max(3, $row['persen']) }}%;"></span>
                            </div>
                        </div>
                        <div class="dashboard-list-value rp">{{ $money($row['total']) }}</div>
                    </div>
                @empty
                    <div class="dashboard-empty">Belum ada pendapatan pada periode ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="dashboard-section dashboard-grid-2">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Biaya Terbesar</span>
            <a href="{{ route('pengeluaran.index', array_filter(['tahun' => $tahun])) }}" class="btn btn-outline btn-sm">Semua Biaya</a>
        </div>
        <div class="card-body">
            <div class="dashboard-list compact">
                @forelse($topBiayaOperasional as $row)
                    <div class="dashboard-list-row">
                        <div class="dashboard-list-main">
                            <strong>{{ $row['nama'] }}</strong>
                            <span>{{ $row['kategori'] }} · {{ $number($row['transaksi']) }} transaksi</span>
                        </div>
                        <div class="dashboard-list-value rp">{{ $money($row['total']) }}</div>
                    </div>
                @empty
                    <div class="dashboard-empty">Belum ada rincian biaya operasional.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Transaksi Terbaru</span>
        </div>
        <div class="card-body">
            <div class="dashboard-activity">
                @forelse($transaksiTerbaru as $row)
                    <div class="dashboard-activity-row">
                        <span class="dashboard-activity-dot {{ $row['arah'] }}"></span>
                        <div>
                            <strong>{{ $row['label'] }}</strong>
                            <small>{{ $row['jenis'] }} · {{ $row['tanggal']?->format('d/m/Y') ?: '-' }}</small>
                            @if($row['keterangan'])
                                <em>{{ $row['keterangan'] }}</em>
                            @endif
                        </div>
                        <b class="rp {{ $row['arah'] === 'keluar' ? 'rp-kredit' : 'rp-saldo' }}">{{ $money($row['nominal']) }}</b>
                    </div>
                @empty
                    <div class="dashboard-empty">Belum ada transaksi terbaru pada periode ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
