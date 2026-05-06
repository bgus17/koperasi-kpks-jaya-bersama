{{-- resources/views/pengaturan/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('content')

{{-- Hidden input untuk pass data dari server ke JavaScript --}}
<input type="hidden" id="tab-to-open" value="{{ $tabToOpen ?? 'tab-koperasi' }}">

{{-- Tab Navigation --}}
<div style="display:flex; gap:8px; margin-bottom:24px;">
    <button onclick="switchTab('tab-koperasi')"
            id="btn-tab-koperasi"
            class="tab-btn active-tab">
        🏢 Info Koperasi
    </button>
    <button onclick="switchTab('tab-pengurus')"
            id="btn-tab-pengurus"
            class="tab-btn">
        👥 Susunan Pengurus
    </button>
    <button onclick="switchTab('tab-profil')"
            id="btn-tab-profil"
            class="tab-btn">
        🔐 Profil & Password
    </button>
</div>

<form method="POST" action="{{ route('pengaturan.update') }}">
    @csrf
    @method('PUT')

    {{-- ── TAB 1: Info Koperasi ── --}}
    <div id="tab-koperasi" class="tab-panel">
        <div class="card-setting">
            <div class="card-setting-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Informasi Koperasi
            </div>
            <div class="card-setting-body">

                <div class="form-group">
                    <label class="form-label">Nama Koperasi</label>
                    <input type="text" name="nama_koperasi"
                           class="form-input @error('nama_koperasi') is-error @enderror"
                           value="{{ old('nama_koperasi', $settings['nama_koperasi'] ?? '') }}"
                           placeholder="Contoh: KPKS Jaya Bersama">
                    @error('nama_koperasi')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Lokasi / Wilayah</label>
                        <input type="text" name="lokasi"
                               class="form-input @error('lokasi') is-error @enderror"
                               value="{{ old('lokasi', $settings['lokasi'] ?? '') }}"
                               placeholder="Contoh: Cahaya Mulya">
                        @error('lokasi')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor Badan Hukum</label>
                        <input type="text" name="nomor_badan_hukum"
                               class="form-input"
                               value="{{ old('nomor_badan_hukum', $settings['nomor_badan_hukum'] ?? '') }}"
                               placeholder="Contoh: 123/BH/KWK...">
                    </div>
                </div>

                <div class="form-group" style="max-width:240px;">
                    <label class="form-label">Tanggal Berdiri</label>
                    <input type="date" name="tanggal_berdiri"
                           class="form-input"
                           value="{{ old('tanggal_berdiri', $settings['tanggal_berdiri'] ?? '') }}">
                </div>

            </div>
        </div>
    </div>

    {{-- ── TAB 2: Susunan Pengurus ── --}}
    <div id="tab-pengurus" class="tab-panel" style="display:none;">
        <div class="card-setting">
            <div class="card-setting-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Susunan Pengurus
            </div>
            <div class="card-setting-body">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ketua Pengurus</label>
                        <input type="text" name="ketua_pengurus"
                               class="form-input @error('ketua_pengurus') is-error @enderror"
                               value="{{ old('ketua_pengurus', $settings['ketua_pengurus'] ?? '') }}">
                        @error('ketua_pengurus')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sekretaris</label>
                        <input type="text" name="sekretaris"
                               class="form-input @error('sekretaris') is-error @enderror"
                               value="{{ old('sekretaris', $settings['sekretaris'] ?? '') }}">
                        @error('sekretaris')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bendahara</label>
                        <input type="text" name="bendahara"
                               class="form-input @error('bendahara') is-error @enderror"
                               value="{{ old('bendahara', $settings['bendahara'] ?? '') }}">
                        @error('bendahara')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ketua Badan Pengawas</label>
                        <input type="text" name="ketua_pengawas"
                               class="form-input @error('ketua_pengawas') is-error @enderror"
                               value="{{ old('ketua_pengawas', $settings['ketua_pengawas'] ?? '') }}">
                        @error('ketua_pengawas')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Tombol Simpan (untuk tab 1 & 2) --}}
    <div id="btn-simpan-setting" style="margin-top:20px;">
        <button type="submit" class="btn-primary-setting">
            💾 Simpan Pengaturan
        </button>
    </div>

</form>

{{-- ── TAB 3: Profil & Password ── --}}
<div id="tab-profil" class="tab-panel" style="display:none;">
    <form method="POST" action="{{ route('pengaturan.updateProfil') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-setting">
            <div class="card-setting-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Profil Admin
            </div>
            <div class="card-setting-body">

                {{-- Foto Profil --}}
                <div class="form-group">
                    <label class="form-label">Foto Profil</label>
                    <div class="d-flex align-items-center" style="gap: 20px;">
                        @if (auth()->user()->profile_photo_path)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Foto Profil" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: #2d6a4f; color: white; font-size: 32px;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div style="flex-grow: 1;">
                            <input type="file" name="photo" class="form-input @error('photo') is-error @enderror">
                            <small class="form-text text-muted" style="font-size: 12px; color: #868e96; margin-top: 5px;">
                                Opsional. Pilih file gambar (JPG, PNG). Ukuran maks 2MB.
                            </small>
                            @error('photo')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name"
                               class="form-input @error('name') is-error @enderror"
                               value="{{ old('name', auth()->user()->name) }}">
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email"
                               class="form-input @error('email') is-error @enderror"
                               value="{{ old('email', auth()->user()->email) }}">
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        <div class="card-setting" style="margin-top:16px;">
            <div class="card-setting-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                Ganti Password
                <span style="font-size:12px; font-weight:400; color:#868e96; margin-left:8px;">
                    (kosongkan jika tidak ingin mengubah)
                </span>
            </div>
            <div class="card-setting-body">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password"
                               class="form-input @error('password') is-error @enderror"
                               placeholder="Minimal 8 karakter"
                               autocomplete="new-password">
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                               class="form-input"
                               placeholder="Ulangi password baru"
                               autocomplete="new-password">
                    </div>
                </div>

            </div>
        </div>

        <div style="margin-top:20px;">
            <button type="submit" class="btn-primary-setting">
                💾 Simpan Profil
            </button>
        </div>

    </form>
</div>

@endsection

@push('styles')
<style>
    /* ── Tab Buttons ───────────────────────────────────────── */
    .tab-btn {
        padding: 9px 18px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        background: #fff;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        color: #495057;
        transition: all 0.2s;
    }
    .tab-btn:hover {
        background: #f8f9fa;
    }
    .tab-btn.active-tab {
        background: #2d6a4f;
        color: #fff;
        border-color: #2d6a4f;
    }

    /* ── Card Setting ──────────────────────────────────────── */
    .card-setting {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }
    .card-setting-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
        font-size: 14px;
        color: #343a40;
    }
    .card-setting-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ── Form ──────────────────────────────────────────────── */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 640px) {
        .form-row { grid-template-columns: 1fr; }
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #495057;
    }
    .form-input {
        padding: 9px 12px;
        border: 1px solid #ced4da;
        border-radius: 7px;
        font-size: 14px;
        color: #343a40;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #fff;
    }
    .form-input:focus {
        outline: none;
        border-color: #2d6a4f;
        box-shadow: 0 0 0 3px rgba(45,106,79,0.12);
    }
    .form-input.is-error {
        border-color: #d64045;
    }
    .form-error {
        font-size: 12px;
        color: #d64045;
    }

    /* ── Button ────────────────────────────────────────────── */
    .btn-primary-setting {
        padding: 10px 24px;
        background: #2d6a4f;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-primary-setting:hover {
        background: #245a42;
    }
</style>
@endpush

@push('scripts')
<script>
function switchTab(tabId) {
    // sembunyikan semua panel & reset semua tombol
    var panels = document.querySelectorAll('.tab-panel');
    for (var i = 0; i < panels.length; i++) {
        panels[i].style.display = 'none';
    }
    
    var buttons = document.querySelectorAll('.tab-btn');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove('active-tab');
    }

    // tampilkan panel & aktifkan tombol
    document.getElementById(tabId).style.display = 'block';
    document.getElementById('btn-' + tabId).classList.add('active-tab');

    // tombol simpan hanya muncul untuk tab 1 & 2
    var btnSimpan = document.getElementById('btn-simpan-setting');
    if (btnSimpan) {
        btnSimpan.style.display = tabId === 'tab-profil' ? 'none' : 'block';
    }
}

// Menentukan tab yang akan dibuka berdasarkan error validasi
// Mengambil nilai dari hidden input
document.addEventListener('DOMContentLoaded', function () {
    var tabToOpenInput = document.getElementById('tab-to-open');
    var tabToOpen = tabToOpenInput ? tabToOpenInput.value : 'tab-koperasi';
    switchTab(tabToOpen);
});
</script>
@endpush