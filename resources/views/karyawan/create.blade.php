{{-- FILE: resources/views/karyawan/karyawan_create.blade.php --}}

@extends('layouts.app')
@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('karyawan.index') }}">Karyawan</a>
    <span>›</span>
    <span>Tambah Baru</span>
</div>

<div class="page-header">
    <div>
        <h2>Tambah Karyawan</h2>
        <p>Daftarkan nama pekerja atau mandor untuk aktivitas lapangan.</p>
    </div>
    <a href="{{ route('karyawan.index') }}" class="btn btn-outline">← Kembali</a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Form Data Karyawan</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('karyawan.store') }}">
            @csrf
            @include('karyawan._form')
            <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--krem-drk);
                        display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary">Simpan Data</button>
                <a href="{{ route('karyawan.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
