<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    public const JENIS_TRANSAKSI = [
        'debet' => 'Debet',
        'kredit' => 'Kredit',
        'saldo' => 'Saldo',
    ];

    public const DETAIL_RELATIONS = [
        'angkutan' => 'angkutanDetail',
        'panen' => 'panenDetail',
        'berondol' => 'kutipBerondolDetail',
        'perawatan' => 'perawatanDetail',
        'pupuk' => 'pupukDetail',
        'alat_berat' => 'alatBeratDetail',
        'perlengkapan' => 'perlengkapanDetail',
        'insentive' => 'insentiveDetail',
        'umum' => 'umumDetail',
    ];

    protected $fillable = [
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
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'debet' => 'integer',
        'kredit' => 'integer',
        'saldo' => 'integer',
        'tanggal' => 'date',
        'sudah_bayar' => 'boolean',
    ];

    protected $appends = [
        'nomor_kategori',
        'nama_kategori',
        'sub_kategori',
        'tahun',
        'bulan',
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
        'supplier_vendor',
        'no_referensi',
        'jenis_transaksi_label',
    ];

    // ── Relationships ─────────────────────────────────────

    public function kategori()
    {
        return $this->belongsTo(KategoriPengeluaran::class, 'kategori_id');
    }

    public function sub()
    {
        return $this->belongsTo(SubPengeluaran::class, 'sub_id');
    }

    public function angkutanDetail()
    {
        return $this->hasOne(PengeluaranAngkutan::class, 'pengeluaran_id');
    }

    public function panenDetail()
    {
        return $this->hasOne(PengeluaranPanen::class, 'pengeluaran_id');
    }

    public function kutipBerondolDetail()
    {
        return $this->hasOne(PengeluaranKutipBerondol::class, 'pengeluaran_id');
    }

    public function perawatanDetail()
    {
        return $this->hasOne(PengeluaranPerawatan::class, 'pengeluaran_id');
    }

    public function pupukDetail()
    {
        return $this->hasOne(PengeluaranPupuk::class, 'pengeluaran_id');
    }

    public function alatBeratDetail()
    {
        return $this->hasOne(PengeluaranAlatBerat::class, 'pengeluaran_id');
    }

    public function perlengkapanDetail()
    {
        return $this->hasOne(PengeluaranPerlengkapan::class, 'pengeluaran_id');
    }

    public function insentiveDetail()
    {
        return $this->hasOne(PengeluaranInsentive::class, 'pengeluaran_id');
    }

    public function umumDetail()
    {
        return $this->hasOne(PengeluaranUmum::class, 'pengeluaran_id');
    }

    public function pekerjaDetail()
    {
        return $this->hasMany(PengeluaranKaryawan::class, 'pengeluaran_id');
    }

    // ── Accessor & Mutator ────────────────────────────────────

    /**
     * Get tahun (year from tanggal).
     */
    public function getTahunAttribute()
    {
        return $this->tanggal?->year;
    }

    /**
     * Get bulan (month from tanggal).
     */
    public function getBulanAttribute()
    {
        return $this->tanggal?->month;
    }

    public function getNomorKategoriAttribute($value = null): ?string
    {
        if ($value !== null) {
            return (string) $value;
        }

        $kategori = $this->kategoriModel();

        return $kategori?->nomor_kategori;
    }

    public function getNamaKategoriAttribute($value = null): ?string
    {
        if ($value !== null) {
            return (string) $value;
        }

        if (isset($this->attributes['kategori']) && is_scalar($this->attributes['kategori'])) {
            return (string) $this->attributes['kategori'];
        }

        $kategori = $this->kategoriModel();

        return $kategori?->nama_kategori;
    }

    public function getSubKategoriAttribute($value = null): ?string
    {
        if ($value !== null) {
            return (string) $value;
        }

        if (isset($this->attributes['sub']) && is_scalar($this->attributes['sub'])) {
            return (string) $this->attributes['sub'];
        }

        $sub = $this->subModel();

        return $sub?->nama_sub;
    }

    public function getMandorIdAttribute($value): ?int
    {
        return $value !== null ? (int) $value : $this->integerDetailValue('mandor_id');
    }

    public function getMandorAttribute($value): ?string
    {
        return $value ?? $this->stringDetailValue('mandor');
    }

    public function getBlokAttribute($value): ?string
    {
        return $value ?? $this->stringDetailValue('blok');
    }

    public function getJumlahPekerjaAttribute($value): ?int
    {
        return $value !== null ? (int) $value : $this->integerDetailValue('jumlah_pekerja');
    }

    public function getLuasHaAttribute($value): ?float
    {
        return $value !== null ? (float) $value : $this->decimalDetailValue('luas_ha');
    }

    public function getTonaseKgAttribute($value): ?float
    {
        return $value !== null ? (float) $value : $this->decimalDetailValue('tonase_kg');
    }

    public function getJumlahJanjangAttribute($value): ?int
    {
        return $value !== null ? (int) $value : $this->integerDetailValue('jumlah_janjang');
    }

    public function getBrondolanKgAttribute($value): ?float
    {
        return $value !== null ? (float) $value : $this->decimalDetailValue('brondolan_kg');
    }

    public function getVolumeAttribute($value): ?float
    {
        return $value !== null ? (float) $value : $this->decimalDetailValue('volume');
    }

    public function getSatuanAttribute($value): ?string
    {
        return $value ?? $this->stringDetailValue('satuan');
    }

    public function getHargaSatuanAttribute($value): ?int
    {
        return $value !== null ? (int) $value : $this->integerDetailValue('harga_satuan');
    }

    public function getSupplierVendorAttribute($value): ?string
    {
        return $value ?? $this->stringDetailValue('supplier_vendor');
    }

    public function getNoReferensiAttribute($value): ?string
    {
        return $value ?? $this->stringDetailValue('no_referensi');
    }

    public function getJenisTransaksiLabelAttribute(): string
    {
        return self::JENIS_TRANSAKSI[$this->jenis_transaksi ?? 'kredit'] ?? 'Kredit';
    }

    public function getMandorKaryawanAttribute(): ?Karyawan
    {
        $detail = $this->activeDetailModel();

        if ($detail && method_exists($detail, 'mandorKaryawan')) {
            return $detail->mandorKaryawan;
        }

        return $this->mandor_id ? Karyawan::find($this->mandor_id) : null;
    }

    public function detailProfile(): string
    {
        $sub = $this->subModel();
        $kategori = $this->kategoriModel();

        return $sub?->jenis_detail
            ?: self::resolveDetailProfile($sub?->nama_sub, $kategori?->nomor_kategori ?? $this->nomor_kategori);
    }

    public static function detailRelations(): array
    {
        return array_values(array_unique(self::DETAIL_RELATIONS));
    }

    public static function detailRelationForProfile(string $profile): string
    {
        return self::DETAIL_RELATIONS[$profile] ?? self::DETAIL_RELATIONS['umum'];
    }

    public static function resolveDetailProfile(?string $subName, ?string $categoryNumber): string
    {
        $name = strtolower((string) $subName);

        if (str_contains($name, 'angkutan')) {
            return 'angkutan';
        }

        if (str_contains($name, 'panen')) {
            return 'panen';
        }

        if (str_contains($name, 'berondol')) {
            return 'berondol';
        }

        if ($categoryNumber === 'III') {
            return 'perawatan';
        }

        if ($categoryNumber === 'IV' || str_contains($name, 'pupuk')) {
            return 'pupuk';
        }

        if ($categoryNumber === 'VI' || str_contains($name, 'traktor') || str_contains($name, 'grader') || str_contains($name, 'compactor')) {
            return 'alat_berat';
        }

        if ($categoryNumber === 'VII') {
            return 'perlengkapan';
        }

        if ($categoryNumber === 'VIII') {
            return 'insentive';
        }

        return 'umum';
    }

    private function activeDetailModel(): ?Model
    {
        $relation = self::detailRelationForProfile($this->detailProfile());

        if ($this->relationLoaded($relation)) {
            return $this->getRelation($relation);
        }

        return $this->{$relation};
    }

    private function kategoriModel(): ?KategoriPengeluaran
    {
        if ($this->relationLoaded('kategori')) {
            $kategori = $this->getRelation('kategori');

            return $kategori instanceof KategoriPengeluaran ? $kategori : null;
        }

        if (! array_key_exists('kategori_id', $this->attributes) || $this->attributes['kategori_id'] === null) {
            return null;
        }

        return $this->kategori()->first();
    }

    private function subModel(): ?SubPengeluaran
    {
        if ($this->relationLoaded('sub')) {
            $sub = $this->getRelation('sub');

            return $sub instanceof SubPengeluaran ? $sub : null;
        }

        if (! array_key_exists('sub_id', $this->attributes) || $this->attributes['sub_id'] === null) {
            return null;
        }

        return $this->sub()->first();
    }

    private function detailValue(string $column): mixed
    {
        $detail = $this->activeDetailModel();

        return $detail?->{$column};
    }

    private function stringDetailValue(string $column): ?string
    {
        $value = $this->detailValue($column);

        return $value !== null && $value !== '' ? (string) $value : null;
    }

    private function integerDetailValue(string $column): ?int
    {
        $value = $this->detailValue($column);

        return $value !== null && $value !== '' ? (int) $value : null;
    }

    private function decimalDetailValue(string $column): ?float
    {
        $value = $this->detailValue($column);

        return $value !== null && $value !== '' ? (float) $value : null;
    }
}
