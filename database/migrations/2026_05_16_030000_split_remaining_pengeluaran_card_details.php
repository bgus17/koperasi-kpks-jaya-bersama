<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $splitTables = [
        'alat_berat' => 'pengeluaran_alat_berat',
        'perlengkapan' => 'pengeluaran_perlengkapan',
        'insentive' => 'pengeluaran_insentive',
    ];

    public function up(): void
    {
        $this->createSplitTables();
        $this->normalizeSubDetailTypes();
        $this->moveSharedDetailsToSplitTables();
    }

    public function down(): void
    {
        $this->moveSplitDetailsBackToSharedTables();

        foreach (array_reverse($this->splitTables) as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function createSplitTables(): void
    {
        if (!Schema::hasTable('pengeluaran_alat_berat')) {
            Schema::create('pengeluaran_alat_berat', function (Blueprint $table) {
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

        if (!Schema::hasTable('pengeluaran_perlengkapan')) {
            Schema::create('pengeluaran_perlengkapan', function (Blueprint $table) {
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

        if (!Schema::hasTable('pengeluaran_insentive')) {
            Schema::create('pengeluaran_insentive', function (Blueprint $table) {
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

    private function normalizeSubDetailTypes(): void
    {
        if (!Schema::hasTable('sub_pengeluaran') || !Schema::hasColumn('sub_pengeluaran', 'jenis_detail')) {
            return;
        }

        foreach ([
            'VI' => 'alat_berat',
            'VII' => 'perlengkapan',
            'VIII' => 'insentive',
        ] as $nomorKategori => $jenisDetail) {
            DB::table('sub_pengeluaran')
                ->join('kategori_pengeluaran', 'sub_pengeluaran.kategori_id', '=', 'kategori_pengeluaran.id')
                ->where('kategori_pengeluaran.nomor_kategori', $nomorKategori)
                ->update([
                    'sub_pengeluaran.jenis_detail' => $jenisDetail,
                    'sub_pengeluaran.updated_at' => now(),
                ]);
        }
    }

    private function moveSharedDetailsToSplitTables(): void
    {
        if (!Schema::hasTable('pengeluaran')) {
            return;
        }

        $this->movePupukLikeDetails('alat_berat');
        $this->movePupukLikeDetails('perlengkapan');
        $this->moveUmumLikeDetails('insentive');
    }

    private function movePupukLikeDetails(string $jenisDetail): void
    {
        if (!Schema::hasTable('pengeluaran_pupuk')) {
            return;
        }

        $rows = $this->rowsForDetailType($jenisDetail)
            ->join('pengeluaran_pupuk as detail', 'pengeluaran.id', '=', 'detail.pengeluaran_id')
            ->select('detail.*')
            ->get();

        foreach ($rows as $row) {
            DB::table($this->splitTables[$jenisDetail])->updateOrInsert(
                ['pengeluaran_id' => $row->pengeluaran_id],
                [
                    'blok' => $row->blok,
                    'volume' => $row->volume,
                    'satuan' => $row->satuan,
                    'harga_satuan' => $row->harga_satuan,
                    'supplier_vendor' => $row->supplier_vendor,
                    'no_referensi' => $row->no_referensi,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]
            );
        }

        DB::table('pengeluaran_pupuk')
            ->whereIn('pengeluaran_id', $rows->pluck('pengeluaran_id')->all())
            ->delete();
    }

    private function moveUmumLikeDetails(string $jenisDetail): void
    {
        if (!Schema::hasTable('pengeluaran_umum')) {
            return;
        }

        $rows = $this->rowsForDetailType($jenisDetail)
            ->join('pengeluaran_umum as detail', 'pengeluaran.id', '=', 'detail.pengeluaran_id')
            ->select('detail.*')
            ->get();

        foreach ($rows as $row) {
            DB::table($this->splitTables[$jenisDetail])->updateOrInsert(
                ['pengeluaran_id' => $row->pengeluaran_id],
                [
                    'volume' => $row->volume,
                    'satuan' => $row->satuan,
                    'harga_satuan' => $row->harga_satuan,
                    'supplier_vendor' => $row->supplier_vendor,
                    'no_referensi' => $row->no_referensi,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]
            );
        }

        DB::table('pengeluaran_umum')
            ->whereIn('pengeluaran_id', $rows->pluck('pengeluaran_id')->all())
            ->delete();
    }

    private function moveSplitDetailsBackToSharedTables(): void
    {
        if (Schema::hasTable('pengeluaran_pupuk')) {
            $this->moveSplitDetailsBack('alat_berat', 'pengeluaran_pupuk', true);
            $this->moveSplitDetailsBack('perlengkapan', 'pengeluaran_pupuk', true);
        }

        if (Schema::hasTable('pengeluaran_umum')) {
            $this->moveSplitDetailsBack('insentive', 'pengeluaran_umum', false);
        }
    }

    private function moveSplitDetailsBack(string $jenisDetail, string $targetTable, bool $withBlok): void
    {
        if (!Schema::hasTable($this->splitTables[$jenisDetail])) {
            return;
        }

        $rows = DB::table($this->splitTables[$jenisDetail])->get();

        foreach ($rows as $row) {
            $payload = [
                'pengeluaran_id' => $row->pengeluaran_id,
                'volume' => $row->volume,
                'satuan' => $row->satuan,
                'harga_satuan' => $row->harga_satuan,
                'supplier_vendor' => $row->supplier_vendor,
                'no_referensi' => $row->no_referensi,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ];

            if ($withBlok) {
                $payload['blok'] = $row->blok;
            }

            DB::table($targetTable)->updateOrInsert(
                ['pengeluaran_id' => $row->pengeluaran_id],
                $payload
            );
        }
    }

    private function rowsForDetailType(string $jenisDetail)
    {
        return DB::table('pengeluaran')
            ->join('sub_pengeluaran', 'pengeluaran.sub_id', '=', 'sub_pengeluaran.id')
            ->where('sub_pengeluaran.jenis_detail', $jenisDetail);
    }
};
