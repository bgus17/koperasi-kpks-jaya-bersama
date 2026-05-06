<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // PERBAIKAN: pakai updateOrInsert() bukan insert()
        // Aman dijalankan berkali-kali — tidak akan error duplikat
        // ============================================================

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@koperasi.com'],
            [
                'name'              => 'Admin Koperasi',
                'password'          => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'ketua@koperasi.com'],
            [
                'name'              => 'Sugeng',
                'password'          => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );


        

        $this->call([
            PengeluaranKategoriSeeder::class,
            KeuanganSeeder::class,
        ]);
    }
}
