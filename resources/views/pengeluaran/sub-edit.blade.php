{{-- FILE: resources/views/pengeluaran/sub-edit.blade.php --}}

@extends('layouts.app')
@section('title', 'Edit — ' . $subKategori)
@section('page-title', 'Edit Transaksi')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('pengeluaran.index') }}">Pengeluaran</a>
    <span>›</span>
    <a href="{{ route('pengeluaran.kategori', $slug) }}">{{ $kat['nama'] }}</a>
    <span>›</span>
    <a href="{{ route('pengeluaran.sub.index', [$slug, $sub->id]) }}">{{ $subKategori }}</a>
    <span>›</span>
    <span>Edit</span>
</div>

<div class="page-header">
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
            <span class="badge-nomkat">{{ $kat['nomor'] }}</span>
            <h2 style="font-size:22px;">Edit Transaksi</h2>
        </div>
        <p>{{ $subKategori }} — {{ $kat['nama'] }}</p>
    </div>
    <a href="{{ route('pengeluaran.sub.index', [$slug, $sub->id]) }}"
       class="btn btn-outline">← Kembali</a>
</div>

<div class="card" style="max-width:1100px;">
    <div class="card-body" style="padding:28px;">

        <form method="POST"
              action="{{ route('pengeluaran.sub.update', [$slug, $sub->id, $pengeluaran]) }}"
              id="form-edit">
            @csrf @method('PUT')
            @include('pengeluaran._sub-form', [
                'p' => $pengeluaran,
                'kategori' => $kategori,
                'sub' => $sub,
                'formContext' => $formContext,
                'karyawanAktif' => $karyawanAktif,
            ])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('pengeluaran.sub.index', [$slug, $sub->id]) }}"
                   class="btn btn-outline">Batal</a>
                <button type="button"
                        onclick="document.getElementById('form-hapus-sub').submit()"
                        class="btn btn-danger btn-delete">
                    Hapus
                </button>
            </div>
        </form>

        <form id="form-hapus-sub"
              method="POST"
              action="{{ route('pengeluaran.sub.destroy', [$slug, $sub->id, $pengeluaran]) }}"
              onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')"
              style="display:none;">
            @csrf @method('DELETE')
        </form>

    </div>
</div>

@endsection
