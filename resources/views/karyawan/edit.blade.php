{{-- FILE: resources/views/karyawan/karyawan_edit.blade.php --}}

@extends('layouts.app')
@section('title', 'Edit — ' . $karyawan->nama)
@section('page-title', 'Edit Karyawan')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('karyawan.index') }}">Karyawan</a>
    <span>›</span>
    <a href="{{ route('karyawan.show', $karyawan) }}">{{ $karyawan->nama }}</a>
    <span>›</span>
    <span>Edit</span>
</div>

<div class="page-header">
    <div>
        <h2>Edit Karyawan</h2>
        <p>Mengubah data: <strong>{{ $karyawan->nama }}</strong></p>
    </div>
    <a href="{{ route('karyawan.show', $karyawan) }}" class="btn btn-outline">← Kembali</a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Form Edit Karyawan</span>
        <span style="font-size:12px;color:var(--abu);">ID: {{ $karyawan->id }}</span>
    </div>
    <div class="card-body">
        {{--
            Nested <form> tidak valid HTML — form hapus dipindahkan ke luar form utama.
            Keduanya menggunakan ID tombol yang saling terpisah.
        --}}
        <form method="POST" action="{{ route('karyawan.update', $karyawan) }}" id="form-edit">
            @csrf @method('PUT')
            @include('karyawan._form')
            <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--krem-drk);
                        display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('karyawan.show', $karyawan) }}" class="btn btn-outline">Batal</a>

                {{-- Tombol hapus memicu form-hapus di bawah (di luar form utama) --}}
                <button type="button"
                        onclick="document.getElementById('form-hapus').submit()"
                        class="btn btn-danger"
                        style="margin-left:auto;">
                    Hapus
                </button>
            </div>
        </form>

        {{-- Form hapus terpisah, di luar form-edit agar tidak nested --}}
        <form id="form-hapus"
              method="POST"
              action="{{ route('karyawan.destroy', $karyawan) }}"
              onsubmit="return confirm('Yakin ingin menghapus karyawan {{ addslashes($karyawan->nama) }}?')"
              style="display:none;">
            @csrf @method('DELETE')
        </form>
    </div>
</div>

@endsection
