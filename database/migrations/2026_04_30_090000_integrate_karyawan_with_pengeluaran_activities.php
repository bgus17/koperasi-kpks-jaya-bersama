<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pengeluaran', 'mandor_id')) {
                $table->foreignId('mandor_id')
                    ->nullable()
                    ->after('sub_id')
                    ->constrained('karyawan')
                    ->nullOnDelete();
            }
        });

        if (!Schema::hasTable('pengeluaran_karyawan')) {
            Schema::create('pengeluaran_karyawan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengeluaran_id')->constrained('pengeluaran')->cascadeOnDelete();
                $table->foreignId('karyawan_id')->nullable()->constrained('karyawan')->nullOnDelete();
                $table->string('nama_karyawan_snapshot', 150);
                $table->decimal('tonase_kg', 12, 2)->nullable();
                $table->unsignedInteger('jumlah_janjang')->nullable();
                $table->decimal('brondolan_kg', 12, 2)->nullable();
                $table->decimal('luas_ha', 10, 2)->nullable();
                $table->decimal('hk', 8, 2)->nullable();
                $table->decimal('volume', 12, 2)->nullable();
                $table->string('satuan', 30)->nullable();
                $table->decimal('tarif_satuan', 18, 2)->default(0);
                $table->decimal('upah', 18, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->unique(['pengeluaran_id', 'karyawan_id']);
            });
        }

        $this->dropKaryawanLegacyColumns();
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_karyawan');

        Schema::table('pengeluaran', function (Blueprint $table) {
            if (Schema::hasColumn('pengeluaran', 'mandor_id')) {
                $table->dropConstrainedForeignId('mandor_id');
            }
        });

        Schema::table('karyawan', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawan', 'nik')) {
                $table->string('nik', 30)->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('karyawan', 'jabatan')) {
                $table->string('jabatan', 100)->nullable()->after('jenis_kelamin');
                $table->index('jabatan');
            }

            if (!Schema::hasColumn('karyawan', 'divisi')) {
                $table->string('divisi', 100)->nullable()->after('jabatan');
                $table->index('divisi');
            }

            if (!Schema::hasColumn('karyawan', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->default(0)->after('status');
            }
        });
    }

    private function dropKaryawanLegacyColumns(): void
    {
        $dropNikUnique = Schema::hasColumn('karyawan', 'nik') && $this->indexExists('karyawan', 'karyawan_nik_unique');
        $dropJabatanIndex = Schema::hasColumn('karyawan', 'jabatan') && $this->indexExists('karyawan', 'karyawan_jabatan_index');
        $dropDivisiIndex = Schema::hasColumn('karyawan', 'divisi') && $this->indexExists('karyawan', 'karyawan_divisi_index');

        Schema::table('karyawan', function (Blueprint $table) use ($dropNikUnique, $dropJabatanIndex, $dropDivisiIndex) {
            if ($dropNikUnique) {
                $table->dropUnique('karyawan_nik_unique');
            }

            if ($dropJabatanIndex) {
                $table->dropIndex('karyawan_jabatan_index');
            }

            if ($dropDivisiIndex) {
                $table->dropIndex('karyawan_divisi_index');
            }
        });

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

    private function indexExists(string $table, string $index): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($row) => ($row->name ?? null) === $index);
        }

        return !empty(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]));
    }
};
