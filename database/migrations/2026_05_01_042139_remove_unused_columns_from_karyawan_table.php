<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['nik', 'jabatan', 'divisi', 'tanggal_masuk']);
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('nik')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('divisi')->nullable();
            $table->date('tanggal_masuk')->nullable();
        });
    }
};
