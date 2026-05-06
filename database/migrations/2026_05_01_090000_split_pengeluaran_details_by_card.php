<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $detailTables = [
        'angkutan'  => 'pengeluaran_angkutan',
        'panen'     => 'pengeluaran_panen',
        'berondol'  => 'pengeluaran_kutip_berondol',
        'perawatan' => 'pengeluaran_perawatan',
        'pupuk'     => 'pengeluaran_pupuk',
        'umum'      => 'pengeluaran_umum',
    ];

    public function up(): void
    {
        $this->addSubPengeluaranDetailType();
        $this->seedSubPengeluaranDetailTypes();
        $this->createDetailTables();
        $this->migrateLegacyPengeluaranColumnsToDetails();
        $this->dropLegacyDetailColumnsFromPengeluaran();
    }

    public function down(): void
    {
        $this->restoreLegacyDetailColumnsOnPengeluaran();
        $this->migrateDetailsBackToLegacyPengeluaranColumns();

        foreach (array_reverse($this->detailTables) as $table) {
            Schema::dropIfExists($table);
        }

        if (Schema::hasColumn('sub_pengeluaran', 'jenis_detail')) {
            Schema::table('sub_pengeluaran', function (Blueprint $table) {
                $table->dropColumn('jenis_detail');
            });
        }
    }

    private function addSubPengeluaranDetailType(): void
    {
        if (!Schema::hasColumn('sub_pengeluaran', 'jenis_detail')) {
            Schema::table('sub_pengeluaran', function (Blueprint $table) {
                $table->string('jenis_detail', 50)->default('umum')->after('nama_sub');
            });
        }
    }

    private function seedSubPengeluaranDetailTypes(): void
    {
        $kategori = DB::table('kategori_pengeluaran')->pluck('nomor_kategori', 'id');
        $subs = DB::table('sub_pengeluaran')->get();

        foreach ($subs as $sub) {
            $nomorKategori = $kategori[$sub->kategori_id] ?? null;
            $jenisDetail = $this->resolveDetailType($sub->nama_sub ?? '', $nomorKategori);

            DB::table('sub_pengeluaran')
                ->where('id', $sub->id)
                ->update([
                    'jenis_detail' => $jenisDetail,
                    'updated_at' => now(),
                ]);
        }
    }

    private function createDetailTables(): void
    {
        if (!Schema::hasTable('pengeluaran_angkutan')) {
            Schema::create('pengeluaran_angkutan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengeluaran_id')->unique()->constrained('pengeluaran')->cascadeOnDelete();
                $table->string('blok', 100)->nullable();
                $table->decimal('tonase_kg', 12, 2)->nullable();
                $table->decimal('volume', 12, 2)->nullable();
                $table->string('satuan', 30)->nullable();
                $table->decimal('harga_satuan', 18, 2)->nullable();
                $table->string('supplier_vendor', 150)->nullable();
                $table->string('no_referensi', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluaran_panen')) {
            Schema::create('pengeluaran_panen', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengeluaran_id')->unique()->constrained('pengeluaran')->cascadeOnDelete();
                $table->foreignId('mandor_id')->nullable()->constrained('karyawan')->nullOnDelete();
                $table->string('mandor', 100)->nullable();
                $table->string('blok', 100)->nullable();
                $table->unsignedInteger('jumlah_pekerja')->nullable();
                $table->decimal('luas_ha', 10, 2)->nullable();
                $table->decimal('tonase_kg', 12, 2)->nullable();
                $table->unsignedInteger('jumlah_janjang')->nullable();
                $table->decimal('brondolan_kg', 12, 2)->nullable();
                $table->decimal('volume', 12, 2)->nullable();
                $table->string('satuan', 30)->nullable();
                $table->decimal('harga_satuan', 18, 2)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluaran_kutip_berondol')) {
            Schema::create('pengeluaran_kutip_berondol', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengeluaran_id')->unique()->constrained('pengeluaran')->cascadeOnDelete();
                $table->foreignId('mandor_id')->nullable()->constrained('karyawan')->nullOnDelete();
                $table->string('mandor', 100)->nullable();
                $table->string('blok', 100)->nullable();
                $table->unsignedInteger('jumlah_pekerja')->nullable();
                $table->decimal('brondolan_kg', 12, 2)->nullable();
                $table->decimal('volume', 12, 2)->nullable();
                $table->string('satuan', 30)->nullable();
                $table->decimal('harga_satuan', 18, 2)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluaran_perawatan')) {
            Schema::create('pengeluaran_perawatan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengeluaran_id')->unique()->constrained('pengeluaran')->cascadeOnDelete();
                $table->foreignId('mandor_id')->nullable()->constrained('karyawan')->nullOnDelete();
                $table->string('mandor', 100)->nullable();
                $table->string('blok', 100)->nullable();
                $table->unsignedInteger('jumlah_pekerja')->nullable();
                $table->decimal('luas_ha', 10, 2)->nullable();
                $table->decimal('volume', 12, 2)->nullable();
                $table->string('satuan', 30)->nullable();
                $table->decimal('harga_satuan', 18, 2)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluaran_pupuk')) {
            Schema::create('pengeluaran_pupuk', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengeluaran_id')->unique()->constrained('pengeluaran')->cascadeOnDelete();
                $table->string('blok', 100)->nullable();
                $table->decimal('volume', 12, 2)->nullable();
                $table->string('satuan', 30)->nullable();
                $table->decimal('harga_satuan', 18, 2)->nullable();
                $table->string('supplier_vendor', 150)->nullable();
                $table->string('no_referensi', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluaran_umum')) {
            Schema::create('pengeluaran_umum', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengeluaran_id')->unique()->constrained('pengeluaran')->cascadeOnDelete();
                $table->decimal('volume', 12, 2)->nullable();
                $table->string('satuan', 30)->nullable();
                $table->decimal('harga_satuan', 18, 2)->nullable();
                $table->string('supplier_vendor', 150)->nullable();
                $table->string('no_referensi', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    private function migrateLegacyPengeluaranColumnsToDetails(): void
    {
        if (!Schema::hasTable('pengeluaran')) {
            return;
        }

        $rows = DB::table('pengeluaran')
            ->leftJoin('kategori_pengeluaran as kp', 'pengeluaran.kategori_id', '=', 'kp.id')
            ->leftJoin('sub_pengeluaran as sp', 'pengeluaran.sub_id', '=', 'sp.id')
            ->select('pengeluaran.*', 'kp.nomor_kategori', 'sp.nama_sub', 'sp.jenis_detail')
            ->get();

        foreach ($rows as $row) {
            $jenisDetail = $row->jenis_detail
                ?: $this->resolveDetailType($row->nama_sub ?? '', $row->nomor_kategori ?? null);
            $table = $this->detailTables[$jenisDetail] ?? $this->detailTables['umum'];

            DB::table($table)->updateOrInsert(
                ['pengeluaran_id' => $row->id],
                array_merge(
                    $this->detailPayloadFor($jenisDetail, $row),
                    [
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ],
                )
            );
        }
    }

    private function dropLegacyDetailColumnsFromPengeluaran(): void
    {
        if (Schema::hasColumn('pengeluaran', 'mandor_id')) {
            $this->dropForeignIfExists('pengeluaran', 'pengeluaran_mandor_id_foreign');
            Schema::table('pengeluaran', function (Blueprint $table) {
                $table->dropColumn('mandor_id');
            });
        }

        foreach ([
            'blok',
            'mandor',
            'jumlah_pekerja',
            'luas_ha',
            'tonase_kg',
            'jumlah_janjang',
            'brondolan_kg',
            'volume',
            'satuan',
            'harga_satuan',
            'supplier_vendor',
            'no_referensi',
        ] as $column) {
            if (Schema::hasColumn('pengeluaran', $column)) {
                Schema::table('pengeluaran', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    private function restoreLegacyDetailColumnsOnPengeluaran(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pengeluaran', 'mandor_id')) {
                $table->foreignId('mandor_id')->nullable()->after('sub_id')->constrained('karyawan')->nullOnDelete();
            }

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

    private function migrateDetailsBackToLegacyPengeluaranColumns(): void
    {
        if (!Schema::hasTable('pengeluaran')) {
            return;
        }

        $rows = DB::table('pengeluaran')
            ->leftJoin('kategori_pengeluaran as kp', 'pengeluaran.kategori_id', '=', 'kp.id')
            ->leftJoin('sub_pengeluaran as sp', 'pengeluaran.sub_id', '=', 'sp.id')
            ->select('pengeluaran.id', 'kp.nomor_kategori', 'sp.nama_sub', 'sp.jenis_detail')
            ->get();

        foreach ($rows as $row) {
            $jenisDetail = $row->jenis_detail
                ?: $this->resolveDetailType($row->nama_sub ?? '', $row->nomor_kategori ?? null);
            $table = $this->detailTables[$jenisDetail] ?? $this->detailTables['umum'];

            if (!Schema::hasTable($table)) {
                continue;
            }

            $detail = DB::table($table)->where('pengeluaran_id', $row->id)->first();

            if (!$detail) {
                continue;
            }

            DB::table('pengeluaran')->where('id', $row->id)->update([
                'mandor_id'       => $detail->mandor_id ?? null,
                'mandor'          => $detail->mandor ?? null,
                'blok'            => $detail->blok ?? null,
                'jumlah_pekerja'  => $detail->jumlah_pekerja ?? null,
                'luas_ha'         => $detail->luas_ha ?? null,
                'tonase_kg'       => $detail->tonase_kg ?? null,
                'jumlah_janjang'  => $detail->jumlah_janjang ?? null,
                'brondolan_kg'    => $detail->brondolan_kg ?? null,
                'volume'          => $detail->volume ?? null,
                'satuan'          => $detail->satuan ?? null,
                'harga_satuan'    => $detail->harga_satuan ?? null,
                'supplier_vendor' => $detail->supplier_vendor ?? null,
                'no_referensi'    => $detail->no_referensi ?? null,
            ]);
        }
    }

    private function detailPayloadFor(string $jenisDetail, object $row): array
    {
        $payloads = [
            'angkutan' => [
                'blok'            => $row->blok ?? null,
                'tonase_kg'       => $row->tonase_kg ?? null,
                'volume'          => $row->volume ?? null,
                'satuan'          => $row->satuan ?? null,
                'harga_satuan'    => $row->harga_satuan ?? null,
                'supplier_vendor' => $row->supplier_vendor ?? null,
                'no_referensi'    => $row->no_referensi ?? null,
            ],
            'panen' => [
                'mandor_id'       => $row->mandor_id ?? null,
                'mandor'          => $row->mandor ?? null,
                'blok'            => $row->blok ?? null,
                'jumlah_pekerja'  => $row->jumlah_pekerja ?? null,
                'luas_ha'         => $row->luas_ha ?? null,
                'tonase_kg'       => $row->tonase_kg ?? null,
                'jumlah_janjang'  => $row->jumlah_janjang ?? null,
                'brondolan_kg'    => $row->brondolan_kg ?? null,
                'volume'          => $row->volume ?? null,
                'satuan'          => $row->satuan ?? null,
                'harga_satuan'    => $row->harga_satuan ?? null,
            ],
            'berondol' => [
                'mandor_id'       => $row->mandor_id ?? null,
                'mandor'          => $row->mandor ?? null,
                'blok'            => $row->blok ?? null,
                'jumlah_pekerja'  => $row->jumlah_pekerja ?? null,
                'brondolan_kg'    => $row->brondolan_kg ?? null,
                'volume'          => $row->volume ?? null,
                'satuan'          => $row->satuan ?? null,
                'harga_satuan'    => $row->harga_satuan ?? null,
            ],
            'perawatan' => [
                'mandor_id'       => $row->mandor_id ?? null,
                'mandor'          => $row->mandor ?? null,
                'blok'            => $row->blok ?? null,
                'jumlah_pekerja'  => $row->jumlah_pekerja ?? null,
                'luas_ha'         => $row->luas_ha ?? null,
                'volume'          => $row->volume ?? null,
                'satuan'          => $row->satuan ?? null,
                'harga_satuan'    => $row->harga_satuan ?? null,
            ],
            'pupuk' => [
                'blok'            => $row->blok ?? null,
                'volume'          => $row->volume ?? null,
                'satuan'          => $row->satuan ?? null,
                'harga_satuan'    => $row->harga_satuan ?? null,
                'supplier_vendor' => $row->supplier_vendor ?? null,
                'no_referensi'    => $row->no_referensi ?? null,
            ],
            'umum' => [
                'volume'          => $row->volume ?? null,
                'satuan'          => $row->satuan ?? null,
                'harga_satuan'    => $row->harga_satuan ?? null,
                'supplier_vendor' => $row->supplier_vendor ?? null,
                'no_referensi'    => $row->no_referensi ?? null,
            ],
        ];

        return $payloads[$jenisDetail] ?? $payloads['umum'];
    }

    private function resolveDetailType(string $subName, ?string $nomorKategori): string
    {
        $name = strtolower($subName);

        if (str_contains($name, 'angkutan')) {
            return 'angkutan';
        }

        if (str_contains($name, 'panen')) {
            return 'panen';
        }

        if (str_contains($name, 'berondol')) {
            return 'berondol';
        }

        if ($nomorKategori === 'III') {
            return 'perawatan';
        }

        if ($nomorKategori === 'IV' || str_contains($name, 'pupuk')) {
            return 'pupuk';
        }

        return 'umum';
    }

    private function dropForeignIfExists(string $table, string $foreign): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('CONSTRAINT_NAME', $foreign)
                ->exists();

            if (!$exists) {
                return;
            }
        }

        try {
            Schema::table($table, function (Blueprint $table) use ($foreign) {
                $table->dropForeign($foreign);
            });
        } catch (Throwable) {
            //
        }
    }
};
