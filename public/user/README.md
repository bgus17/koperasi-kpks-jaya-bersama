# Portal User Koperasi

Frontend statis untuk aktor API:

- `mandor`
- `staff_operator`

Struktur:

- `index.html`: entry point SPA.
- `.htaccess`: header keamanan dan fallback SPA.
- `assets/css`: design tokens dan komponen UI.
- `assets/js/core`: konfigurasi, session, HTTP client, router, helper DOM, dan pemeriksaan konteks HTTPS.
- `assets/js/modules`: layar login, dashboard, form transaksi, rekap, dan shell aplikasi.

Catatan keamanan:

- Tidak ada API key, password, atau secret yang disimpan di frontend.
- Token API hanya disimpan di `sessionStorage`, bukan `localStorage`, sehingga hilang saat sesi browser ditutup.
- Semua akses tetap divalidasi server melalui `auth:sanctum` dan middleware role.
- Produksi wajib memakai HTTPS agar Bearer token tidak terkirim di koneksi plaintext.
- Header keamanan disiapkan di `.htaccess`; pastikan `mod_headers` aktif pada Apache produksi.
