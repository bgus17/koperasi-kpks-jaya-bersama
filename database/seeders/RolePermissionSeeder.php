<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ──────────────────────────────────────
        $permissions = [
            // Pendapatan
            'pendapatan.view',
            'pendapatan.create',
            'pendapatan.update',
            'pendapatan.delete',

            // Pengeluaran
            'pengeluaran.view',
            'pengeluaran.create',
            'pengeluaran.update',
            'pengeluaran.delete',

            // Karyawan
            'karyawan.view',
            'karyawan.create',
            'karyawan.update',
            'karyawan.delete',

            // Rekap
            'rekap.view',
            'rekap.create',
            'rekap.update',
            'rekap.delete',

            // Biaya Produksi (II)
            'biaya-produksi.view',
            'biaya-produksi.create',
            'biaya-produksi.update',
            'biaya-produksi.delete',

            // Biaya Perawatan (III)
            'biaya-perawatan.view',
            'biaya-perawatan.create',
            'biaya-perawatan.update',
            'biaya-perawatan.delete',

            // Pembelian Pupuk & Racun (IV)
            'pembelian-pupuk.view',
            'pembelian-pupuk.create',
            'pembelian-pupuk.update',
            'pembelian-pupuk.delete',

            // Biaya Umum (V)
            'biaya-umum.view',
            'biaya-umum.create',
            'biaya-umum.update',
            'biaya-umum.delete',

            // Pemakaian Alat Berat (VI)
            'pemakaian-alat-berat.view',
            'pemakaian-alat-berat.create',
            'pemakaian-alat-berat.update',
            'pemakaian-alat-berat.delete',

            // Perlengkapan (VII)
            'perlengkapan.view',
            'perlengkapan.create',
            'perlengkapan.update',
            'perlengkapan.delete',

            // Insentive (VIII)
            'insentive.view',
            'insentive.create',
            'insentive.update',
            'insentive.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Roles ────────────────────────────────────────────

        // Admin — full access
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Mandor — biaya produksi, perawatan, alat berat
        $mandorRole = Role::firstOrCreate(['name' => 'mandor', 'guard_name' => 'web']);
        $mandorRole->syncPermissions([
            'biaya-produksi.view', 'biaya-produksi.create', 'biaya-produksi.update', 'biaya-produksi.delete',
            'biaya-perawatan.view', 'biaya-perawatan.create', 'biaya-perawatan.update', 'biaya-perawatan.delete',
            'pemakaian-alat-berat.view', 'pemakaian-alat-berat.create', 'pemakaian-alat-berat.update', 'pemakaian-alat-berat.delete',
            'pengeluaran.view', 'pengeluaran.create',
        ]);

        // Staff/Operator — pupuk, perlengkapan, biaya umum, insentive, rekap
        $staffRole = Role::firstOrCreate(['name' => 'staff_operator', 'guard_name' => 'web']);
        $staffRole->syncPermissions([
            'pembelian-pupuk.view', 'pembelian-pupuk.create', 'pembelian-pupuk.update', 'pembelian-pupuk.delete',
            'perlengkapan.view', 'perlengkapan.create', 'perlengkapan.update', 'perlengkapan.delete',
            'biaya-umum.view', 'biaya-umum.create', 'biaya-umum.update', 'biaya-umum.delete',
            'insentive.view', 'insentive.create', 'insentive.update', 'insentive.delete',
            'rekap.view',
            'pengeluaran.view', 'pengeluaran.create',
        ]);
    }
}
