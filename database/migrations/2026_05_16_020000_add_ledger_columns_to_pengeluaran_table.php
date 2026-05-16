<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengeluaran')) {
            return;
        }

        Schema::table('pengeluaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pengeluaran', 'jenis_transaksi')) {
                $table->string('jenis_transaksi', 20)->default('kredit')->after('jumlah');
            }

            if (!Schema::hasColumn('pengeluaran', 'debet')) {
                $table->unsignedBigInteger('debet')->default(0)->after('jenis_transaksi');
            }

            if (!Schema::hasColumn('pengeluaran', 'kredit')) {
                $table->unsignedBigInteger('kredit')->default(0)->after('debet');
            }

            if (!Schema::hasColumn('pengeluaran', 'saldo')) {
                $table->bigInteger('saldo')->default(0)->after('kredit');
            }
        });

        DB::table('pengeluaran')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $jumlah = (int) round((float) ($row->jumlah ?? 0));
                    $jenis = in_array($row->jenis_transaksi ?? null, ['debet', 'kredit', 'saldo'], true)
                        ? $row->jenis_transaksi
                        : 'kredit';

                    [$debet, $kredit, $saldo] = match ($jenis) {
                        'debet' => [$jumlah, 0, $jumlah],
                        'saldo' => [0, 0, -$jumlah],
                        default => [0, $jumlah, -$jumlah],
                    };

                    DB::table('pengeluaran')
                        ->where('id', $row->id)
                        ->update([
                            'jenis_transaksi' => $jenis,
                            'debet' => $debet,
                            'kredit' => $kredit,
                            'saldo' => $saldo,
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pengeluaran')) {
            return;
        }

        Schema::table('pengeluaran', function (Blueprint $table) {
            foreach (['saldo', 'kredit', 'debet', 'jenis_transaksi'] as $column) {
                if (Schema::hasColumn('pengeluaran', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
