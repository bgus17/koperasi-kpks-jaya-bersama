<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // Seed roles & permissions TERLEBIH DAHULU
        // ============================================================
        $this->call([
            RolePermissionSeeder::class,
            KaryawanSeeder::class,
            PengeluaranKategoriSeeder::class,
            KeuanganSeeder::class,
        ]);

        // Ambil ID karyawan pertama untuk mandor dan staff
        $mandorId = DB::table('karyawan')
            ->where('status', 'aktif')
            ->orderBy('id')
            ->value('id');

        $staffId = DB::table('karyawan')
            ->where('status', 'aktif')
            ->orderBy('id', 'desc')
            ->value('id');

        // ── Admin Koperasi ───────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@koperasi.com'],
            [
                'name'              => 'Admin Koperasi',
                'password'          => Hash::make('password123'),
                'role'              => 'admin',
                'karyawan_id'       => null,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles('admin');

        // ── Ketua ────────────────────────────────────────────
        $ketua = User::updateOrCreate(
            ['email' => 'ketua@koperasi.com'],
            [
                'name'              => 'Sugeng',
                'password'          => Hash::make('password123'),
                'role'              => 'admin',
                'karyawan_id'       => null,
                'email_verified_at' => now(),
            ]
        );
        $ketua->syncRoles('admin');

        // ── Mandor ───────────────────────────────────────────
        $mandor = User::updateOrCreate(
            ['email' => 'mandor@koperasi.com'],
            [
                'name'              => 'Mandor Lapangan',
                'password'          => Hash::make('password123'),
                'role'              => 'mandor',
                'karyawan_id'       => $mandorId,
                'email_verified_at' => now(),
            ]
        );
        $mandor->syncRoles('mandor');

        // ── Staff/Operator ───────────────────────────────────
        $staff = User::updateOrCreate(
            ['email' => 'staff@koperasi.com'],
            [
                'name'              => 'Staff Operator',
                'password'          => Hash::make('password123'),
                'role'              => 'staff_operator',
                'karyawan_id'       => $staffId,
                'email_verified_at' => now(),
            ]
        );
        $staff->syncRoles('staff_operator');
    }
}
