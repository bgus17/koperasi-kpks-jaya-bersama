<?php

// FILE: database/migrations/xxxx_create_pengeluaran_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel master kategori pengeluaran
        Schema::create('kategori_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kategori');       // e.g. "II", "III", "IV"
            $table->string('nama_kategori');         // e.g. "Biaya Produksi"
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Tabel master sub-item pengeluaran
        Schema::create('sub_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori_pengeluaran')->onDelete('cascade');
            $table->integer('nomor_sub');           // e.g. 1, 2, 3
            $table->string('nama_sub');              // e.g. "Angkutan", "Panen"
            $table->timestamps();
        });

        // Tabel transaksi pengeluaran harian
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori_pengeluaran');
            $table->foreignId('sub_id')->constrained('sub_pengeluaran');
            $table->date('tanggal');
            $table->integer('tahun')->storedAs('YEAR(tanggal)');
            $table->integer('bulan')->storedAs('MONTH(tanggal)');
            $table->decimal('jumlah', 18, 2)->default(0);   // kredit / pengeluaran
            $table->text('keterangan')->nullable();
            $table->boolean('sudah_bayar')->default(false);  // tanda centang
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
        Schema::dropIfExists('sub_pengeluaran');
        Schema::dropIfExists('kategori_pengeluaran');
    }
};