<div class="form-section">
    <div class="form-section-title">
        <div class="form-section-icon">01</div>
        <h3>Identitas Sekolah</h3>
        <span>Data utama</span>
    </div>
    <div class="form-grid">
        <div class="field">
            <label for="nama_sekolah">Nama Sekolah <em>*</em></label>
            <input id="nama_sekolah" type="text" name="nama_sekolah" value="{{ old('nama_sekolah', $school->nama_sekolah ?? '') }}" required maxlength="255" placeholder="Contoh: SD Negeri 01 Kendari">
            @error('nama_sekolah')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="npsn">NPSN</label>
            <input id="npsn" type="text" name="npsn" value="{{ old('npsn', $school->npsn ?? '') }}" maxlength="50" inputmode="numeric" placeholder="Nomor Pokok Sekolah Nasional">
            <div class="field-help">Digunakan sebagai identitas pada laporan dan dokumen sekolah.</div>
            @error('npsn')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field full">
            <label for="alamat">Alamat Sekolah</label>
            <textarea id="alamat" name="alamat" maxlength="1000" placeholder="Tuliskan alamat sekolah secara lengkap">{{ old('alamat', $school->alamat ?? '') }}</textarea>
            @error('alamat')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-title">
        <div class="form-section-icon">02</div>
        <h3>Penanggung Jawab</h3>
        <span>Untuk dokumen LPJ</span>
    </div>
    <div class="form-grid">
        <div class="field">
            <label for="kepala_sekolah">Nama Kepala Sekolah</label>
            <input id="kepala_sekolah" type="text" name="kepala_sekolah" value="{{ old('kepala_sekolah', $school->kepala_sekolah ?? '') }}" maxlength="255" placeholder="Nama lengkap kepala sekolah">
            @error('kepala_sekolah')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="nip_kepala">NIP Kepala Sekolah</label>
            <input id="nip_kepala" type="text" name="nip_kepala" value="{{ old('nip_kepala', $school->nip_kepala ?? '') }}" maxlength="50" placeholder="NIP jika tersedia">
            @error('nip_kepala')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="bendahara">Nama Bendahara</label>
            <input id="bendahara" type="text" name="bendahara" value="{{ old('bendahara', $school->bendahara ?? '') }}" maxlength="255" placeholder="Nama lengkap bendahara">
            @error('bendahara')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="nip_bendahara">NIP Bendahara</label>
            <input id="nip_bendahara" type="text" name="nip_bendahara" value="{{ old('nip_bendahara', $school->nip_bendahara ?? '') }}" maxlength="50" placeholder="NIP jika tersedia">
            @error('nip_bendahara')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="section-note">
    <strong>Mengapa data ini penting?</strong>
    Nama sekolah, kepala sekolah, dan bendahara digunakan pada Nota Pesanan, Kwitansi, BAPB, dan Laporan LPJ. Periksa kembali ejaan sebelum menyimpan.
</div>
