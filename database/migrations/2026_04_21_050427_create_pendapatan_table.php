<?php
// database/migrations/2026_04_18_000000_create_pendapatan_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendapatan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kategori', 10)->nullable();
            $table->string('kategori', 100);
            $table->string('sub_kategori', 200)->nullable();
            $table->unsignedBigInteger('debet')->default(0);
            $table->unsignedBigInteger('kredit')->default(0);
            $table->unsignedBigInteger('saldo')->default(0);
            $table->integer('tahun');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index('tahun');
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendapatan');
    }
};