{{-- FILE: resources/views/rekap/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Rekap ' . $rekap->tahun)
@section('page-title', 'Edit Rekap Keuangan')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('rekap.index') }}">Rekap Keuangan</a>
    <span>/</span>
    <span>Edit Tahun {{ $rekap->tahun }}</span>
</div>

<div class="page-header">
    <div>
        <h2>Edit Rekap Tahun {{ $rekap->tahun }}</h2>
        <p>Perbarui grand total dan informasi pengurus koperasi.</p>
    </div>
    <a href="{{ route('rekap.index', ['tahun' => $rekap->tahun]) }}" class="btn btn-outline">← Kembali</a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Form Edit Rekap</span>
        <span style="font-size:12px;color:var(--abu);">
            Tutup buku: {{ $rekap->tanggal_tutup?->format('d M Y') }}
        </span>
    </div>
    <div class="card-body">

        <form method="POST" action="{{ route('rekap.update', $rekap) }}" id="form-edit">
            @csrf @method('PUT')

            @include('rekap._form', ['rekap' => $rekap])

            <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--krem-drk);
                        display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <button type="submit" class="btn btn-gold">✓ Simpan Perubahan</button>
                <a href="{{ route('rekap.index', ['tahun' => $rekap->tahun]) }}" class="btn btn-outline">Batal</a>
                {{-- Tombol hapus memicu form-hapus di luar form-edit --}}
                <button type="button"
                        onclick="document.getElementById('form-hapus-rekap').submit()"
                        class="btn btn-danger"
                        style="margin-left:auto;">
                    🗑 Hapus Rekap
                </button>
            </div>
        </form>

        {{-- Form hapus terpisah — tidak nested di dalam form-edit --}}
        <form id="form-hapus-rekap"
              method="POST"
              action="{{ route('rekap.destroy', $rekap) }}"
              onsubmit="return confirm('Hapus rekap tahun {{ $rekap->tahun }}? Tindakan ini tidak dapat dibatalkan.')"
              style="display:none;">
            @csrf @method('DELETE')
        </form>

    </div>
</div>

@endsection
