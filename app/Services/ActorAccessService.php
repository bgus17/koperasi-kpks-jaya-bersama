<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ActorAccessService
{
    public const MENUS = [
        'data-karyawan' => [
            'label' => 'Data Karyawan',
            'type' => 'admin_master',
            'roles' => [User::ROLE_ADMIN],
            'capabilities' => ['view', 'create', 'update', 'delete'],
        ],
        'pendapatan' => [
            'label' => 'Pendapatan',
            'type' => 'pendapatan',
            'roles' => [User::ROLE_ADMIN],
            'capabilities' => ['view', 'create', 'update', 'delete'],
        ],
        'biaya-produksi' => [
            'label' => 'Biaya Produksi',
            'type' => 'pengeluaran',
            'category_number' => 'II',
            'roles' => [User::ROLE_ADMIN, User::ROLE_MANDOR],
            'capabilities' => ['view', 'create', 'update', 'delete'],
        ],
        'biaya-perawatan' => [
            'label' => 'Biaya Perawatan',
            'type' => 'pengeluaran',
            'category_number' => 'III',
            'roles' => [User::ROLE_ADMIN, User::ROLE_MANDOR],
            'capabilities' => ['view', 'create', 'update', 'delete'],
        ],
        'pembelian-pupuk' => [
            'label' => 'Pembelian Pupuk & Racun',
            'type' => 'pengeluaran',
            'category_number' => 'IV',
            'roles' => [User::ROLE_ADMIN, User::ROLE_STAFF_OPERATOR],
            'capabilities' => ['view', 'create', 'update', 'delete'],
            'aliases' => ['pembelian-pupuk-racun'],
        ],
        'pemakaian-alat-berat' => [
            'label' => 'Pemakaian Alat Berat',
            'type' => 'pengeluaran',
            'category_number' => 'VI',
            'roles' => [User::ROLE_ADMIN, User::ROLE_MANDOR],
            'capabilities' => ['view', 'create', 'update', 'delete'],
        ],
        'perlengkapan' => [
            'label' => 'Perlengkapan',
            'type' => 'pengeluaran',
            'category_number' => 'VII',
            'roles' => [User::ROLE_ADMIN, User::ROLE_STAFF_OPERATOR],
            'capabilities' => ['view', 'create', 'update', 'delete'],
        ],
        'biaya-umum' => [
            'label' => 'Biaya Umum',
            'type' => 'pengeluaran',
            'category_number' => 'V',
            'roles' => [User::ROLE_ADMIN, User::ROLE_STAFF_OPERATOR],
            'capabilities' => ['view', 'create', 'update', 'delete'],
        ],
        'insentive' => [
            'label' => 'Insentive',
            'type' => 'pengeluaran',
            'category_number' => 'VIII',
            'roles' => [User::ROLE_ADMIN, User::ROLE_STAFF_OPERATOR],
            'capabilities' => ['view', 'create', 'update', 'delete'],
        ],
        'rekap-laporan-keuangan' => [
            'label' => 'Rekap Laporan Keuangan',
            'type' => 'rekap',
            'roles' => [User::ROLE_ADMIN, User::ROLE_STAFF_OPERATOR],
            'capabilities' => ['view'],
            'aliases' => ['rekap', 'rekap-keuangan'],
        ],
    ];

    public static function actorPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->spatie_role,
            'role_label' => $user->role_label,
            'karyawan_id' => $user->karyawan_id,
        ];
    }

    public static function menusForUser(User $user): Collection
    {
        return collect(self::MENUS)
            ->filter(fn (array $menu) => $user->hasEffectiveRole($menu['roles']))
            ->map(fn (array $menu, string $slug) => self::menuPayload($slug, $menu))
            ->values();
    }

    public static function menuForSlug(string $slug): ?array
    {
        $canonicalSlug = self::canonicalSlug($slug);

        if (!isset(self::MENUS[$canonicalSlug])) {
            return null;
        }

        return self::menuPayload($canonicalSlug, self::MENUS[$canonicalSlug]);
    }

    public static function canAccess(User $user, string $slug): bool
    {
        $menu = self::menuForSlug($slug);

        return $menu && $user->hasEffectiveRole($menu['roles']);
    }

    public static function categoryNumberForSlug(string $slug): ?string
    {
        $menu = self::menuForSlug($slug);

        return $menu['category_number'] ?? null;
    }

    public static function expenseCategoryNumbersForUser(User $user): array
    {
        return self::menusForUser($user)
            ->where('type', 'pengeluaran')
            ->pluck('category_number')
            ->filter()
            ->values()
            ->all();
    }

    public static function formSchemasByProfile(): array
    {
        $schemas = [];

        foreach (['angkutan', 'panen', 'berondol', 'perawatan', 'pupuk', 'alat_berat', 'perlengkapan', 'insentive', 'umum'] as $profile) {
            $schemas[$profile] = self::formSchemaForProfile($profile);
        }

        return $schemas;
    }

    public static function formSchemaForProfile(string $profile): array
    {
        $fields = [
            self::field('tanggal', 'Tanggal Kegiatan', 'date', true),
            self::field('sudah_bayar', 'Status Pembayaran', 'boolean'),
            self::field('jenis_transaksi', 'Jenis Transaksi Kas', 'select', true, [
                'options' => [
                    ['value' => 'kredit', 'label' => 'Kredit - pengeluaran keluar'],
                    ['value' => 'debet', 'label' => 'Debet - pengembalian dana'],
                    ['value' => 'saldo', 'label' => 'Saldo - penyesuaian keluar'],
                ],
                'default' => 'kredit',
            ]),
            self::field('jumlah', 'Jumlah Biaya (Rp)', 'money', true),
            self::field('keterangan', 'Keterangan Operasional', 'textarea'),
        ];

        foreach (self::detailFieldsForProfile($profile) as $field) {
            $fields[] = $field;
        }

        return [
            'profile' => $profile,
            'title' => self::profileTitle($profile),
            'fields' => $fields,
            'worker_fields' => self::workerFieldsForProfile($profile),
            'requires_workers' => in_array($profile, ['panen', 'berondol', 'perawatan'], true),
        ];
    }

    private static function menuPayload(string $slug, array $menu): array
    {
        return array_merge($menu, [
            'slug' => $slug,
            'aliases' => $menu['aliases'] ?? [],
        ]);
    }

    private static function canonicalSlug(string $slug): string
    {
        foreach (self::MENUS as $canonical => $menu) {
            if ($slug === $canonical || in_array($slug, $menu['aliases'] ?? [], true)) {
                return $canonical;
            }
        }

        return $slug;
    }

    private static function detailFieldsForProfile(string $profile): array
    {
        $map = [
            'angkutan' => [
                self::field('blok', 'Blok / Afdeling / Lokasi', 'text'),
                self::field('tonase_kg', 'Total Tonase TBS (Kg)', 'decimal'),
                self::field('volume', 'Ritase / Tonase Angkut', 'decimal'),
                self::field('satuan', 'Satuan', 'select', false, ['default' => 'rit', 'options' => self::satuanOptions()]),
                self::field('harga_satuan', 'Tarif / Harga Satuan (Rp)', 'money'),
                self::field('supplier_vendor', 'Vendor / Sopir', 'text'),
                self::field('no_referensi', 'No. SPB / Nota / Referensi', 'text'),
            ],
            'panen' => [
                self::field('mandor', 'Mandor / Pengawas Lapangan', 'readonly'),
                self::field('blok', 'Blok / Afdeling / Lokasi', 'text'),
                self::field('tonase_kg', 'Total Tonase TBS (Kg)', 'decimal'),
                self::field('jumlah_janjang', 'Total Janjang', 'integer'),
                self::field('brondolan_kg', 'Total Brondolan (Kg)', 'decimal'),
                self::field('volume', 'Basis/Output Panen', 'decimal'),
                self::field('satuan', 'Satuan', 'select', false, ['default' => 'kg', 'options' => self::satuanOptions()]),
                self::field('harga_satuan', 'Tarif / Harga Satuan (Rp)', 'money'),
            ],
            'berondol' => [
                self::field('mandor', 'Mandor / Pengawas Lapangan', 'readonly'),
                self::field('blok', 'Blok / Afdeling / Lokasi', 'text'),
                self::field('brondolan_kg', 'Total Brondolan (Kg)', 'decimal'),
                self::field('volume', 'Berondolan Terkutip', 'decimal'),
                self::field('satuan', 'Satuan', 'select', false, ['default' => 'kg', 'options' => self::satuanOptions()]),
                self::field('harga_satuan', 'Tarif / Harga Satuan (Rp)', 'money'),
            ],
            'perawatan' => [
                self::field('mandor', 'Mandor / Pengawas Lapangan', 'readonly'),
                self::field('blok', 'Blok / Afdeling / Lokasi', 'text'),
                self::field('luas_ha', 'Total Luas Kerja (Ha)', 'decimal'),
                self::field('volume', 'HK / Luas Kerja', 'decimal'),
                self::field('satuan', 'Satuan', 'select', false, ['default' => 'HK', 'options' => self::satuanOptions()]),
                self::field('harga_satuan', 'Tarif / Harga Satuan (Rp)', 'money'),
            ],
            'pupuk' => self::inventoryFields('Jumlah Pupuk/Racun', 'sak'),
            'alat_berat' => self::inventoryFields('Jam Kerja / HM', 'HM'),
            'perlengkapan' => self::inventoryFields('Jumlah Perlengkapan', 'unit'),
            'insentive' => self::simpleVendorFields('Jumlah Penerima', 'orang'),
            'umum' => self::simpleVendorFields('Volume', ''),
        ];

        return $map[$profile] ?? $map['umum'];
    }

    private static function inventoryFields(string $metricLabel, string $defaultSatuan): array
    {
        return [
            self::field('blok', 'Blok / Afdeling / Lokasi', 'text'),
            self::field('volume', $metricLabel, 'decimal'),
            self::field('satuan', 'Satuan', 'select', false, ['default' => $defaultSatuan, 'options' => self::satuanOptions()]),
            self::field('harga_satuan', 'Tarif / Harga Satuan (Rp)', 'money'),
            self::field('supplier_vendor', 'Penerima / Vendor / Supplier', 'text'),
            self::field('no_referensi', 'No. SPB / Nota / Referensi', 'text'),
        ];
    }

    private static function simpleVendorFields(string $metricLabel, string $defaultSatuan): array
    {
        return [
            self::field('volume', $metricLabel, 'decimal'),
            self::field('satuan', 'Satuan', 'select', false, ['default' => $defaultSatuan, 'options' => self::satuanOptions()]),
            self::field('harga_satuan', 'Tarif / Harga Satuan (Rp)', 'money'),
            self::field('supplier_vendor', 'Penerima / Vendor / Supplier', 'text'),
            self::field('no_referensi', 'No. SPB / Nota / Referensi', 'text'),
        ];
    }

    private static function workerFieldsForProfile(string $profile): array
    {
        if (!in_array($profile, ['panen', 'berondol', 'perawatan'], true)) {
            return [];
        }

        $common = [
            self::field('selected', 'Pilih Pekerja', 'boolean'),
            self::field('tarif_satuan', 'Tarif (Rp)', 'money'),
            self::field('upah', 'Upah (Rp)', 'money'),
            self::field('keterangan', 'Catatan', 'text'),
        ];

        $profileFields = [
            'panen' => [
                self::field('tonase_kg', 'TBS (Kg)', 'decimal'),
                self::field('jumlah_janjang', 'Janjang', 'integer'),
                self::field('brondolan_kg', 'Brondolan (Kg)', 'decimal'),
            ],
            'berondol' => [
                self::field('brondolan_kg', 'Brondolan (Kg)', 'decimal'),
            ],
            'perawatan' => [
                self::field('hk', 'HK', 'decimal'),
                self::field('luas_ha', 'Luas (Ha)', 'decimal'),
                self::field('volume', 'Volume', 'decimal'),
                self::field('satuan', 'Satuan', 'select', false, ['options' => self::satuanOptions()]),
            ],
        ];

        return array_merge($profileFields[$profile], $common);
    }

    private static function profileTitle(string $profile): string
    {
        return match ($profile) {
            'angkutan' => 'Distribusi dan Angkutan',
            'panen' => 'Aktivitas Panen',
            'berondol' => 'Kutip Berondol',
            'perawatan' => 'Aktivitas Perawatan',
            'pupuk' => 'Pembelian / Distribusi Pupuk & Racun',
            'alat_berat' => 'Pemakaian Alat Berat',
            'perlengkapan' => 'Perlengkapan Operasional Kebun',
            'insentive' => 'Insentive Operasional',
            default => 'Detail Operasional',
        };
    }

    private static function satuanOptions(): array
    {
        return ['kg', 'ton', 'janjang', 'HK', 'ha', 'liter', 'sak', 'rit', 'HM', 'jam', 'unit', 'pcs', 'set', 'meter', 'batang', 'roll', 'paket', 'orang', 'bulan', 'hari'];
    }

    private static function field(string $name, string $label, string $type, bool $required = false, array $extra = []): array
    {
        return array_merge([
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'required' => $required,
        ], $extra);
    }
}
