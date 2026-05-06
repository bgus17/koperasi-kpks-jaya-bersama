{{-- resources/views/pendapatan/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Pendapatan')
@section('page-title', 'Edit Data Pendapatan')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('pendapatan.index') }}">Pendapatan</a>
    <span>/</span>
    <span>Edit #{{ $pendapatan->id }}</span>
</div>

<div class="page-header">
    <div>
        <h2>Edit Data Pendapatan</h2>
        <p>Mengubah data: <strong>{{ $pendapatan->sub_kategori }}</strong></p>
    </div>
    <a href="{{ route('pendapatan.index') }}" class="btn btn-outline">← Kembali</a>
</div>

{{-- INFO KONTEKS --}}
<div style="background:var(--krem);border:1px solid var(--krem-drk);border-left:3px solid var(--emas);
            border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--hijau);">
    <strong>{{ $pendapatan->nomor_kategori }} — {{ $pendapatan->kategori }}</strong><br>
    <span style="color:var(--abu);">Saldo saat ini:
        <strong>Rp {{ number_format($pendapatan->saldo, 0, ',', '.') }}</strong>
    </span>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Form Edit Pendapatan</span>
        <span style="font-size:12px;color:var(--abu);">ID: {{ $pendapatan->id }}</span>
    </div>
    <div class="card-body">

        <form method="POST" action="{{ route('pendapatan.update', $pendapatan) }}" id="form-edit">
            @csrf @method('PUT')

            <div class="form-grid">

                {{-- Nomor Kategori --}}
                <div class="form-group">
                    <label for="nomor_kategori">Nomor Kategori *</label>
                    <select name="nomor_kategori" id="nomor_kategori" required>
                        @foreach(['I','II','III','IV','V','VI','VII'] as $n)
                            <option value="{{ $n }}"
                                {{ old('nomor_kategori', $pendapatan->nomor_kategori) == $n ? 'selected' : '' }}>
                                {{ $n }}
                            </option>
                        @endforeach
                    </select>
                    @error('nomor_kategori')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Kategori --}}
                <div class="form-group">
                    <label for="kategori">Kategori *</label>
                    <input type="text" id="kategori" name="kategori"
                           value="{{ old('kategori', $pendapatan->kategori) }}"
                           list="kategori-list" required>
                    <datalist id="kategori-list">
                        @foreach($kategoriList as $item)
                            <option value="{{ $item->kategori }}">
                        @endforeach
                    </datalist>
                    @error('kategori')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Sub Kategori --}}
                <div class="form-group full">
                    <label for="sub_kategori">Sub Kategori *</label>
                    <input type="text" id="sub_kategori" name="sub_kategori"
                           value="{{ old('sub_kategori', $pendapatan->sub_kategori) }}" required>
                    @error('sub_kategori')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Debet — pakai .rupiah-input, diformat oleh app.js --}}
                <div class="form-group">
                    <label for="debet">Debet (Rp)</label>
                    <input type="text" id="debet" name="debet"
                           class="rupiah-input"
                           value="{{ old('debet', $pendapatan->debet) }}"
                           placeholder="0">
                    @error('debet')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Kredit --}}
                <div class="form-group">
                    <label for="kredit">Kredit (Rp)</label>
                    <input type="text" id="kredit" name="kredit"
                           class="rupiah-input"
                           value="{{ old('kredit', $pendapatan->kredit) }}"
                           placeholder="0">
                    @error('kredit')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Saldo --}}
                <div class="form-group">
                    <label for="saldo">Saldo (Rp)</label>
                    <input type="text" id="saldo" name="saldo"
                           class="rupiah-input"
                           value="{{ old('saldo', $pendapatan->saldo) }}"
                           placeholder="0">
                    @error('saldo')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Tahun --}}
                <div class="form-group">
                    <label for="tahun">Tahun *</label>
                    <select id="tahun" name="tahun" required>
                        @for($y = date('Y') + 1; $y >= 2020; $y--)
                            <option value="{{ $y }}"
                                {{ old('tahun', $pendapatan->tahun) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                    @error('tahun')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Tanggal --}}
                <div class="form-group">
                    <label for="tanggal">Tanggal *</label>
                    <input type="date" id="tanggal" name="tanggal"
                           value="{{ old('tanggal', $pendapatan->tanggal?->format('Y-m-d')) }}" required>
                    @error('tanggal')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Keterangan --}}
                <div class="form-group full">
                    <label for="keterangan">Keterangan (opsional)</label>
                    <textarea id="keterangan" name="keterangan" rows="3"
                              placeholder="Catatan tambahan jika ada...">{{ old('keterangan', $pendapatan->keterangan) }}</textarea>
                    @error('keterangan')<span class="form-error">{{ $message }}</span>@enderror
                </div>

            </div>

            <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--krem-drk);
                        display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <button type="submit" class="btn btn-gold">✓ Simpan Perubahan</button>
                <a href="{{ route('pendapatan.index') }}" class="btn btn-outline">Batal</a>
                <button type="button"
                        onclick="document.getElementById('form-hapus').submit()"
                        class="btn btn-danger"
                        style="margin-left:auto;">
                    🗑 Hapus
                </button>
            </div>
        </form>

        {{-- Form hapus dipisah agar tidak nested di dalam form-edit --}}
        <form id="form-hapus"
              method="POST"
              action="{{ route('pendapatan.destroy', $pendapatan) }}"
              onsubmit="return confirm('Yakin ingin menghapus data ini?')"
              style="display:none;">
            @csrf @method('DELETE')
        </form>

    </div>
</div>

@endsection
