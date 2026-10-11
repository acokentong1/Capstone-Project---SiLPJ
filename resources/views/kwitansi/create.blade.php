<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kwitansi - SILPJ</title>
    <style>@include('documents._app-styles')</style>
</head>
<body>
@include('partials.app-navigation')
<header class="header"><div class="header-inner"><div class="brand"><h1>Buat Kwitansi</h1><p>Lengkapi nama penerima. Nominal dan terbilang dihitung otomatis oleh sistem.</p></div><div class="actions"><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Kembali ke Belanja</a></div></div></header>
<main class="container">
    @if($errors->any())<div class="alert alert-error"><strong>Data belum lengkap.</strong>&nbsp;{{ $errors->first() }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    <div class="topbar"><div class="page-title"><h2>Konfirmasi Kwitansi</h2><p>Kwitansi akan terhubung langsung dengan transaksi ini dan hanya dapat dibuat satu kali.</p></div></div>

    <div class="form-layout">
        <section class="card form-card">
            <h3>Informasi Kwitansi</h3>
            <form action="{{ route('kwitansi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="belanja_id" value="{{ $belanja->id }}">
                <div class="form-grid">
                    <div class="field"><label>Nomor Kwitansi</label><input class="readonly" type="text" value="{{ $nomorKwitansi }}" readonly></div>
                    <div class="field"><label>Tanggal Pembuatan</label><input class="readonly" type="text" value="{{ now()->translatedFormat('d F Y') }}" readonly></div>
                    <div class="field full"><label>Nama Penerima <span style="color:#b91c1c">*</span></label><input type="text" name="penerima" value="{{ old('penerima') }}" placeholder="Contoh: Ahmad, Toko Maju Jaya, atau nama penyedia" maxlength="255" required autofocus>@error('penerima')<div class="error-text">{{ $message }}</div>@enderror<div class="help">Isi pihak yang menerima pembayaran sesuai bukti transaksi.</div></div>
                    <div class="field"><label>Jumlah Uang</label><input class="readonly" type="text" value="Rp {{ number_format($belanja->total,0,',','.') }}" readonly></div>
                    <div class="field"><label>Dibayar oleh</label><input class="readonly" type="text" value="{{ $school->nama_sekolah }}" readonly></div>
                    <div class="field full"><label>Terbilang</label><textarea class="readonly" readonly>{{ ucfirst($terbilang) }} rupiah</textarea></div>
                </div>
                <div class="form-footer"><button type="submit" class="btn btn-primary">Simpan Kwitansi</button><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Batal</a></div>
            </form>
        </section>

        <aside class="card summary-card">
            <h3>Ringkasan Transaksi</h3>
            <div class="summary-list">
                <div class="summary-item"><div class="summary-label">Nomor Bukti</div><div class="summary-value">{{ $belanja->nomor_bukti ?: '-' }}</div></div>
                <div class="summary-item"><div class="summary-label">Tanggal Belanja</div><div class="summary-value">{{ \Carbon\Carbon::parse($belanja->tanggal)->translatedFormat('d F Y') }}</div></div>
                <div class="summary-item"><div class="summary-label">Uraian</div><div class="summary-value">{{ $belanja->uraian }}</div></div>
                <div class="summary-item"><div class="summary-label">Kategori</div><div class="summary-value">{{ $belanja->kategori }}</div></div>
            </div>
            <div class="summary-total"><div class="summary-label">Nominal Pembayaran</div><div class="summary-value">Rp {{ number_format($belanja->total,0,',','.') }}</div></div>
        </aside>
    </div>
</main>
</body>
</html>
