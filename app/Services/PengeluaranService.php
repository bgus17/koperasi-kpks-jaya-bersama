<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\Pengeluaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PengeluaranService
{
    public function create(array $pengeluaranData, array $detailData, array $workerDetails = []): Pengeluaran
    {
        return DB::transaction(function () use ($pengeluaranData, $detailData, $workerDetails) {
            $pengeluaran = Pengeluaran::create($pengeluaranData);
            $this->syncDetail($pengeluaran, $detailData);
            $this->syncPekerja($pengeluaran, $workerDetails);

            return $pengeluaran;
        });
    }

    public function update(Pengeluaran $pengeluaran, array $pengeluaranData, array $detailData, array $workerDetails = []): Pengeluaran
    {
        return DB::transaction(function () use ($pengeluaran, $pengeluaranData, $detailData, $workerDetails) {
            $pengeluaran->update($pengeluaranData);
            $this->syncDetail($pengeluaran, $detailData);
            $this->syncPekerja($pengeluaran, $workerDetails);

            return $pengeluaran->fresh($this->relations()) ?? $pengeluaran;
        });
    }

    public function relations(array $extra = []): array
    {
        return array_values(array_unique(array_merge([
            'kategori',
            'sub',
            'pekerjaDetail',
        ], Pengeluaran::detailRelations(), $extra)));
    }

    public function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            $query->whereHas('sub', fn (Builder $sub) => $sub->where('nama_sub', 'like', "%{$search}%"))
                ->orWhereHas('kategori', fn (Builder $kategori) => $kategori->where('nama_kategori', 'like', "%{$search}%"))
                ->orWhereHas('pekerjaDetail', fn (Builder $detail) => $detail->where('nama_karyawan_snapshot', 'like', "%{$search}%"))
                ->orWhere('keterangan', 'like', "%{$search}%");

            foreach ($this->detailSearchColumns() as $relation => $columns) {
                $query->orWhereHas($relation, function (Builder $detail) use ($columns, $search) {
                    $detail->where(function (Builder $detailQuery) use ($columns, $search) {
                        foreach ($columns as $column) {
                            $detailQuery->orWhere($column, 'like', "%{$search}%");
                        }
                    });
                });
            }
        });
    }

    public function activeWorkers(?Pengeluaran $pengeluaran = null): Collection
    {
        $includeIds = collect();

        if ($pengeluaran) {
            $includeIds = $pengeluaran->pekerjaDetail
                ->pluck('karyawan_id')
                ->filter()
                ->unique()
                ->values();
        }

        return Karyawan::query()
            ->where(function (Builder $query) use ($includeIds) {
                $query->where('status', 'aktif');

                if ($includeIds->isNotEmpty()) {
                    $query->orWhereIn('id', $includeIds);
                }
            })
            ->orderBy('nama')
            ->get();
    }

    private function detailSearchColumns(): array
    {
        return [
            'angkutanDetail' => ['blok', 'supplier_vendor', 'no_referensi'],
            'panenDetail' => ['blok', 'mandor'],
            'kutipBerondolDetail' => ['blok', 'mandor'],
            'perawatanDetail' => ['blok', 'mandor'],
            'pupukDetail' => ['blok', 'supplier_vendor', 'no_referensi'],
            'alatBeratDetail' => ['blok', 'supplier_vendor', 'no_referensi'],
            'perlengkapanDetail' => ['blok', 'supplier_vendor', 'no_referensi'],
            'insentiveDetail' => ['supplier_vendor', 'no_referensi'],
            'umumDetail' => ['supplier_vendor', 'no_referensi'],
        ];
    }

    private function syncDetail(Pengeluaran $pengeluaran, array $detailData): void
    {
        $pengeluaran->loadMissing(['kategori', 'sub']);

        $activeRelation = Pengeluaran::detailRelationForProfile($pengeluaran->detailProfile());

        foreach (Pengeluaran::detailRelations() as $relation) {
            if ($relation !== $activeRelation) {
                $pengeluaran->{$relation}()->delete();
            }
        }

        $pengeluaran->{$activeRelation}()->updateOrCreate(
            ['pengeluaran_id' => $pengeluaran->id],
            $detailData
        );

        $pengeluaran->unsetRelation($activeRelation);
    }

    private function syncPekerja(Pengeluaran $pengeluaran, array $details): void
    {
        $pengeluaran->pekerjaDetail()->delete();

        if (empty($details)) {
            return;
        }

        $karyawan = Karyawan::whereIn('id', collect($details)->pluck('karyawan_id'))
            ->get()
            ->keyBy('id');

        foreach ($details as $detail) {
            $worker = $karyawan->get($detail['karyawan_id']);

            if (! $worker) {
                continue;
            }

            $pengeluaran->pekerjaDetail()->create(array_merge($detail, [
                'nama_karyawan_snapshot' => $worker->nama,
            ]));
        }
    }
}
