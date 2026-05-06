{{-- FILE: resources/views/rekap/_form.blade.php --}}
{{-- Partial bersama untuk create & edit rekap --}}
{{-- Variabel: $rekap (object saat edit, null saat create) --}}

@php $r = $rekap ?? null; @endphp

<div class="form-grid">

    {{-- Tahun --}}
    <div class="form-group">
        <label for="tahun">Tahun Buku *</label>
        <input type="number" id="tahun" name="tahun"
               value="{{ old('tahun', $r?->tahun ?? date('Y')) }}"
               min="2000" max="2100"
               placeholder="{{ date('Y') }}" required>
        @error('tahun')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Tanggal Tutup --}}
    <div class="form-group">
        <label for="tanggal_tutup">Tanggal Tutup Buku *</label>
        <input type="date" id="tanggal_tutup" name="tanggal_tutup"
               value="{{ old('tanggal_tutup', $r?->tanggal_tutup?->format('Y-m-d') ?? date('Y') . '-12-31') }}"
               required>
        @error('tanggal_tutup')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Grand Total Debet — rupiah-input agar mudah dibaca --}}
    <div class="form-group">
        <label for="grand_total_debet">Grand Total Debet (Rp) *</label>
        <input type="text" id="grand_total_debet" name="grand_total_debet"
               class="rupiah-input"
               value="{{ old('grand_total_debet', $r?->grand_total_debet ?? 0) }}"
               placeholder="0" required>
        @error('grand_total_debet')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Grand Total Kredit --}}
    <div class="form-group">
        <label for="grand_total_kredit">Grand Total Kredit (Rp) *</label>
        <input type="text" id="grand_total_kredit" name="grand_total_kredit"
               class="rupiah-input"
               value="{{ old('grand_total_kredit', $r?->grand_total_kredit ?? 0) }}"
               placeholder="0" required>
        @error('grand_total_kredit')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Saldo Akhir --}}
    <div class="form-group">
        <label for="saldo_akhir">Saldo Akhir (Rp) *</label>
        <input type="text" id="saldo_akhir" name="saldo_akhir"
               class="rupiah-input"
               value="{{ old('saldo_akhir', $r?->saldo_akhir ?? 0) }}"
               placeholder="0" required>
        @error('saldo_akhir')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Lokasi --}}
    <div class="form-group">
        <label for="lokasi">Lokasi</label>
        <input type="text" id="lokasi" name="lokasi"
               value="{{ old('lokasi', $r?->lokasi ?? 'Cahaya Mulya') }}"
               placeholder="Nama lokasi koperasi">
        @error('lokasi')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Divider Pengurus --}}
    <div style="grid-column:1/-1;">
        <hr style="border:none;border-top:1px solid var(--krem-drk);margin:8px 0 16px;">
        <p style="font-size:12px;text-transform:uppercase;letter-spacing:.6px;color:var(--abu);margin-bottom:14px;">
            Informasi Pengurus
        </p>
    </div>

    {{-- Ketua Pengurus --}}
    <div class="form-group">
        <label for="ketua_pengurus">Ketua Pengurus</label>
        <input type="text" id="ketua_pengurus" name="ketua_pengurus"
               value="{{ old('ketua_pengurus', $r?->ketua_pengurus ?? 'SUGENG') }}"
               placeholder="Nama ketua pengurus">
        @error('ketua_pengurus')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Sekretaris --}}
    <div class="form-group">
        <label for="sekretaris">Sekretaris</label>
        <input type="text" id="sekretaris" name="sekretaris"
               value="{{ old('sekretaris', $r?->sekretaris ?? 'PUTU SEDANA') }}"
               placeholder="Nama sekretaris">
        @error('sekretaris')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Bendahara --}}
    <div class="form-group">
        <label for="bendahara">Bendahara</label>
        <input type="text" id="bendahara" name="bendahara"
               value="{{ old('bendahara', $r?->bendahara ?? 'MUSLIMIN') }}"
               placeholder="Nama bendahara">
        @error('bendahara')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Ketua Badan Pengawas --}}
    <div class="form-group">
        <label for="ketua_badan_pengawas">Ketua Badan Pengawas</label>
        <input type="text" id="ketua_badan_pengawas" name="ketua_badan_pengawas"
               value="{{ old('ketua_badan_pengawas', $r?->ketua_badan_pengawas ?? 'NASRUDIN') }}"
               placeholder="Nama ketua badan pengawas">
        @error('ketua_badan_pengawas')<span class="form-error">{{ $message }}</span>@enderror
    </div>

</div>
