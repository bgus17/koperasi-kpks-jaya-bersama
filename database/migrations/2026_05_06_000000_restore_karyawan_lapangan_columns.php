<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('karyawan', 'tanggal_masuk')) {
            Schema::table('karyawan', function (Blueprint $table) {
                $table->date('tanggal_masuk')->nullable()->after('alamat');
            });
        }

        $this->dropLegacyColumnsIfPresent();
    }

    public function down(): void
    {
        // No-op: tanggal_masuk adalah bagian dari skema karyawan yang masih dipakai form.
    }

    private function dropLegacyColumnsIfPresent(): void
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
