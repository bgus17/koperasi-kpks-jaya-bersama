{{-- resources/views/rekap/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Rekap Keuangan ' . $tahun)
@section('page-title', 'Rekap Keuangan')

@section('content')

{{-- HEADER + FILTER TAHUN --}}
<div class="page-header">
    <div>
        <h2>Rekap Keuangan</h2>
        <p>
            @if($rekap)
                Dana Kebun Tahun {{ $rekap->tahun }} — Lokasi: {{ $rekap->lokasi }}
                &nbsp;|&nbsp; Ditutup: {{ $rekap->tanggal_tutup?->format('d M Y') }}
            @else
                Laporan keuangan per tahun buku.
            @endif
        </p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        {{-- Pilih tahun --}}
        <form method="GET" action="{{ route('rekap.index') }}" style="display:flex;gap:8px;align-items:center;">
            <select name="tahun" onchange="this.form.submit()"
                    style="padding:8px 12px;border-radius:8px;border:1.5px solid var(--krem-drk);font-family:inherit;font-size:13px;color:var(--hijau);">
                @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                @endforeach
            </select>
        </form>

        {{-- Hitung ulang --}}
        <form method="POST" action="{{ route('rekap.hitung') }}">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <button type="submit" class="btn btn-outline btn-sm"
                    onclick="return confirm('Hitung ulang rekap dari data aktual?')">
                🔄 Hitung Ulang
            </button>
        </form>

        <a href="{{ route('rekap.create') }}" class="btn btn-primary btn-sm">+ Rekap Baru</a>
    </div>
</div>

{{-- GRAND TOTAL CARDS --}}
@if($rekap)
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card" style="border-top:3px solid var(--hijau-lt);">
        <div class="stat-label">Grand Total Debet</div>
        <div class="stat-value rp" style="font-size:18px;">Rp {{ number_format($rekap->grand_total_debet, 0, ',', '.') }}</div>
        <div class="stat-icon">📈</div>
    </div>
    <div class="stat-card" style="border-top:3px solid #fca5a5;">
        <div class="stat-label">Grand Total Kredit</div>
        <div class="stat-value kredit rp" style="font-size:18px;">Rp {{ number_format($rekap->grand_total_kredit, 0, ',', '.') }}</div>
        <div class="stat-icon">📉</div>
    </div>
    <div class="stat-card" style="border-top:3px solid var(--emas);background:var(--hijau);">
        <div class="stat-label" style="color:rgba(255,255,255,.6);">Saldo Akhir</div>
        <div class="stat-value rp" style="color:var(--emas-lt);font-size:18px;">Rp {{ number_format($rekap->saldo_akhir, 0, ',', '.') }}</div>
        <div class="stat-icon" style="color:var(--emas-lt);">💰</div>
    </div>
</div>

{{-- PENGURUS --}}
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-title">Pengurus Koperasi {{ $tahun }}</span>
        <a href="{{ route('rekap.edit', $rekap) }}" class="btn btn-outline btn-sm">Edit</a>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
            @foreach([
                ['label' => 'Ketua Pengurus',       'value' => $rekap->ketua_pengurus],
                ['label' => 'Sekretaris',            'value' => $rekap->sekretaris],
                ['label' => 'Bendahara',             'value' => $rekap->bendahara],
                ['label' => 'Ketua Badan Pengawas',  'value' => $rekap->ketua_badan_pengawas],
                ['label' => 'Lokasi',                'value' => $rekap->lokasi],
            ] as $item)
            <div>
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--abu);margin-bottom:4px;">{{ $item['label'] }}</div>
                <div style="font-family:'DM Serif Display',serif;font-size:15px;color:var(--hijau);">{{ $item['value'] ?? '—' }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@else
<div style="background:var(--krem);border:1px solid var(--krem-drk);border-radius:10px;padding:24px;text-align:center;margin-bottom:24px;color:var(--abu);">
    Belum ada data rekap untuk tahun {{ $tahun }}.
    <a href="{{ route('rekap.create') }}" style="color:var(--hijau-mid);font-weight:500;"> Buat sekarang →</a>
</div>
@endif

{{--
    2 KOLOM: PENDAPATAN & PENGELUARAN
    Class .rekap-two-col memiliki responsive rule di app.css (@media ≤900px → 1 kolom)
--}}
<div class="rekap-two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    {{-- RINGKASAN PENDAPATAN --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Pendapatan per Kategori</span>
            <a href="{{ route('pendapatan.index', ['tahun' => $tahun]) }}" style="font-size:12px;color:var(--hijau-mid);text-decoration:none;">Lihat detail →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th class="text-right">Debet</th>
                        <th class="text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendapatanPerKategori as $row)
                    <tr>
                        <td><span class="badge-nomkat">{{ $row->nomor_kategori }}</span></td>
                        <td style="font-size:12px;">{{ $row->kategori }}</td>
                        <td class="text-right rp" style="font-size:12px;color:var(--hijau-mid);">
                            {{ $row->total_debet > 0 ? number_format($row->total_debet, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right rp" style="font-size:12px;color:var(--merah);">
                            {{ $row->total_kredit > 0 ? number_format($row->total_kredit, 0, ',', '.') : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center" style="padding:20px;color:var(--abu);font-size:13px;">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- RINGKASAN PENGELUARAN --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Pengeluaran per Kategori</span>
            <a href="{{ route('pengeluaran.index', ['tahun' => $tahun]) }}" style="font-size:12px;color:var(--hijau-mid);text-decoration:none;">Lihat detail →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th class="text-right">Debet</th>
                        <th class="text-right">Kredit</th>
                        <th class="text-right">Mutasi Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengeluaranPerKategori as $row)
                    <tr>
                        <td><span class="badge-nomkat" style="background:var(--emas);color:var(--hijau);">{{ $row->nomor_kategori }}</span></td>
                        <td style="font-size:12px;">{{ $row->kategori }}</td>
                        <td class="text-right rp" style="font-size:12px;color:var(--hijau-mid);">
                            {{ $row->total_debet > 0 ? number_format($row->total_debet, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right rp" style="font-size:12px;color:var(--merah);">
                            {{ $row->total_kredit > 0 ? number_format($row->total_kredit, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right rp {{ $row->total_saldo < 0 ? 'rp-kredit' : 'rp-saldo' }}" style="font-size:12px;">
                            {{ $row->total_saldo > 0 ? '+' : '' }}{{ number_format($row->total_saldo, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center" style="padding:20px;color:var(--abu);font-size:13px;">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- TABEL LENGKAP PENDAPATAN --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">Detail Dana Kebun — Pendapatan {{ $tahun }}</span>
        <span style="font-size:12px;color:var(--abu);">{{ $pendapatanDetail->count() }} baris</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Sub Kategori</th>
                    <th class="text-right">Debet (Rp)</th>
                    <th class="text-right">Kredit (Rp)</th>
                    <th class="text-right">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php $prevKat = ''; @endphp
                @foreach($pendapatanDetail as $row)
                    @if($row->nomor_kategori !== $prevKat)
                        @php $prevKat = $row->nomor_kategori; @endphp
                        <tr class="kategori-row">
                            <td colspan="6" style="padding:8px 14px;font-size:12px;color:var(--hijau);">
                                <span class="badge-nomkat">{{ $row->nomor_kategori }}</span>
                                &nbsp;{{ $row->kategori }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding-left:24px;color:var(--abu);font-size:12px;">{{ $row->nomor_kategori }}</td>
                        <td style="font-size:12px;color:var(--abu);"></td>
                        <td>{{ $row->sub_kategori }}</td>
                        <td class="text-right rp {{ $row->debet > 0 ? 'rp-debet' : '' }}" style="font-size:13px;">
                            {{ $row->debet > 0 ? number_format($row->debet, 0, ',', '.') : '' }}
                        </td>
                        <td class="text-right rp {{ $row->kredit > 0 ? 'rp-kredit' : '' }}" style="font-size:13px;">
                            {{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '' }}
                        </td>
                        <td class="text-right rp rp-saldo" style="font-size:13px;">{{ number_format($row->saldo, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            @if($rekap)
            <tfoot>
                <tr style="background:var(--hijau);color:var(--putih);">
                    <td colspan="3" style="padding:11px 14px;font-weight:600;font-family:'DM Serif Display',serif;">GRAND TOTAL TERINTEGRASI</td>
                    <td class="text-right rp" style="padding:11px 14px;font-weight:600;color:var(--emas-lt);">{{ number_format($rekap->grand_total_debet, 0, ',', '.') }}</td>
                    <td class="text-right rp" style="padding:11px 14px;font-weight:600;color:#f28b82;">{{ number_format($rekap->grand_total_kredit, 0, ',', '.') }}</td>
                    <td class="text-right rp" style="padding:11px 14px;font-weight:600;color:var(--emas-lt);">{{ number_format($rekap->saldo_akhir, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- TABEL LENGKAP PENGELUARAN --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Detail Pengeluaran & Biaya {{ $tahun }}</span>
        <span style="font-size:12px;color:var(--abu);">{{ $pengeluaranDetail->count() }} baris</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Kegiatan</th>
                    <th>Lapangan / Output</th>
                    <th class="text-right">Jumlah (Rp)</th>
                    <th class="text-right">Debet</th>
                    <th class="text-right">Kredit</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php $prevKat2 = ''; @endphp
                @foreach($pengeluaranDetail as $row)
                    @if($row->kategori?->nomor_kategori !== $prevKat2)
                        @php $prevKat2 = $row->kategori?->nomor_kategori; @endphp
                        <tr class="kategori-row">
                            <td colspan="8" style="padding:8px 14px;font-size:12px;color:var(--hijau);">
                                <span class="badge-nomkat" style="background:var(--emas);color:var(--hijau);">{{ $row->kategori?->nomor_kategori }}</span>
                                &nbsp;{{ $row->kategori?->nama_kategori }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding-left:24px;color:var(--abu);font-size:12px;">{{ $row->kategori?->nomor_kategori }}</td>
                        <td></td>
                        <td>{{ $row->sub?->nama_sub }}</td>
                        <td style="font-size:12px;color:var(--abu);">
                            @if($row->blok)<div>Blok: {{ $row->blok }}</div>@endif
                            @if($row->mandor)<div>Mandor: {{ $row->mandor }}</div>@endif
                            @if($row->tonase_kg)<div>TBS: {{ number_format($row->tonase_kg, 0, ',', '.') }} kg</div>@endif
                            @if($row->brondolan_kg)<div>Brondolan: {{ number_format($row->brondolan_kg, 0, ',', '.') }} kg</div>@endif
                            @if($row->luas_ha)<div>Luas: {{ number_format($row->luas_ha, 2, ',', '.') }} ha</div>@endif
                            @if($row->volume)<div>Volume: {{ number_format($row->volume, 2, ',', '.') }} {{ $row->satuan }}</div>@endif
                            @if($row->supplier_vendor)<div>{{ $row->supplier_vendor }}</div>@endif
                            @if($row->no_referensi)<div>Ref: {{ $row->no_referensi }}</div>@endif
                            @if(!$row->blok && !$row->mandor && !$row->tonase_kg && !$row->brondolan_kg && !$row->luas_ha && !$row->volume && !$row->supplier_vendor && !$row->no_referensi)
                                -
                            @endif
                        </td>
                        <td class="text-right rp {{ $row->jumlah > 0 ? 'rp-kredit' : '' }}" style="font-size:13px;">
                            {{ $row->jumlah > 0 ? number_format($row->jumlah, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right rp {{ $row->debet > 0 ? 'rp-debet' : '' }}" style="font-size:13px;">
                            {{ $row->debet > 0 ? number_format($row->debet, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right rp {{ $row->kredit > 0 ? 'rp-kredit' : '' }}" style="font-size:13px;">
                            {{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right rp {{ $row->saldo < 0 ? 'rp-kredit' : 'rp-saldo' }}" style="font-size:13px;">
                            {{ $row->saldo > 0 ? '+' : '' }}{{ number_format($row->saldo, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            @if($rekap)
            <tfoot>
                <tr style="background:var(--hijau);color:var(--putih);">
                    <td colspan="4" style="padding:11px 14px;font-weight:600;font-family:'DM Serif Display',serif;">TOTAL PENGELUARAN</td>
                    <td class="text-right rp" style="padding:11px 14px;font-weight:600;color:#f28b82;">{{ number_format($ledgerSummary['pengeluaran_jumlah'], 0, ',', '.') }}</td>
                    <td class="text-right rp" style="padding:11px 14px;font-weight:600;color:var(--emas-lt);">{{ number_format($ledgerSummary['pengeluaran_debet'], 0, ',', '.') }}</td>
                    <td class="text-right rp" style="padding:11px 14px;font-weight:600;color:#f28b82;">{{ number_format($ledgerSummary['pengeluaran_kredit'], 0, ',', '.') }}</td>
                    <td class="text-right rp" style="padding:11px 14px;font-weight:600;color:var(--emas-lt);">{{ number_format($ledgerSummary['pengeluaran_saldo'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
