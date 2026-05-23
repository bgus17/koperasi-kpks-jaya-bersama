<?php

namespace App\Http\Requests;

use App\Models\KategoriPengeluaran;
use App\Models\Karyawan;
use App\Models\Pengeluaran;
use App\Models\SubPengeluaran;
use App\Services\ActorAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PengeluaranRequest extends FormRequest
{
    private array $workerDetails = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $kategoriId = $this->input('kategori_id');
        if (!$kategoriId && $this->route('slug')) {
            $nomorKategori = ActorAccessService::categoryNumberForSlug((string) $this->route('slug'));

            if ($nomorKategori) {
                $kategoriId = KategoriPengeluaran::where('nomor_kategori', $nomorKategori)->value('id');
            }
        }

        if (!$kategoriId && $this->filled('nomor_kategori')) {
            $kategoriId = KategoriPengeluaran::where('nomor_kategori', trim((string) $this->input('nomor_kategori')))
                ->value('id');
        }

        $subId = $this->input('sub_id');
        $routeSubKategori = $this->route('subKategori');

        if (!$subId && $routeSubKategori && $kategoriId) {
            $subQuery = SubPengeluaran::where('kategori_id', $kategoriId);

            if (ctype_digit((string) $routeSubKategori)) {
                $subId = (clone $subQuery)->whereKey((int) $routeSubKategori)->value('id');
            } else {
                $subId = (clone $subQuery)->where('nama_sub', urldecode((string) $routeSubKategori))->value('id');
            }
        }

        if (!$subId && $this->filled('sub_kategori')) {
            $subQuery = SubPengeluaran::where('nama_sub', trim((string) $this->input('sub_kategori')));

            if ($kategoriId) {
                $subQuery->where('kategori_id', $kategoriId);
            }

            $subId = $subQuery->value('id');
        }

        $this->merge([
            'kategori_id' => $kategoriId,
            'sub_id' => $subId,
        ]);

        $workerDetails = $this->normalizeWorkerDetails();
        $workerTotals = $this->calculateWorkerTotals($workerDetails);

        $volume = $this->cleanDecimal($this->input('volume'));
        $hargaSatuan = $this->cleanMoney($this->input('harga_satuan'));
        $jumlah = $this->cleanMoney($this->input('jumlah'));

        if (!empty($workerDetails)) {
            $jumlah = $workerTotals['jumlah'];
            $volume = $workerTotals['volume'] ?: $volume;
        } elseif ($jumlah <= 0 && $volume > 0 && $hargaSatuan > 0) {
            $jumlah = (int) round($volume * $hargaSatuan);
        }

        $jenisTransaksi = $this->normalizeJenisTransaksi($this->input('jenis_transaksi'));
        $ledger = $this->ledgerValues($jenisTransaksi, $jumlah);

        [$mandorId, $mandorNama] = $this->automaticMandorSnapshot();

        $this->workerDetails = $workerDetails;

        $this->merge([
            'kategori_id'      => $kategoriId,
            'sub_id'           => $subId,
            'mandor_id'        => $mandorId,
            'mandor'           => $mandorNama,
            'jumlah'           => $jumlah,
            'jenis_transaksi'  => $jenisTransaksi,
            'debet'            => $ledger['debet'],
            'kredit'           => $ledger['kredit'],
            'saldo'            => $ledger['saldo'],
            'harga_satuan'     => $hargaSatuan ?: null,
            'volume'           => $volume ?: null,
            'luas_ha'          => !empty($workerDetails) ? ($workerTotals['luas_ha'] ?: null) : $this->nullableDecimal('luas_ha'),
            'tonase_kg'        => !empty($workerDetails) ? ($workerTotals['tonase_kg'] ?: null) : $this->nullableDecimal('tonase_kg'),
            'brondolan_kg'     => !empty($workerDetails) ? ($workerTotals['brondolan_kg'] ?: null) : $this->nullableDecimal('brondolan_kg'),
            'jumlah_pekerja'   => !empty($workerDetails) ? $workerTotals['jumlah_pekerja'] : $this->nullableInteger('jumlah_pekerja'),
            'jumlah_janjang'   => !empty($workerDetails) ? ($workerTotals['jumlah_janjang'] ?: null) : $this->nullableInteger('jumlah_janjang'),
            'blok'             => $this->nullableTrimmed('blok'),
            'satuan'           => $this->nullableTrimmed('satuan'),
            'supplier_vendor'  => $this->nullableTrimmed('supplier_vendor'),
            'no_referensi'     => $this->nullableTrimmed('no_referensi'),
            'keterangan'       => $this->nullableTrimmed('keterangan'),
            'sudah_bayar'      => $this->boolean('sudah_bayar'),
        ]);
    }

    public function rules(): array
    {
        return [
            'kategori_id'                 => 'required|integer|exists:kategori_pengeluaran,id',
            'sub_id'                      => 'required|integer|exists:sub_pengeluaran,id',
            'mandor_id'                   => 'nullable|integer|exists:karyawan,id',
            'tanggal'                     => 'required|date',
            'blok'                        => 'nullable|string|max:100',
            'mandor'                      => 'nullable|string|max:100',
            'jumlah_pekerja'              => 'nullable|integer|min:0',
            'luas_ha'                     => 'nullable|numeric|min:0',
            'tonase_kg'                   => 'nullable|numeric|min:0',
            'jumlah_janjang'              => 'nullable|integer|min:0',
            'brondolan_kg'                => 'nullable|numeric|min:0',
            'volume'                      => 'nullable|numeric|min:0',
            'satuan'                      => 'nullable|string|max:30',
            'harga_satuan'                => 'nullable|integer|min:0',
            'jumlah'                      => 'required|integer|min:1',
            'jenis_transaksi'             => 'required|in:debet,kredit,saldo',
            'debet'                       => 'nullable|integer|min:0',
            'kredit'                      => 'nullable|integer|min:0',
            'saldo'                       => 'nullable|integer',
            'supplier_vendor'             => 'nullable|string|max:150',
            'no_referensi'                => 'nullable|string|max:100',
            'keterangan'                  => 'nullable|string|max:1000',
            'sudah_bayar'                 => 'boolean',
            'pekerja'                     => 'nullable|array',
            'pekerja.*.selected'          => 'nullable',
            'pekerja.*.tonase_kg'         => 'nullable',
            'pekerja.*.jumlah_janjang'    => 'nullable',
            'pekerja.*.brondolan_kg'      => 'nullable',
            'pekerja.*.luas_ha'           => 'nullable',
            'pekerja.*.hk'                => 'nullable',
            'pekerja.*.volume'            => 'nullable',
            'pekerja.*.satuan'            => 'nullable|string|max:30',
            'pekerja.*.tarif_satuan'      => 'nullable',
            'pekerja.*.upah'              => 'nullable',
            'pekerja.*.keterangan'        => 'nullable|string|max:500',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('kategori_id') && $this->filled('sub_id')) {
                $subBelongsToCategory = SubPengeluaran::where('id', $this->input('sub_id'))
                    ->where('kategori_id', $this->input('kategori_id'))
                    ->exists();

                if (!$subBelongsToCategory) {
                    $validator->errors()->add('sub_id', 'Kegiatan harus sesuai dengan kategori pengeluaran yang dipilih.');
                }
            }

            if ($this->filled('mandor_id') && !Karyawan::aktif()->where('id', $this->input('mandor_id'))->exists()) {
                $validator->errors()->add('mandor_id', 'Mandor harus dipilih dari karyawan aktif.');
            }

            if ($this->needsWorkerDetails() && empty($this->workerDetails)) {
                $validator->errors()->add('pekerja', 'Pilih minimal satu pekerja untuk aktivitas panen, berondol, dan perawatan.');
            }

            $workerIds = collect($this->workerDetails)->pluck('karyawan_id')->all();
            if (!empty($workerIds)) {
                $activeCount = Karyawan::aktif()->whereIn('id', $workerIds)->count();

                if ($activeCount !== count($workerIds)) {
                    $validator->errors()->add('pekerja', 'Semua pekerja yang dipilih harus berstatus aktif.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Kategori pengeluaran wajib dipilih.',
            'kategori_id.exists'   => 'Kategori pengeluaran tidak ditemukan.',
            'sub_id.required'      => 'Kegiatan lapangan wajib dipilih.',
            'sub_id.exists'        => 'Kegiatan lapangan tidak ditemukan.',
            'mandor_id.exists'     => 'Mandor tidak ditemukan.',
            'tanggal.required'     => 'Tanggal wajib diisi.',
            'tanggal.date'         => 'Format tanggal tidak valid.',
            'jumlah.required'      => 'Jumlah biaya wajib diisi.',
            'jumlah.min'           => 'Jumlah biaya harus lebih dari 0.',
            'jenis_transaksi.required' => 'Jenis transaksi kas wajib dipilih.',
            'jenis_transaksi.in'       => 'Jenis transaksi kas harus Debet, Kredit, atau Saldo.',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (!is_array($validated)) {
            return $validated;
        }

        unset($validated['nomor_kategori'], $validated['sub_kategori'], $validated['pekerja']);

        return $validated;
    }

    public function pengeluaranData(): array
    {
        return $this->onlyValidated([
            'kategori_id',
            'sub_id',
            'tanggal',
            'jumlah',
            'jenis_transaksi',
            'debet',
            'kredit',
            'saldo',
            'keterangan',
            'sudah_bayar',
        ]);
    }

    public function detailData(): array
    {
        $columns = [
            'angkutan' => [
                'blok',
                'tonase_kg',
                'volume',
                'satuan',
                'harga_satuan',
                'supplier_vendor',
                'no_referensi',
            ],
            'panen' => [
                'mandor_id',
                'mandor',
                'blok',
                'jumlah_pekerja',
                'luas_ha',
                'tonase_kg',
                'jumlah_janjang',
                'brondolan_kg',
                'volume',
                'satuan',
                'harga_satuan',
            ],
            'berondol' => [
                'mandor_id',
                'mandor',
                'blok',
                'jumlah_pekerja',
                'brondolan_kg',
                'volume',
                'satuan',
                'harga_satuan',
            ],
            'perawatan' => [
                'mandor_id',
                'mandor',
                'blok',
                'jumlah_pekerja',
                'luas_ha',
                'volume',
                'satuan',
                'harga_satuan',
            ],
            'pupuk' => [
                'blok',
                'volume',
                'satuan',
                'harga_satuan',
                'supplier_vendor',
                'no_referensi',
            ],
            'alat_berat' => [
                'blok',
                'volume',
                'satuan',
                'harga_satuan',
                'supplier_vendor',
                'no_referensi',
            ],
            'perlengkapan' => [
                'blok',
                'volume',
                'satuan',
                'harga_satuan',
                'supplier_vendor',
                'no_referensi',
            ],
            'insentive' => [
                'volume',
                'satuan',
                'harga_satuan',
                'supplier_vendor',
                'no_referensi',
            ],
            'umum' => [
                'volume',
                'satuan',
                'harga_satuan',
                'supplier_vendor',
                'no_referensi',
            ],
        ];

        return $this->onlyValidated($columns[$this->detailProfile()] ?? $columns['umum']);
    }

    public function workerDetails(): array
    {
        return $this->workerDetails;
    }

    private function normalizeWorkerDetails(): array
    {
        $details = [];
        $pekerja = $this->input('pekerja', []);

        if (!is_array($pekerja)) {
            return [];
        }

        foreach ($pekerja as $karyawanId => $row) {
            if (!is_array($row) || empty($row['selected'])) {
                continue;
            }

            $tonaseKg = $this->cleanDecimal($row['tonase_kg'] ?? null);
            $jumlahJanjang = $this->cleanInteger($row['jumlah_janjang'] ?? null);
            $brondolanKg = $this->cleanDecimal($row['brondolan_kg'] ?? null);
            $luasHa = $this->cleanDecimal($row['luas_ha'] ?? null);
            $hk = $this->cleanDecimal($row['hk'] ?? null);
            $volume = $this->cleanDecimal($row['volume'] ?? null);
            $satuan = $this->nullableStringFrom($row['satuan'] ?? null, 30);
            $tarifSatuan = $this->cleanMoney($row['tarif_satuan'] ?? null);
            $upah = $this->cleanMoney($row['upah'] ?? null);

            if ($volume <= 0) {
                if ($tonaseKg > 0) {
                    $volume = $tonaseKg;
                    $satuan = $satuan ?: 'kg';
                } elseif ($hk > 0) {
                    $volume = $hk;
                    $satuan = $satuan ?: 'HK';
                } elseif ($luasHa > 0) {
                    $volume = $luasHa;
                    $satuan = $satuan ?: 'ha';
                } elseif ($brondolanKg > 0) {
                    $volume = $brondolanKg;
                    $satuan = $satuan ?: 'kg';
                }
            }

            if ($upah <= 0 && $volume > 0 && $tarifSatuan > 0) {
                $upah = (int) round($volume * $tarifSatuan);
            }

            $details[] = [
                'karyawan_id'      => (int) $karyawanId,
                'tonase_kg'        => $tonaseKg ?: null,
                'jumlah_janjang'   => $jumlahJanjang ?: null,
                'brondolan_kg'     => $brondolanKg ?: null,
                'luas_ha'          => $luasHa ?: null,
                'hk'               => $hk ?: null,
                'volume'           => $volume ?: null,
                'satuan'           => $satuan,
                'tarif_satuan'     => $tarifSatuan,
                'upah'             => $upah,
                'keterangan'       => $this->nullableStringFrom($row['keterangan'] ?? null, 500),
            ];
        }

        return $details;
    }

    private function needsWorkerDetails(): bool
    {
        return in_array($this->detailProfile(), ['panen', 'berondol', 'perawatan'], true);
    }

    private function automaticMandorSnapshot(): array
    {
        if (!$this->needsWorkerDetails()) {
            return [null, null];
        }

        $user = $this->user();
        $mandorNama = $this->nullableStringFrom($user?->name, 100);

        if (!$mandorNama) {
            $mandorId = $this->nullableInteger('mandor_id');
            $mandorNama = $this->nullableTrimmed('mandor');

            if ($mandorId) {
                $mandorNama = Karyawan::where('id', $mandorId)->value('nama') ?: $mandorNama;
            }

            return [$mandorId, $mandorNama];
        }

        $mandorId = null;
        $userKaryawanId = (int) ($user?->karyawan_id ?? 0);

        if ($userKaryawanId > 0 && Karyawan::aktif()->whereKey($userKaryawanId)->exists()) {
            $mandorId = $userKaryawanId;
        }

        if (!$mandorId) {
            $mandorId = Karyawan::aktif()->where('nama', $mandorNama)->value('id');
        }

        return [$mandorId ? (int) $mandorId : null, $mandorNama];
    }

    private function calculateWorkerTotals(array $details): array
    {
        return [
            'jumlah_pekerja'  => count($details),
            'tonase_kg'       => array_sum(array_column($details, 'tonase_kg')),
            'jumlah_janjang'  => array_sum(array_column($details, 'jumlah_janjang')),
            'brondolan_kg'    => array_sum(array_column($details, 'brondolan_kg')),
            'luas_ha'         => array_sum(array_column($details, 'luas_ha')),
            'volume'          => array_sum(array_column($details, 'volume')),
            'jumlah'          => array_sum(array_column($details, 'upah')),
        ];
    }

    private function normalizeJenisTransaksi(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['debet', 'kredit', 'saldo'], true) ? $value : 'kredit';
    }

    private function ledgerValues(string $jenisTransaksi, int $jumlah): array
    {
        return match ($jenisTransaksi) {
            'debet' => [
                'debet' => $jumlah,
                'kredit' => 0,
                'saldo' => $jumlah,
            ],
            'saldo' => [
                'debet' => 0,
                'kredit' => 0,
                'saldo' => -$jumlah,
            ],
            default => [
                'debet' => 0,
                'kredit' => $jumlah,
                'saldo' => -$jumlah,
            ],
        };
    }

    private function nullableTrimmed(string $key): ?string
    {
        return $this->nullableStringFrom($this->input($key), 1000);
    }

    private function nullableInteger(string $key): ?int
    {
        $value = $this->cleanInteger($this->input($key));

        return $value > 0 ? $value : null;
    }

    private function nullableDecimal(string $key): ?float
    {
        $value = $this->cleanDecimal($this->input($key));

        return $value > 0 ? $value : null;
    }

    private function cleanMoney(mixed $value): int
    {
        return (int) preg_replace('/\D/', '', (string) $value);
    }

    private function cleanInteger(mixed $value): int
    {
        return (int) preg_replace('/\D/', '', (string) $value);
    }

    private function cleanDecimal(mixed $value): float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0.0;
        }

        $value = str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $value));

        return (float) $value;
    }

    private function nullableStringFrom(mixed $value, int $max): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private function detailProfile(): string
    {
        if (!$this->filled('sub_id')) {
            return 'umum';
        }

        $sub = SubPengeluaran::with('kategori')->find($this->input('sub_id'));

        return $sub?->jenis_detail
            ?: Pengeluaran::resolveDetailProfile($sub?->nama_sub, $sub?->kategori?->nomor_kategori);
    }

    private function onlyValidated(array $keys): array
    {
        $validated = $this->validated();

        return array_intersect_key($validated, array_flip($keys));
    }
}
