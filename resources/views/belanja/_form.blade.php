@php
    $editing = isset($belanja);
    $tanggalValue = old('tanggal', $editing ? \Carbon\Carbon::parse($belanja->tanggal)->format('Y-m-d') : now()->toDateString());
    $nomorBuktiValue = old('nomor_bukti', $editing ? $belanja->nomor_bukti : '');
    $uraianValue = old('uraian', $editing ? $belanja->uraian : '');
    $kategoriValue = old('kategori', $editing ? $belanja->kategori : 'Barang');
    $jumlahValue = old('jumlah', $editing ? $belanja->jumlah : 1);
    $hargaValue = old('harga_satuan', $editing ? $belanja->harga_satuan : '');
@endphp

<div class="form-grid">
    <div class="field">
        <label for="tanggal">Tanggal <span>*</span></label>
        <input id="tanggal" type="date" name="tanggal" value="{{ $tanggalValue }}" required>
        @error('tanggal') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
        <label for="nomor_bukti">Nomor Bukti <span>*</span></label>
        <input id="nomor_bukti" type="text" name="nomor_bukti" value="{{ $nomorBuktiValue }}" maxlength="255" placeholder="Contoh: BKT-001" required>
        @error('nomor_bukti') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field full">
        <label for="uraian">Uraian Belanja <span>*</span></label>
        <textarea id="uraian" name="uraian" maxlength="255" rows="3" placeholder="Tuliskan barang atau jasa yang dibelanjakan" required>{{ $uraianValue }}</textarea>
        <div class="field-help">Gunakan uraian yang jelas karena teks ini akan digunakan pada dokumen LPJ.</div>
        @error('uraian') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
        <label for="kategori">Kategori <span>*</span></label>
        <select id="kategori" name="kategori" required>
            <option value="Barang" @selected($kategoriValue === 'Barang')>Barang</option>
            <option value="Jasa" @selected($kategoriValue === 'Jasa')>Jasa</option>
        </select>
        @error('kategori') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
        <label for="jumlah">Jumlah <span>*</span></label>
        <input id="jumlah" type="number" name="jumlah" value="{{ $jumlahValue }}" min="1" step="1" required>
        @error('jumlah') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
        <label for="harga_satuan">Harga Satuan <span>*</span></label>
        <div class="money-input">
            <span>Rp</span>
            <input id="harga_satuan" type="number" name="harga_satuan" value="{{ $hargaValue }}" min="0" step="1" inputmode="numeric" placeholder="0" required>
        </div>
        @error('harga_satuan') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
        <label>Total Belanja</label>
        <div class="total-preview" id="totalPreview">Rp 0</div>
        <div class="field-help">Total dihitung otomatis oleh server dari jumlah × harga satuan.</div>
    </div>
</div>

<div class="form-actions">
    <a href="{{ route('belanja.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $editing ? 'Simpan Perubahan' : 'Simpan Belanja' }}</button>
</div>

<script>
    const jumlahInput = document.getElementById('jumlah');
    const hargaInput = document.getElementById('harga_satuan');
    const totalPreview = document.getElementById('totalPreview');

    function updateTotalPreview() {
        const jumlah = Number(jumlahInput?.value || 0);
        const harga = Number(hargaInput?.value || 0);
        const total = Math.max(0, jumlah) * Math.max(0, harga);
        totalPreview.textContent = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(total);
    }

    jumlahInput?.addEventListener('input', updateTotalPreview);
    hargaInput?.addEventListener('input', updateTotalPreview);
    updateTotalPreview();
</script>
