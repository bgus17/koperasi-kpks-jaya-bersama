{{-- FILE: resources/views/karyawan/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@section('content')

<div class="page-header">
    <div>
        <h2>Data Karyawan</h2>
        <p>Master nama pekerja dan mandor untuk input aktivitas lapangan.</p>
    </div>
    <a href="{{ route('karyawan.create') }}" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        Tambah Karyawan
    </a>
</div>

{{-- STAT CARDS --}}
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-label">Total Karyawan</div>
        <div class="stat-value">{{ $totalSemua }}</div>
        <div class="stat-icon">👥</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Aktif</div>
        <div class="stat-value stat-aktif">{{ $totalAktif }}</div>
        <div class="stat-icon">✅</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Cuti</div>
        <div class="stat-value stat-cuti">{{ $totalCuti }}</div>
        <div class="stat-icon">🏖️</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Non-aktif</div>
        <div class="stat-value stat-nonaktif">{{ $totalNonaktif }}</div>
        <div class="stat-icon">❌</div>
    </div>
</div>

{{-- FILTER --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 20px;">
        <form method="GET" action="{{ route('karyawan.index') }}" class="filter-bar">
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="aktif"    {{ request('status') == 'aktif'    ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                    <option value="cuti"     {{ request('status') == 'cuti'     ? 'selected' : '' }}>Cuti</option>
                </select>
            </div>
            <div class="form-group" style="max-width:260px;">
                <label>Cari Nama / No. HP</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Ketik nama atau no. HP...">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:1px;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('karyawan.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- TABEL --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Daftar Karyawan</span>
        <span style="font-size:13px;color:var(--abu);">
            {{ $karyawan->firstItem() ?? 0 }}–{{ $karyawan->lastItem() ?? 0 }}
            dari {{ $karyawan->total() }} data
        </span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th class="text-center">JK</th>
                    <th>No. HP</th>
                    <th>Tanggal Masuk</th>
                    <th>Keterangan</th>
                    <th class="text-center" style="width:90px;">Status</th>
                    <th class="text-center" style="width:120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($karyawan as $row)
                    @php
                        // Semua kondisi PHP dikumpulkan di blok @php,
                        // tidak ditulis langsung di dalam atribut style="" atau onsubmit="".
                        $genderClass = $row->jenis_kelamin === 'L' ? 'gender-l' : 'gender-p';
                        $statusClass = 'badge-status badge-status--' . ($row->status ?? 'nonaktif');
                        $statusLabel = $row->status_label ?? ucfirst($row->status ?? '—');
                        $namaEscaped = e($row->nama);
                    @endphp
                    <tr>
                        <td style="font-weight:500;">{{ $row->nama }}</td>

                        {{--
                            FIX baris 103-104:
                            Hapus style="--text-color:{{ }};font-size:12px;..."
                            Ganti dengan class dinamis — warna cukup diatur di app.css.
                        --}}
                        <td class="text-center">
                            <span class="gender-text {{ $genderClass }}">
                                {{ $row->jenis_kelamin ?? '—' }}
                            </span>
                        </td>

                        <td style="font-size:13px;">{{ $row->no_hp ?: '—' }}</td>
                        <td style="font-size:13px;">{{ $row->tanggal_masuk?->format('d/m/Y') ?: '—' }}</td>
                        <td style="font-size:13px;color:var(--abu);max-width:260px;">{{ $row->keterangan ?: '—' }}</td>

                        {{--
                            FIX baris 112:
                            Hapus style="--badge-color:{{ $row->status_color }}"
                            Ganti dengan class dinamis — warna diatur di app.css.
                        --}}
                        <td class="text-center">
                            <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>

                        {{--
                            FIX baris 123:
                            Hapus onsubmit="return confirm('Hapus {{ addslashes($row->nama) }}?')"
                            Ganti dengan data-nama + event listener di @push('scripts').
                        --}}
                        <td class="text-center">
                            <div style="display:flex;gap:5px;justify-content:center;">
                                <a href="{{ route('karyawan.show', $row) }}"
                                   class="btn btn-outline btn-sm" title="Detail">👁</a>
                                <a href="{{ route('karyawan.edit', $row) }}"
                                   class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('karyawan.destroy', $row) }}"
                                      class="form-hapus"
                                      data-nama="{{ $namaEscaped }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center"
                            style="padding:48px;color:var(--abu);font-style:italic;">
                            Tidak ada data karyawan ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($karyawan->hasPages())
        <div style="padding:12px 20px;border-top:1px solid var(--krem-drk);">
            {{ $karyawan->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.form-hapus').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var nama = form.dataset.nama || 'karyawan ini';
            if (!confirm('Hapus karyawan ' + nama + '?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush