<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat BAPB - SILPJ</title>
    <style>@include('documents._app-styles')</style>
</head>
<body>
@include('partials.app-navigation')
<header class="header"><div class="header-inner"><div class="brand"><h1>Buat BAPB</h1><p>Catat hasil pemeriksaan barang/jasa dengan format yang konsisten.</p></div><div class="actions"><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Kembali ke Belanja</a></div></div></header>
<main class="container">
    @if($errors->any())<div class="alert alert-error"><strong>Data belum lengkap.</strong>&nbsp;{{ $errors->first() }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    <div class="topbar"><div class="page-title"><h2>Form Pemeriksaan {{ $belanja->kategori }}</h2><p>Nomor BAPB dibuat otomatis. Pilih hasil pemeriksaan dan tambahkan catatan jika diperlukan.</p></div></div>

    <div class="form-layout">
        <section class="card form-card">
            <h3>Informasi Pemeriksaan</h3>
            <form action="{{ route('bapb.store') }}" method="POST">
                @csrf
                <input type="hidden" name="belanja_id" value="{{ $belanja->id }}">
                <div class="form-grid">
                    <div class="field"><label>Nomor BAPB</label><input class="readonly" type="text" value="{{ $nomorBapb }}" readonly></div>
                    <div class="field"><label>Tanggal Pemeriksaan <span style="color:#b91c1c">*</span></label><input type="date" name="tanggal_bapb" value="{{ old('tanggal_bapb', now()->toDateString()) }}" required>@error('tanggal_bapb')<div class="error-text">{{ $message }}</div>@enderror</div>
                    <div class="field full"><label>Hasil Pemeriksaan <span style="color:#b91c1c">*</span></label><select name="hasil_pemeriksaan" required><option value="Baik dan sesuai" @selected(old('hasil_pemeriksaan','Baik dan sesuai')==='Baik dan sesuai')>Baik dan sesuai</option><option value="Baik, terdapat catatan" @selected(old('hasil_pemeriksaan')==='Baik, terdapat catatan')>Baik, terdapat catatan</option><option value="Tidak sesuai" @selected(old('hasil_pemeriksaan')==='Tidak sesuai')>Tidak sesuai</option></select><div class="help">Gunakan “Baik, terdapat catatan” jika barang/jasa diterima tetapi ada hal yang perlu dicatat.</div></div>
                    <div class="field full"><label>Keterangan</label><textarea name="keterangan" maxlength="2000" placeholder="Contoh: kemasan rusak ringan, jumlah sesuai, pekerjaan selesai dengan catatan, dan sebagainya">{{ old('keterangan') }}</textarea>@error('keterangan')<div class="error-text">{{ $message }}</div>@enderror</div>
                </div>
                <div class="form-footer"><button type="submit" class="btn btn-primary">Simpan BAPB</button><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Batal</a></div>
            </form>
        </section>

        <aside class="card summary-card">
            <h3>Objek Pemeriksaan</h3>
            <div class="summary-list">
                <div class="summary-item"><div class="summary-label">Nomor Bukti</div><div class="summary-value">{{ $belanja->nomor_bukti ?: '-' }}</div></div>
                <div class="summary-item"><div class="summary-label">Kategori</div><div class="summary-value">{{ $belanja->kategori }}</div></div>
                <div class="summary-item"><div class="summary-label">Uraian</div><div class="summary-value">{{ $belanja->uraian }}</div></div>
                <div class="summary-item"><div class="summary-label">Jumlah</div><div class="summary-value">{{ number_format($belanja->jumlah,0,',','.') }}</div></div>
                <div class="summary-item"><div class="summary-label">Pemeriksa</div><div class="summary-value">{{ $school->bendahara ?: '-' }}</div></div>
            </div>
            <div class="summary-total"><div class="summary-label">Nilai Transaksi</div><div class="summary-value">Rp {{ number_format($belanja->total,0,',','.') }}</div></div>
        </aside>
    </div>
</main>
</body>
</html>
