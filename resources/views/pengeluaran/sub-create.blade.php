{{-- FILE: resources/views/pengeluaran/sub-create.blade.php --}}

@extends('layouts.app')
@section('title', 'Tambah — ' . $subKategori)
@section('page-title', 'Tambah Transaksi')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('pengeluaran.index') }}">Pengeluaran</a>
    <span>›</span>
    <a href="{{ route('pengeluaran.kategori', $slug) }}">{{ $kat['nama'] }}</a>
    <span>›</span>
    <a href="{{ route('pengeluaran.sub.index', [$slug, urlencode($subKategori)]) }}">{{ $subKategori }}</a>
    <span>›</span>
    <span>Tambah</span>
</div>

<div class="page-header">
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
            <span class="badge-nomkat">{{ $kat['nomor'] }}</span>
            <h2 style="font-size:22px;">Tambah Transaksi</h2>
        </div>
        <p>{{ $subKategori }} — {{ $kat['nama'] }}</p>
    </div>
    <a href="{{ route('pengeluaran.sub.index', [$slug, urlencode($subKategori)]) }}"
       class="btn btn-outline">← Kembali</a>
</div>

<div class="card" style="max-width:1100px;">
    <div class="card-body" style="padding:28px;">
        <form method="POST" action="{{ route('pengeluaran.sub.store', [$slug, urlencode($subKategori)]) }}">
            @csrf
            @include('pengeluaran._sub-form', [
                'p' => null,
                'kategori' => $kategori,
                'sub' => $sub,
                'formContext' => $formContext,
                'karyawanAktif' => $karyawanAktif,
            ])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('pengeluaran.sub.index', [$slug, urlencode($subKategori)]) }}"
                   class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
