@php
    $isPengeluaran = request()->routeIs('pengeluaran.*') || request()->routeIs('biaya.*');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KPKS Jaya Bersama') — Sistem Keuangan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-panel">

<aside class="sidebar" id="admin-sidebar" data-mobile-sidebar>
    <div class="sidebar-brand">
        <div class="logo-icon">🌿</div>
        <h1>KPKS<br>Jaya Bersama</h1>
        <p>Sistem Keuangan</p>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Menu Utama</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 13h8V3H3v10ZM13 21h8V3h-8v18ZM3 21h8v-6H3v6Z"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('rekap.index') }}"
           class="nav-link {{ request()->routeIs('rekap.*') ? 'active' : '' }}">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/>
            </svg>
            Rekap Keuangan
        </a>

        <div class="nav-label">Transaksi</div>

        <a href="{{ route('pendapatan.index') }}"
           class="nav-link {{ request()->routeIs('pendapatan.*') ? 'active' : '' }}">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Dana Kebun (Pendapatan)
        </a>

        <button class="nav-link nav-dropdown-toggle {{ $isPengeluaran ? 'active' : '' }}"
                type="button"
                id="toggle-pengeluaran"
                data-dropdown="drop-pengeluaran"
                aria-controls="drop-pengeluaran"
                aria-expanded="{{ $isPengeluaran ? 'true' : 'false' }}">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M2 8h20M2 12h20M2 16h12"/><circle cx="19" cy="16" r="3"/>
                <path d="m21 18-2-2"/>
            </svg>
            <span style="flex:1;text-align:left;">Pengeluaran & Biaya</span>
            <svg class="chevron {{ $isPengeluaran ? 'open' : '' }}" width="14" height="14"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </button>

        <div class="nav-dropdown {{ $isPengeluaran ? 'open' : '' }}" id="drop-pengeluaran">
            <a href="{{ route('pengeluaran.index') }}"
               class="nav-sub-link {{ request()->routeIs('pengeluaran.index') ? 'active' : '' }}">
                <span class="sub-dot"></span>
                Semua Pengeluaran
            </a>

            <a href="{{ route('pengeluaran.kategori', 'biaya-produksi') }}"
               class="nav-sub-link {{ request()->routeIs('pengeluaran.*') && request()->route('slug') === 'biaya-produksi' ? 'active' : '' }}">
                <span class="sub-dot"></span>
                Biaya Produksi
            </a>

            <a href="{{ route('pengeluaran.kategori', 'biaya-perawatan') }}"
               class="nav-sub-link {{ request()->routeIs('pengeluaran.*') && request()->route('slug') === 'biaya-perawatan' ? 'active' : '' }}">
                <span class="sub-dot"></span>
                Biaya Perawatan
            </a>

            <a href="{{ route('pengeluaran.kategori', 'pembelian-pupuk') }}"
               class="nav-sub-link {{ request()->routeIs('pengeluaran.*') && request()->route('slug') === 'pembelian-pupuk' ? 'active' : '' }}">
                <span class="sub-dot"></span>
                Pembelian Pupuk & Racun
            </a>

            <a href="{{ route('pengeluaran.kategori', 'pemakaian-alat-berat') }}"
               class="nav-sub-link {{ request()->routeIs('pengeluaran.*') && request()->route('slug') === 'pemakaian-alat-berat' ? 'active' : '' }}">
                <span class="sub-dot"></span>
                Pemakaian Alat Berat
            </a>

            <a href="{{ route('pengeluaran.kategori', 'perlengkapan') }}"
               class="nav-sub-link {{ request()->routeIs('pengeluaran.*') && request()->route('slug') === 'perlengkapan' ? 'active' : '' }}">
                <span class="sub-dot"></span>
                Perlengkapan
            </a>

            <a href="{{ route('pengeluaran.kategori', 'insentive') }}"
               class="nav-sub-link {{ request()->routeIs('pengeluaran.*') && request()->route('slug') === 'insentive' ? 'active' : '' }}">
                <span class="sub-dot"></span>
                Insentive
            </a>

            <a href="{{ route('pengeluaran.kategori', 'biaya-umum') }}"
               class="nav-sub-link {{ request()->routeIs('pengeluaran.*') && request()->route('slug') === 'biaya-umum' ? 'active' : '' }}">
                <span class="sub-dot"></span>
                Biaya Umum
            </a>
        </div>

        <div class="nav-label">SDM</div>

        <a href="{{ route('karyawan.index') }}"
           class="nav-link {{ request()->routeIs('karyawan.*') ? 'active' : '' }}">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Data Karyawan
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">{{ Auth::user()->role_label }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>

<button class="mobile-sidebar-overlay" type="button" data-sidebar-close aria-label="Tutup menu"></button>

<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-left">
            <button class="mobile-menu-button" type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false">
                <span class="sr-only">Menu</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        </div>
        <div class="topbar-right">
            <span class="badge-tahun">Tahun Buku {{ date('Y') }}</span>
        </div>
    </header>

    <main class="content">
        @if(session('success'))
            <div class="alert alert-success" role="status">
                <span>✓ {{ session('success') }}</span>
                <button class="alert-close" type="button" data-alert-close aria-label="Tutup notifikasi">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error" role="alert">
                <span>✕ {{ session('error') }}</span>
                <button class="alert-close" type="button" data-alert-close aria-label="Tutup notifikasi">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    window.activeDropdowns = {!! json_encode($isPengeluaran ? ['drop-pengeluaran'] : []) !!};
</script>

@stack('scripts')

</body>
</html>
