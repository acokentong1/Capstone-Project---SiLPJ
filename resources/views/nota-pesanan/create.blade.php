<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Nota Pesanan - SILPJ</title>
    <style>@include('documents._app-styles')</style>
</head>
<body>
@include('partials.app-navigation')
<header class="header"><div class="header-inner"><div class="brand"><h1>Buat Nota Pesanan</h1><p>Periksa data transaksi sebelum dokumen disimpan.</p></div><div class="actions"><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Kembali ke Belanja</a></div></div></header>
<main class="container">
    @if($errors->any())<div class="alert alert-error"><strong>Data belum dapat disimpan.</strong>&nbsp;{{ $errors->first() }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

    <div class="topbar"><div class="page-title"><h2>Konfirmasi Nota Pesanan</h2><p>Nomor dokumen dibuat otomatis oleh sistem. Data nominal diambil langsung dari transaksi.</p></div></div>

    <div class="form-layout">
        <section class="card form-card">
            <h3>Informasi Dokumen</h3>
            <form action="{{ route('nota-pesanan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="belanja_id" value="{{ $belanja->id }}">
                <div class="form-grid">
                    <div class="field"><label>Nomor Nota</label><input class="readonly" type="text" value="{{ $nomorNota }}" readonly><div class="help">Nomor final akan dikunci saat dokumen disimpan.</div></div>
                    <div class="field"><label>Tanggal Nota</label><input class="readonly" type="text" value="{{ \Carbon\Carbon::parse($belanja->tanggal)->translatedFormat('d F Y') }}" readonly></div>
                    <div class="field full"><label>Sekolah</label><input class="readonly" type="text" value="{{ $school->nama_sekolah }}" readonly></div>
                    <div class="field"><label>NPSN</label><input class="readonly" type="text" value="{{ $school->npsn ?: '-' }}" readonly></div>
                    <div class="field"><label>Kategori</label><input class="readonly" type="text" value="{{ $belanja->kategori }}" readonly></div>
                </div>
                <div class="alert alert-warning" style="margin-top:18px;margin-bottom:0">Pastikan uraian, jumlah, harga, dan tanggal transaksi sudah benar. Setelah Nota dibuat, perubahan pada transaksi dapat memengaruhi isi dokumen saat dicetak kembali.</div>
                <div class="form-footer"><button type="submit" class="btn btn-primary">Simpan Nota Pesanan</button><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Batal</a></div>
            </form>
        </section>

        <aside class="card summary-card">
            <h3>Ringkasan Transaksi</h3>
            <div class="summary-list">
                <div class="summary-item"><div class="summary-label">Nomor Bukti</div><div class="summary-value">{{ $belanja->nomor_bukti ?: '-' }}</div></div>
                <div class="summary-item"><div class="summary-label">Uraian</div><div class="summary-value">{{ $belanja->uraian }}</div></div>
                <div class="summary-item"><div class="summary-label">Jumlah</div><div class="summary-value">{{ number_format($belanja->jumlah,0,',','.') }}</div></div>
                <div class="summary-item"><div class="summary-label">Harga Satuan</div><div class="summary-value">Rp {{ number_format($belanja->harga_satuan,0,',','.') }}</div></div>
            </div>
            <div class="summary-total"><div class="summary-label">Total Belanja</div><div class="summary-value">Rp {{ number_format($belanja->total,0,',','.') }}</div></div>
        </aside>
    </div>
</main>
</body>
</html>
