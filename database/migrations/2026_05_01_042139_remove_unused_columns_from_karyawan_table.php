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
        $columns = collect(['nik', 'jabatan', 'divisi', 'gaji_pokok'])
            ->filter(fn ($column) => Schema::hasColumn('karyawan', $column))
            ->values()
            ->all();

        if (!empty($columns)) {
            Schema::table('karyawan', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawan', 'nik')) {
                $table->string('nik')->nullable();
            }

            if (!Schema::hasColumn('karyawan', 'jabatan')) {
                $table->string('jabatan')->nullable();
            }

            if (!Schema::hasColumn('karyawan', 'divisi')) {
                $table->string('divisi')->nullable();
            }

            if (!Schema::hasColumn('karyawan', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->default(0);
            }
        });
    }
};
