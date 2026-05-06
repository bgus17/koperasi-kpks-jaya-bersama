{{-- resources/views/pengeluaran/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Pengeluaran')
@section('page-title', 'Tambah Data Pengeluaran')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('pengeluaran.index') }}">Pengeluaran</a>
    <span>/</span>
    <span>Tambah Baru</span>
</div>

<div class="page-header">
    <div>
        <h2>Tambah Data Pengeluaran</h2>
        <p>Input biaya panen, angkutan, perawatan, pupuk, dan operasional lapangan.</p>
    </div>
    <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline">← Kembali</a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Form Pengeluaran</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('pengeluaran.store') }}">
            @csrf

            @include('pengeluaran._sub-form', [
                'p' => null,
                'kategoriList' => $kategoriList,
                'formContext' => $formContext,
                'karyawanAktif' => $karyawanAktif,
            ])

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Data</button>
                <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
