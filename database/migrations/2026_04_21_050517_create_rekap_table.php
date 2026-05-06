<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->date('tanggal_tutup');
            // Tanggal penutupan buku, contoh: 31-Dec-2025
            $table->unsignedBigInteger('grand_total_debet');
            // Rp 9,567,628,135 dari gambar 1
            $table->unsignedBigInteger('grand_total_kredit');
            // Rp 7,983,409,629 dari gambar 1
            $table->unsignedBigInteger('saldo_akhir');
            // Rp 1,584,218,506 dari gambar 1
            $table->string('ketua_pengurus', 100)->nullable();
            $table->string('sekretaris', 100)->nullable();
            $table->string('bendahara', 100)->nullable();
            $table->string('ketua_badan_pengawas', 100)->nullable();
            $table->string('lokasi', 100)->nullable();
            // Contoh: 'Cahaya Mulya'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap');
    }
};