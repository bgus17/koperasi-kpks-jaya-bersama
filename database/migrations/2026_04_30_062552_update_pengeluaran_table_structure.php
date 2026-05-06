<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('kategori_pengeluaran')) {
            Schema::create('kategori_pengeluaran', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_kategori');
                $table->string('nama_kategori');
                $table->integer('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sub_pengeluaran')) {
            Schema::create('sub_pengeluaran', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kategori_id')->constrained('kategori_pengeluaran')->onDelete('cascade');
                $table->integer('nomor_sub');
                $table->string('nama_sub');
                $table->timestamps();
            });
        }

        $this->seedMasterData();

        if (!Schema::hasTable('pengeluaran')) {
            $this->createPengeluaranTable();
            return;
        }

        if (Schema::hasColumn('pengeluaran', 'kategori_id')) {
            return;
        }

        $oldData = DB::table('pengeluaran')->get();

        Schema::drop('pengeluaran');
        $this->createPengeluaranTable();

        foreach ($oldData as $row) {
            $kategori = DB::table('kategori_pengeluaran')
                ->where('nomor_kategori', $row->nomor_kategori ?? '')
                ->orWhere('nama_kategori', $row->kategori ?? '')
                ->first();

            if (!$kategori) {
                continue;
            }

            $sub = DB::table('sub_pengeluaran')
                ->where('kategori_id', $kategori->id)
                ->where('nama_sub', $row->sub_kategori ?? '')
                ->first();

            if (!$sub) {
                continue;
            }

            DB::table('pengeluaran')->insert([
                'kategori_id'  => $kategori->id,
                'sub_id'       => $sub->id,
                'tanggal'      => $row->tanggal,
                'jumlah'       => $row->jumlah ?? 0,
                'keterangan'   => $row->keterangan ?? null,
                'sudah_bayar'  => $row->sudah_bayar ?? false,
                'created_at'   => $row->created_at ?? now(),
                'updated_at'   => $row->updated_at ?? now(),
            ]);
        }
    }

    private function createPengeluaranTable(): void
    {
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

    private function seedMasterData(): void
    {
        $masterData = [
            ['nomor' => 'II', 'nama' => 'Biaya Produksi', 'urutan' => 1, 'subs' => [
                'Angkutan',
                'Panen',
                'Kutip Berondol',
            ]],
            ['nomor' => 'III', 'nama' => 'Biaya Perawatan', 'urutan' => 2, 'subs' => [
                'Aplikasi Insektisida Capture',
                'Aplikasi Racun Tikus',
                'Aplikasi Herbisida',
                'Sulam Tanaman Kelapa Sawit',
                'Pemupukan / Muat / Ecer Pupuk',
                'Bokor / Piringan',
                'Pembersihan Lebung',
                'Tapak Kuda',
                'Tapak Timbun',
                'Kastrasi, Sanitasi Dan Pruning',
                'Dongkel Anak Kayu',
                'Pembuatan Pasar Pikul',
                'Pembuatan TPH',
                'Penanaman Bunga / Perawatan',
                'Sensus Sawit',
            ]],
            ['nomor' => 'IV', 'nama' => 'Pembelian Pupuk', 'urutan' => 3, 'subs' => [
                'Pupuk Urea',
                'Pupuk MOP / KCL',
                'Pupuk SP-36',
                'Pupuk Dolomit',
                'Pupuk Organik',
            ]],
            ['nomor' => 'V', 'nama' => 'Biaya Umum', 'urutan' => 4, 'subs' => [
                'Administrasi',
                'Operasional Kantor',
                'Lain-lain',
            ]],
        ];

        foreach ($masterData as $item) {
            DB::table('kategori_pengeluaran')->updateOrInsert(
                ['nomor_kategori' => $item['nomor']],
                [
                    'nama_kategori' => $item['nama'],
                    'urutan' => $item['urutan'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $kategoriId = DB::table('kategori_pengeluaran')
                ->where('nomor_kategori', $item['nomor'])
                ->value('id');

            foreach ($item['subs'] as $index => $namaSub) {
                DB::table('sub_pengeluaran')->updateOrInsert(
                    [
                        'kategori_id' => $kategoriId,
                        'nama_sub' => $namaSub,
                    ],
                    [
                        'nomor_sub' => $index + 1,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
        Schema::dropIfExists('sub_pengeluaran');
        Schema::dropIfExists('kategori_pengeluaran');

        // Recreate old pengeluaran table
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kategori');
            $table->string('kategori');
            $table->string('sub_kategori');
            $table->decimal('jumlah', 18, 2)->default(0);
            $table->decimal('saldo', 18, 2)->default(0);
            $table->integer('tahun');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->integer('bulan')->nullable();
            $table->boolean('sudah_bayar')->default(false);
            $table->timestamps();
        });
    }
};
