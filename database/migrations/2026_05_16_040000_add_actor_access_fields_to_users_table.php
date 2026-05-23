<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('admin')->after('password');
            }

            if (!Schema::hasColumn('users', 'karyawan_id')) {
                $table->foreignId('karyawan_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('karyawan')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'karyawan_id')) {
                $table->dropConstrainedForeignId('karyawan_id');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
