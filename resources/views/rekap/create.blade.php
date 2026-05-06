{{-- FILE: resources/views/rekap/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Buat Rekap Baru')
@section('page-title', 'Buat Rekap Keuangan')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('rekap.index') }}">Rekap Keuangan</a>
    <span>/</span>
    <span>Buat Baru</span>
</div>

<div class="page-header">
    <div>
        <h2>Buat Rekap Keuangan</h2>
        <p>Isi grand total dan informasi pengurus untuk periode tutup buku.</p>
    </div>
    <a href="{{ route('rekap.index') }}" class="btn btn-outline">← Kembali</a>
</div>

<div style="background:#fffbeb;border:1px solid var(--emas-lt);border-left:3px solid var(--emas);
            border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#92400e;">
    💡 <strong>Tips:</strong> Gunakan tombol <em>Hitung Ulang</em> di halaman Rekap untuk mengisi
    nilai otomatis dari data pendapatan &amp; pengeluaran yang sudah ada.
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Form Rekap</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('rekap.store') }}">
            @csrf

            @include('rekap._form', ['rekap' => null])

            <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--krem-drk);display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary">💾 Simpan Rekap</button>
                <a href="{{ route('rekap.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
