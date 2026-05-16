{{-- Partial form pengeluaran operasional sawit. --}}
{{-- Variabel: $p, $kategoriList, $kategori, $sub, $formContext, $karyawanAktif --}}

@php
    $p = $p ?? null;
    $kategori = $kategori ?? null;
    $sub = $sub ?? null;
    $karyawanAktif = collect($karyawanAktif ?? []);
    $formContext = $formContext ?? [
        'profile' => 'umum',
        'title' => 'Detail Operasional',
        'metricLabel' => 'Volume',
        'metricHint' => 'Isi volume kerja, unit barang, ritase, atau jumlah transaksi sesuai kegiatan.',
        'defaultSatuan' => '',
    ];

    $locked = $kategori && $sub;
    $selectedKategoriId = (string) old('kategori_id', $kategori?->id ?? $p?->kategori_id ?? '');
    $selectedSubId = (string) old('sub_id', $sub?->id ?? $p?->sub_id ?? '');
    $selectedSatuan = old('satuan', $p?->satuan ?? $formContext['defaultSatuan'] ?? '');
    $mandorOtomatis = trim((string) (auth()->user()?->name ?? old('mandor', $p?->mandor ?? '')));

    $oldPekerja = old('pekerja');
    $oldPekerja = is_array($oldPekerja) ? $oldPekerja : null;
    $detailMap = collect($p?->pekerjaDetail ?? [])->keyBy('karyawan_id');

    $workerValue = function ($karyawanId, string $field, $default = '') use ($oldPekerja, $detailMap) {
        if ($oldPekerja !== null) {
            return $oldPekerja[$karyawanId][$field] ?? $default;
        }

        return $detailMap->get($karyawanId)?->{$field} ?? $default;
    };

    $workerChecked = function ($karyawanId) use ($oldPekerja, $detailMap) {
        if ($oldPekerja !== null) {
            return !empty($oldPekerja[$karyawanId]['selected']);
        }

        return $detailMap->has($karyawanId);
    };
@endphp

@if($errors->any())
    <div class="form-alert">
        <strong>Ada kesalahan:</strong>
        <ul>
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="activity-form"
     data-activity-form
     data-initial-profile="{{ $formContext['profile'] }}"
     data-initial-sub="{{ $sub?->nama_sub ?? $p?->sub?->nama_sub }}"
     data-initial-category="{{ $kategori?->nomor_kategori ?? $p?->kategori?->nomor_kategori }}"
     data-initial-detail-type="{{ $sub?->jenis_detail ?? $p?->sub?->jenis_detail }}">

    <div class="form-section">
        <div class="form-section-title">Kegiatan</div>
        <div class="form-grid">
            @if($locked)
                <input type="hidden" name="kategori_id" value="{{ $kategori->id }}">
                <input type="hidden" name="sub_id" value="{{ $sub->id }}">

                <div class="form-group">
                    <label>Kategori Biaya</label>
                    <div class="readonly-field">{{ $kategori->nomor_kategori }} - {{ $kategori->nama_kategori }}</div>
                </div>

                <div class="form-group">
                    <label>Kegiatan Lapangan</label>
                    <div class="readonly-field">{{ $sub->nama_sub }}</div>
                </div>
            @else
                <div class="form-group">
                    <label for="kategori_id">Kategori Biaya *</label>
                    <select name="kategori_id" id="kategori_id" data-kategori-select required>
                        <option value="">Pilih kategori</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat->id }}"
                                    data-category-number="{{ $kat->nomor_kategori }}"
                                    {{ $selectedKategoriId === (string) $kat->id ? 'selected' : '' }}>
                                {{ $kat->nomor_kategori }} - {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="sub_id">Kegiatan Lapangan *</label>
                    <select name="sub_id" id="sub_id" data-sub-select required>
                        <option value="">Pilih kegiatan</option>
                        @foreach($kategoriList as $kat)
                            @foreach($kat->subPengeluaran as $item)
                                <option value="{{ $item->id }}"
                                        data-kategori-id="{{ $kat->id }}"
                                        data-category-number="{{ $kat->nomor_kategori }}"
                                        data-sub-name="{{ $item->nama_sub }}"
                                        data-detail-type="{{ $item->jenis_detail }}"
                                        {{ $selectedSubId === (string) $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_sub }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('sub_id')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            @endif

            <div class="form-group">
                <label for="tanggal">Tanggal Kegiatan *</label>
                <input type="date" id="tanggal" name="tanggal"
                       value="{{ old('tanggal', $p?->tanggal?->format('Y-m-d') ?? date('Y-m-d')) }}"
                       required>
                @error('tanggal')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="sudah_bayar">Status Pembayaran</label>
                <label class="check-row">
                    <input type="checkbox" id="sudah_bayar" name="sudah_bayar" value="1"
                           {{ old('sudah_bayar', $p?->sudah_bayar) ? 'checked' : '' }}>
                    Sudah dibayar
                </label>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-title" data-profile-title>{{ $formContext['title'] }}</div>
        <div class="form-hint" data-profile-note>{{ $formContext['metricHint'] }}</div>

        <input type="hidden" name="mandor" value="{{ $mandorOtomatis }}">

        <div class="form-grid">
            <div class="form-group activity-field" data-profiles="panen berondol perawatan">
                <label>Mandor / Pengawas Lapangan</label>
                <div class="readonly-field">{{ $mandorOtomatis ?: 'Mengikuti akun login' }}</div>
                @error('mandor')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group activity-field" data-profiles="panen angkutan berondol perawatan pupuk">
                <label for="blok">Blok / Afdeling / Lokasi</label>
                <input type="text" id="blok" name="blok"
                       value="{{ old('blok', $p?->blok) }}"
                       placeholder="Contoh: Blok A12 / Afdeling II">
                @error('blok')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group activity-field" data-profiles="panen berondol perawatan">
                <label for="jumlah_pekerja">Jumlah Pekerja Terpilih</label>
                <input type="number" id="jumlah_pekerja" name="jumlah_pekerja"
                       value="{{ old('jumlah_pekerja', $p?->jumlah_pekerja) }}"
                       min="0" step="1" placeholder="0" data-worker-count readonly>
                @error('jumlah_pekerja')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group activity-field" data-profiles="perawatan">
                <label for="luas_ha">Total Luas Kerja (Ha)</label>
                <input type="number" id="luas_ha" name="luas_ha"
                       value="{{ old('luas_ha', $p?->luas_ha) }}"
                       min="0" step="0.01" placeholder="0.00" data-header-luas>
                @error('luas_ha')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group activity-field" data-profiles="panen angkutan">
                <label for="tonase_kg">Total Tonase TBS (Kg)</label>
                <input type="number" id="tonase_kg" name="tonase_kg"
                       value="{{ old('tonase_kg', $p?->tonase_kg) }}"
                       min="0" step="0.01" placeholder="0" data-header-tonase>
                @error('tonase_kg')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group activity-field" data-profiles="panen">
                <label for="jumlah_janjang">Total Janjang</label>
                <input type="number" id="jumlah_janjang" name="jumlah_janjang"
                       value="{{ old('jumlah_janjang', $p?->jumlah_janjang) }}"
                       min="0" step="1" placeholder="0" data-header-janjang>
                @error('jumlah_janjang')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group activity-field" data-profiles="panen berondol">
                <label for="brondolan_kg">Total Brondolan (Kg)</label>
                <input type="number" id="brondolan_kg" name="brondolan_kg"
                       value="{{ old('brondolan_kg', $p?->brondolan_kg) }}"
                       min="0" step="0.01" placeholder="0" data-header-brondolan>
                @error('brondolan_kg')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group activity-field" data-profiles="angkutan pupuk umum">
                <label for="supplier_vendor">Vendor / Sopir / Supplier</label>
                <input type="text" id="supplier_vendor" name="supplier_vendor"
                       value="{{ old('supplier_vendor', $p?->supplier_vendor) }}"
                       placeholder="Nama vendor, sopir, atau toko">
                @error('supplier_vendor')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group activity-field" data-profiles="angkutan pupuk umum">
                <label for="no_referensi">No. SPB / Nota / Referensi</label>
                <input type="text" id="no_referensi" name="no_referensi"
                       value="{{ old('no_referensi', $p?->no_referensi) }}"
                       placeholder="Contoh: SPB-001 / INV-2026-01">
                @error('no_referensi')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="form-section activity-field" data-profiles="panen berondol perawatan umum">
        <div class="form-section-title">Pekerja Lapangan</div>
        <div class="form-hint">Centang minimal satu pekerja dari database karyawan, lalu isi hasil kerja dan upah per orang.</div>

        <div class="worker-toolbar">
            <input type="text" data-worker-search placeholder="Cari nama karyawan...">
            <span data-worker-summary>0 pekerja dipilih</span>
        </div>

        <div class="worker-list">
            @forelse($karyawanAktif as $karyawan)
                @php
                    $checked = $workerChecked($karyawan->id);
                    $baseName = "pekerja[{$karyawan->id}]";
                @endphp
                <div class="worker-row {{ $checked ? 'is-selected' : '' }}"
                     data-worker-row
                     data-worker-name="{{ strtolower($karyawan->nama) }}">
                    <label class="worker-check">
                        <input type="checkbox"
                               name="{{ $baseName }}[selected]"
                               value="1"
                               data-worker-toggle
                               {{ $checked ? 'checked' : '' }}>
                        <span>
                            <strong>{{ $karyawan->nama }}</strong>
                            <small>{{ $karyawan->jenis_kelamin_label }}{{ $karyawan->no_hp ? ' - ' . $karyawan->no_hp : '' }}{{ $karyawan->status !== 'aktif' ? ' - non-aktif' : '' }}</small>
                        </span>
                    </label>

                    <div class="worker-fields">
                        <div class="worker-field" data-worker-profiles="panen angkutan">
                            <label>TBS (Kg)</label>
                            <input type="number" name="{{ $baseName }}[tonase_kg]"
                                   value="{{ $workerValue($karyawan->id, 'tonase_kg') }}"
                                   min="0" step="0.01" data-worker-tonase>
                        </div>
                        <div class="worker-field" data-worker-profiles="panen">
                            <label>Janjang</label>
                            <input type="number" name="{{ $baseName }}[jumlah_janjang]"
                                   value="{{ $workerValue($karyawan->id, 'jumlah_janjang') }}"
                                   min="0" step="1" data-worker-janjang>
                        </div>
                        <div class="worker-field" data-worker-profiles="panen berondol">
                            <label>Brondolan (Kg)</label>
                            <input type="number" name="{{ $baseName }}[brondolan_kg]"
                                   value="{{ $workerValue($karyawan->id, 'brondolan_kg') }}"
                                   min="0" step="0.01" data-worker-brondolan>
                        </div>
                        <div class="worker-field" data-worker-profiles="perawatan">
                            <label>HK</label>
                            <input type="number" name="{{ $baseName }}[hk]"
                                   value="{{ $workerValue($karyawan->id, 'hk') }}"
                                   min="0" step="0.01" data-worker-hk>
                        </div>
                        <div class="worker-field" data-worker-profiles="perawatan">
                            <label>Luas (Ha)</label>
                            <input type="number" name="{{ $baseName }}[luas_ha]"
                                   value="{{ $workerValue($karyawan->id, 'luas_ha') }}"
                                   min="0" step="0.01" data-worker-luas>
                        </div>
                        <div class="worker-field" data-worker-profiles="perawatan umum">
                            <label>Volume</label>
                            <input type="number" name="{{ $baseName }}[volume]"
                                   value="{{ $workerValue($karyawan->id, 'volume') }}"
                                   min="0" step="0.01" data-worker-volume>
                        </div>
                        <div class="worker-field" data-worker-profiles="perawatan umum">
                            <label>Satuan</label>
                            <select name="{{ $baseName }}[satuan]" data-worker-satuan>
                                <option value="">-</option>
                                @foreach(['kg', 'ton', 'janjang', 'HK', 'ha', 'liter', 'sak', 'rit', 'unit', 'hari'] as $satuan)
                                    <option value="{{ $satuan }}" {{ $workerValue($karyawan->id, 'satuan') === $satuan ? 'selected' : '' }}>{{ $satuan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="worker-field" data-worker-profiles="panen berondol perawatan umum">
                            <label>Tarif (Rp)</label>
                            <input type="text" name="{{ $baseName }}[tarif_satuan]"
                                   value="{{ $workerValue($karyawan->id, 'tarif_satuan') }}"
                                   class="rupiah-input" data-worker-rate>
                        </div>
                        <div class="worker-field" data-worker-profiles="panen berondol perawatan umum">
                            <label>Upah (Rp)</label>
                            <input type="text" name="{{ $baseName }}[upah]"
                                   value="{{ $workerValue($karyawan->id, 'upah') }}"
                                   class="rupiah-input" data-worker-upah>
                        </div>
                        <div class="worker-field worker-note" data-worker-profiles="panen berondol perawatan umum">
                            <label>Catatan</label>
                            <input type="text" name="{{ $baseName }}[keterangan]"
                                   value="{{ $workerValue($karyawan->id, 'keterangan') }}"
                                   maxlength="500">
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-worker">Belum ada karyawan aktif. Tambahkan data karyawan terlebih dahulu.</div>
            @endforelse
        </div>
        @error('pekerja')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-section">
        <div class="form-section-title">Perhitungan Biaya</div>
        <div class="form-grid">
            <div class="form-group">
                <label for="volume" data-volume-label>{{ $formContext['metricLabel'] }}</label>
                <input type="number" id="volume" name="volume" data-cost-volume data-header-volume
                       value="{{ old('volume', $p?->volume) }}"
                       min="0" step="0.01" placeholder="0">
                <small class="field-note">Jika pekerja dipilih, nilai ini mengikuti total detail pekerja.</small>
                @error('volume')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="satuan">Satuan</label>
                <select id="satuan" name="satuan" data-satuan-select>
                    <option value="">Pilih satuan</option>
                    @foreach(['kg', 'ton', 'janjang', 'HK', 'ha', 'liter', 'sak', 'rit', 'unit', 'hari'] as $satuan)
                        <option value="{{ $satuan }}" {{ $selectedSatuan === $satuan ? 'selected' : '' }}>{{ $satuan }}</option>
                    @endforeach
                </select>
                @error('satuan')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="harga_satuan">Tarif / Harga Satuan (Rp)</label>
                <input type="text" id="harga_satuan" name="harga_satuan"
                       class="rupiah-input" data-cost-rate
                       value="{{ old('harga_satuan', $p?->harga_satuan) }}"
                       placeholder="0">
                @error('harga_satuan')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Biaya (Rp) *</label>
                <input type="text" id="jumlah" name="jumlah"
                       class="rupiah-input" data-cost-total
                       value="{{ old('jumlah', $p?->jumlah) }}"
                       placeholder="0" required>
                <small class="field-note">Jika pekerja dipilih, total dihitung dari upah pekerja.</small>
                @error('jumlah')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-title">Catatan</div>
        <div class="form-group">
            <label for="keterangan">Keterangan Operasional</label>
            <textarea id="keterangan" name="keterangan" rows="3"
                      placeholder="Contoh: panen blok A12, 8 pekerja, 5.250 kg TBS">{{ old('keterangan', $p?->keterangan) }}</textarea>
            @error('keterangan')<span class="form-error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
