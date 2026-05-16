{{-- resources/views/pengeluaran/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Pengeluaran')
@section('page-title', 'Edit Data Pengeluaran')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('pengeluaran.index') }}">Pengeluaran</a>
    <span>/</span>
    <span>Edit #{{ $pengeluaran->id }}</span>
</div>

<div class="page-header">
    <div>
        <h2>Edit Data Pengeluaran</h2>
        <p>Mengubah transaksi: <strong>{{ $pengeluaran->sub?->nama_sub }}</strong></p>
    </div>
    <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline">← Kembali</a>
</div>

<div style="background:var(--krem);border:1px solid var(--krem-drk);border-left:3px solid var(--emas);
            border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--hijau);">
    <strong>{{ $pengeluaran->kategori?->nomor_kategori }} — {{ $pengeluaran->kategori?->nama_kategori }}</strong><br>
    <span style="color:var(--abu);">
        Jumlah saat ini: <strong>Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}</strong>
        &nbsp;|&nbsp;
        Transaksi: <strong>{{ $pengeluaran->jenis_transaksi_label }}</strong>
        &nbsp;|&nbsp;
        Tanggal: <strong>{{ $pengeluaran->tanggal->format('d M Y') }}</strong>
    </span>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Form Edit Pengeluaran</span>
        <span style="font-size:12px;color:var(--abu);">ID: {{ $pengeluaran->id }}</span>
    </div>
    <div class="card-body">

        <form method="POST" action="{{ route('pengeluaran.update', $pengeluaran) }}" id="form-edit">
            @csrf @method('PUT')

            @include('pengeluaran._sub-form', [
                'p' => $pengeluaran,
                'kategoriList' => $kategoriList,
                'formContext' => $formContext,
                'karyawanAktif' => $karyawanAktif,
            ])

            <div class="form-actions">
                <button type="submit" class="btn btn-gold">Simpan Perubahan</button>
                <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline">Batal</a>
                <button type="button"
                        onclick="document.getElementById('form-hapus').submit()"
                        class="btn btn-danger btn-delete">
                    Hapus
                </button>
            </div>
        </form>

        <form id="form-hapus"
              method="POST"
              action="{{ route('pengeluaran.destroy', $pengeluaran) }}"
              onsubmit="return confirm('Yakin ingin menghapus data ini?')"
              style="display:none;">
            @csrf @method('DELETE')
        </form>

    </div>
</div>

@endsection
