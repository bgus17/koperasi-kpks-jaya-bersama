{{-- FILE: resources/views/karyawan/_form.blade.php --}}
{{-- Kolom aktif: nama, jenis_kelamin, no_hp, alamat, tanggal_masuk, status, keterangan --}}

@if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;
                padding:10px 16px;border-radius:8px;margin-bottom:20px;">
        <strong>Ada kesalahan input:</strong>
        <ul style="margin:6px 0 0 16px;">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php $k = $karyawan ?? null; @endphp

<div class="form-grid">

    {{-- Nama --}}
    <div class="form-group">
        <label>Nama Lengkap <span style="color:red">*</span></label>
        <input type="text" name="nama"
               value="{{ old('nama', $k?->nama) }}"
               placeholder="Nama lengkap karyawan"
               maxlength="150" required>
        @error('nama')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Jenis Kelamin --}}
    <div class="form-group">
        <label>Jenis Kelamin <span style="color:red">*</span></label>
        <select name="jenis_kelamin" required>
            <option value="L" {{ old('jenis_kelamin', $k?->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
            <option value="P" {{ old('jenis_kelamin', $k?->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
        @error('jenis_kelamin')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- No HP --}}
    <div class="form-group">
        <label>No. HP / WhatsApp</label>
        <input type="text" name="no_hp"
               value="{{ old('no_hp', $k?->no_hp) }}"
               placeholder="08xx-xxxx-xxxx"
               maxlength="20">
        @error('no_hp')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Status --}}
    <div class="form-group">
        <label>Status <span style="color:red">*</span></label>
        <select name="status" required>
            <option value="aktif"    {{ old('status', $k?->status ?? 'aktif') == 'aktif'    ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ old('status', $k?->status) == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
            <option value="cuti"     {{ old('status', $k?->status) == 'cuti'     ? 'selected' : '' }}>Cuti</option>
        </select>
        @error('status')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Tanggal Masuk --}}
    <div class="form-group">
        <label>Tanggal Masuk Kerja</label>
        <input type="date" name="tanggal_masuk"
               value="{{ old('tanggal_masuk', $k?->tanggal_masuk?->format('Y-m-d')) }}">
        @error('tanggal_masuk')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Alamat --}}
    <div class="form-group full">
        <label>Alamat</label>
        <textarea name="alamat" rows="2"
                  placeholder="Alamat lengkap karyawan..."
                  style="resize:vertical;">{{ old('alamat', $k?->alamat) }}</textarea>
        @error('alamat')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Keterangan --}}
    <div class="form-group full">
        <label>Keterangan <span style="font-weight:400;color:var(--abu);">(opsional)</span></label>
        <textarea name="keterangan" rows="2"
                  placeholder="Catatan tambahan..."
                  style="resize:vertical;">{{ old('keterangan', $k?->keterangan) }}</textarea>
        @error('keterangan')<span class="form-error">{{ $message }}</span>@enderror
    </div>

</div>
