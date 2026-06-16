<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Informasi Keuangan Koperasi Jaya Bersama — Kelola pendapatan, pengeluaran, dan rekap keuangan koperasi kelapa sawit secara terintegrasi.">
    <title>KPKS Jaya Bersama — Sistem Keuangan Internal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600&display=swap" rel="stylesheet">
    @vite(['resources/css/gateway.css'])
</head>
<body>

<div class="gateway-container">

    {{-- ── Company Header ── --}}
    <header class="company-header">
        <div class="company-logo">🌿</div>
        <h1 class="company-name">KPKS <em>JAYA BERSAMA</em></h1>
        <p class="company-tagline">
            Sistem informasi keuangan terintegrasi untuk pengelolaan dana kebun,
            pendapatan, dan pengeluaran koperasi kelapa sawit.
        </p>
    </header>

    <div class="divider"></div>

    {{-- ── Company Profile ── --}}
    <section class="company-profile">
        <h2 class="profile-title">Profil Koperasi</h2>
        <p class="profile-desc">
            Koperasi Perkebunan Kelapa Sawit (KPKS) Jaya Bersama adalah koperasi yang bergerak
            di bidang perkebunan kelapa sawit. Didirikan dengan tujuan meningkatkan kesejahteraan
            anggota melalui pengelolaan kebun yang terstruktur dan transparan. Sistem ini dirancang
            untuk membantu pencatatan keuangan, monitoring biaya produksi, perawatan, dan pemakaian
            alat berat secara digital.
        </p>
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-value">KPKS</div>
                <div class="stat-label">Jenis Koperasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🌴</div>
                <div class="stat-value">Sawit</div>
                <div class="stat-label">Sektor Usaha</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value">{{ date('Y') }}</div>
                <div class="stat-label">Tahun Buku</div>
            </div>
        </div>
    </section>

    {{-- ── Portal Selection ── --}}
    <div class="portal-section-title">
        <h2>Pilih Portal Akses</h2>
        <p>Masuk sesuai dengan peran Anda dalam sistem koperasi</p>
    </div>

    <div class="portal-cards">
        {{-- Admin Panel --}}
        <a href="{{ route('admin.login') }}" class="portal-card portal-card--admin" id="portal-admin">
            <div class="portal-icon">🛡️</div>
            <h3 class="portal-card-title">Admin Panel</h3>
            <p class="portal-card-desc">
                Kelola pendapatan, pengeluaran, data karyawan, dan rekap keuangan koperasi
                melalui dashboard administrasi.
            </p>
            <div class="portal-card-roles">
                <span class="role-badge">Administrator</span>
            </div>
            <span class="portal-card-action">
                Masuk sebagai Admin <span class="arrow">→</span>
            </span>
        </a>

        {{-- User Portal --}}
        <a href="/user/#/login" class="portal-card portal-card--user" id="portal-user">
            <div class="portal-icon">📋</div>
            <h3 class="portal-card-title">User Portal</h3>
            <p class="portal-card-desc">
                Input data biaya produksi, perawatan, dan pemakaian alat berat
                sesuai akses aktor lapangan.
            </p>
            <div class="portal-card-roles">
                <span class="role-badge">Mandor</span>
                <span class="role-badge">Staff / Operator</span>
            </div>
            <span class="portal-card-action">
                Masuk ke Portal User <span class="arrow">→</span>
            </span>
        </a>
    </div>

    {{-- ── Footer ── --}}
    <footer class="gateway-footer">
        <p>KPKS Jaya Bersama &copy; {{ date('Y') }} &mdash; Sistem Keuangan Internal</p>
    </footer>

</div>

</body>
</html>
