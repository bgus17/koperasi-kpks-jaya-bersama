{{-- FILE: resources/views/karyawan/show.blade.php --}}

@extends('layouts.app')
@section('title', $karyawan->nama)
@section('page-title', 'Detail Karyawan')

@section('content')

@php
    // Semua logika PHP dikumpulkan di atas, tidak ditulis di dalam atribut HTML.
    $statusClass = 'badge-status badge-status--' . ($karyawan->status ?? 'nonaktif');
    $statusLabel = $karyawan->status_label ?? ucfirst($karyawan->status ?? '—');
    $namaEscaped = e($karyawan->nama);
@endphp

<div class="breadcrumb">
    <a href="{{ route('karyawan.index') }}">Karyawan</a>
    <span>›</span>
    <span>{{ $karyawan->nama }}</span>
</div>

<div class="page-header">
    <div>
        <h2>{{ $karyawan->nama }}</h2>
        <p>Data master pekerja/mandor untuk aktivitas lapangan.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('karyawan.edit', $karyawan) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('karyawan.index') }}" class="btn btn-outline">← Kembali</a>
    </div>
</div>

<div class="card" style="max-width:760px;margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">Informasi Karyawan</span>

        {{--
            FIX: Ganti style="--badge-color:{{ $karyawan->status_color }}"
            dengan class dinamis — warna diatur di app.css.
        --}}
        <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
    </div>
    <div class="card-body">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <tr style="border-bottom:1px solid var(--krem-drk);">
                <td style="padding:10px 0;color:var(--abu);width:180px;">Nama Lengkap</td>
                <td style="padding:10px 0;font-weight:600;">{{ $karyawan->nama }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--krem-drk);">
                <td style="padding:10px 0;color:var(--abu);">Jenis Kelamin</td>
                <td style="padding:10px 0;">{{ $karyawan->jenis_kelamin_label }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--krem-drk);">
                <td style="padding:10px 0;color:var(--abu);">No. HP / WhatsApp</td>
                <td style="padding:10px 0;">
                    @if($karyawan->no_hp)
                        <a href="tel:{{ $karyawan->no_hp }}" class="link-hp">
                            {{ $karyawan->no_hp }}
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
            <tr style="border-bottom:1px solid var(--krem-drk);">
                <td style="padding:10px 0;color:var(--abu);">Tanggal Masuk</td>
                <td style="padding:10px 0;">
                    @if($karyawan->tanggal_masuk)
                        {{ $karyawan->tanggal_masuk->format('d F Y') }}
                        <span class="text-muted text-sm">
                            ({{ $karyawan->tanggal_masuk->diffForHumans() }})
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
            <tr style="border-bottom:1px solid var(--krem-drk);">
                <td style="padding:10px 0;color:var(--abu);">Alamat</td>
                <td style="padding:10px 0;">{{ $karyawan->alamat ?: '—' }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:var(--abu);">Keterangan</td>
                <td style="padding:10px 0;font-size:13px;color:var(--abu);">
                    {{ $karyawan->keterangan ?: '—' }}
                </td>
            </tr>
        </table>
    </div>
</div>

<div style="display:flex;gap:10px;">
    <a href="{{ route('karyawan.edit', $karyawan) }}" class="btn btn-primary">Edit Data</a>

    {{--
        FIX: Ganti onsubmit="return confirm('... {{ addslashes($karyawan->nama) }} ...')"
        dengan data-nama + event listener di @push('scripts').
    --}}
    <form method="POST"
          action="{{ route('karyawan.destroy', $karyawan) }}"
          id="form-hapus"
          data-nama="{{ $namaEscaped }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Hapus</button>
    </form>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('form-hapus').addEventListener('submit', function (e) {
        var nama = this.dataset.nama || 'karyawan ini';
        if (!confirm('Yakin ingin menghapus karyawan ' + nama + '?')) {
            e.preventDefault();
        }
    });
</script>
@endpush