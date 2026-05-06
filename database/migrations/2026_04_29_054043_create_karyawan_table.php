<?php

// FILE: database/migrations/2026_04_29_000000_create_karyawan_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 30)->unique();                    // Nomor Induk Karyawan
            $table->string('nama', 150);
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->string('jabatan', 100)->nullable();             // Mandor, Pemanen, dll
            $table->string('divisi', 100)->nullable();              // Divisi / Bagian
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'cuti'])->default('aktif');
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('jabatan');
            $table->index('divisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};