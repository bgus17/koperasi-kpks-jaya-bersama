<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pengeluaran', 'blok')) {
                $table->string('blok', 100)->nullable()->after('tanggal');
            }

            if (!Schema::hasColumn('pengeluaran', 'mandor')) {
                $table->string('mandor', 100)->nullable()->after('blok');
            }

            if (!Schema::hasColumn('pengeluaran', 'jumlah_pekerja')) {
                $table->unsignedInteger('jumlah_pekerja')->nullable()->after('mandor');
            }

            if (!Schema::hasColumn('pengeluaran', 'luas_ha')) {
                $table->decimal('luas_ha', 10, 2)->nullable()->after('jumlah_pekerja');
            }

            if (!Schema::hasColumn('pengeluaran', 'tonase_kg')) {
                $table->decimal('tonase_kg', 12, 2)->nullable()->after('luas_ha');
            }

            if (!Schema::hasColumn('pengeluaran', 'jumlah_janjang')) {
                $table->unsignedInteger('jumlah_janjang')->nullable()->after('tonase_kg');
            }

            if (!Schema::hasColumn('pengeluaran', 'brondolan_kg')) {
                $table->decimal('brondolan_kg', 12, 2)->nullable()->after('jumlah_janjang');
            }

            if (!Schema::hasColumn('pengeluaran', 'volume')) {
                $table->decimal('volume', 12, 2)->nullable()->after('brondolan_kg');
            }

            if (!Schema::hasColumn('pengeluaran', 'satuan')) {
                $table->string('satuan', 30)->nullable()->after('volume');
            }

            if (!Schema::hasColumn('pengeluaran', 'harga_satuan')) {
                $table->decimal('harga_satuan', 18, 2)->nullable()->after('satuan');
            }

            if (!Schema::hasColumn('pengeluaran', 'supplier_vendor')) {
                $table->string('supplier_vendor', 150)->nullable()->after('harga_satuan');
            }

            if (!Schema::hasColumn('pengeluaran', 'no_referensi')) {
                $table->string('no_referensi', 100)->nullable()->after('supplier_vendor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            foreach ([
                'no_referensi',
                'supplier_vendor',
                'harga_satuan',
                'satuan',
                'volume',
                'brondolan_kg',
                'jumlah_janjang',
                'tonase_kg',
                'luas_ha',
                'jumlah_pekerja',
                'mandor',
                'blok',
            ] as $column) {
                if (Schema::hasColumn('pengeluaran', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
