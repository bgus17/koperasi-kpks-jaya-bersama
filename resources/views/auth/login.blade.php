<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Koperasi Cahaya Mulya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600&display=swap" rel="stylesheet">
    @vite(['resources/css/login.css'])
</head>
<body>

<div class="branding">
    <div class="brand-logo">🌿</div>
    <h1 class="brand-name">Koperasi<br><em>Cahaya Mulya</em></h1>
    <p class="brand-desc">
        Panel administrasi untuk mengelola pendapatan, pengeluaran,
        data karyawan, dan rekap keuangan koperasi kelapa sawit.
    </p>
    <div class="brand-stats">
        <div class="bstat">
            <div class="bstat-val">Rp 9,5M+</div>
            <div class="bstat-label">Total Debet 2025</div>
        </div>
        <div class="bstat">
            <div class="bstat-val">11</div>
            <div class="bstat-label">Kategori</div>
        </div>
        <div class="bstat">
            <div class="bstat-val">2025</div>
            <div class="bstat-label">Tahun Buku</div>
        </div>
    </div>
</div>

<div class="login-panel">
    <div class="login-header">
        <h2>Admin Panel</h2>
        <p>Masuk dengan akun administrator untuk mengelola sistem.</p>
    </div>

    <form method="POST" action="{{ route('admin.login.post') }}" id="admin-login-form">
        @csrf

        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="admin@koperasi.com"
                autocomplete="email"
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                required
            >
            @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                autocomplete="current-password"
                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                required
            >
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-check">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ingat saya di perangkat ini</label>
        </div>

        <button type="submit" class="btn-login" id="btn-admin-login">Masuk sebagai Admin →</button>
    </form>

    <div class="login-alt">
        <p>Bukan administrator? <a href="/user/#/login" id="link-user-portal">Masuk ke Portal User →</a></p>
    </div>

    <div class="login-footer">
        <a href="{{ route('gateway') }}" id="link-back-gateway">← Kembali ke halaman utama</a>
    </div>
</div>

</body>
</html>
