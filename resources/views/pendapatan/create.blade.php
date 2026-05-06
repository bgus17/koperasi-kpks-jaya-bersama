{{-- resources/views/pendapatan/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Pendapatan')
@section('page-title', 'Tambah Data Pendapatan')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('pendapatan.index') }}">Pendapatan</a>
    <span>/</span>
    <span>Tambah Baru</span>
</div>

<div class="page-header">
    <div>
        <h2>Tambah Data Pendapatan</h2>
        <p>Isi form di bawah untuk menambahkan transaksi pendapatan baru.</p>
    </div>
    <a href="{{ route('pendapatan.index') }}" class="btn btn-outline">← Kembali</a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Form Pendapatan</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('pendapatan.store') }}">
            @csrf

            <div class="form-grid">

                {{-- Kategori --}}
                <div class="form-group">
                    <label for="kategori">Kategori *</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">— Pilih Kategori —</option>
                        <option value="Saldo Per 31 Des"    {{ old('kategori') == 'Saldo Per 31 Des'    ? 'selected' : '' }}>Saldo Per 31 Des</option>
                        <option value="Pendapatan Diterima" {{ old('kategori') == 'Pendapatan Diterima' ? 'selected' : '' }}>Pendapatan Diterima</option>
                        <option value="Penjualan Barang"    {{ old('kategori') == 'Penjualan Barang'    ? 'selected' : '' }}>Penjualan Barang</option>
                    </select>
                    @error('kategori')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Debet — pakai .rupiah-input, diformat oleh app.js --}}
                <div class="form-group">
                    <label for="debet">Debet (Rp)</label>
                    <input type="text" id="debet" name="debet"
                           class="rupiah-input"
                           value="{{ old('debet', 0) }}"
                           placeholder="0">
                    @error('debet')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Kredit --}}
                <div class="form-group">
                    <label for="kredit">Kredit (Rp)</label>
                    <input type="text" id="kredit" name="kredit"
                           class="rupiah-input"
                           value="{{ old('kredit', 0) }}"
                           placeholder="0">
                    @error('kredit')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Saldo --}}
                <div class="form-group">
                    <label for="saldo">Saldo (Rp)</label>
                    <input type="text" id="saldo" name="saldo"
                           class="rupiah-input"
                           value="{{ old('saldo', 0) }}"
                           placeholder="0">
                    <small style="font-size:11px;color:var(--abu);margin-top:3px;">Saldo berjalan setelah transaksi ini.</small>
                    @error('saldo')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div class="form-group">
                    <label for="tanggal">Tanggal *</label>
                    <input type="date" id="tanggal" name="tanggal"
                           value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    @error('tanggal')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Keterangan --}}
                <div class="form-group full">
                    <label for="keterangan">Keterangan (opsional)</label>
                    <textarea id="keterangan" name="keterangan" rows="3"
                              placeholder="Catatan tambahan jika ada...">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div style="margin-top:28px; padding-top:20px; border-top:1px solid var(--krem-drk); display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary">
                    💾 Simpan Data
                </button>
                <a href="{{ route('pendapatan.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
