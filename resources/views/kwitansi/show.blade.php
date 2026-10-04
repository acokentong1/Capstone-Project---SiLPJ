<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi {{ $kwitansi->nomor_kwitansi }} - SILPJ</title>
    <style>@include('documents._print-styles')</style>
</head>
<body>
@include('partials.app-navigation')
@php $belanja = $kwitansi->belanja; @endphp
<div class="screen-wrap">
    @if(session('success'))<div class="flash no-print">{{ session('success') }}</div>@endif
    <div class="toolbar no-print">
        <div class="toolbar-group"><a href="{{ route('kwitansi.index') }}" class="btn btn-secondary">← Daftar Kwitansi</a><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Data Belanja</a></div>
        <div class="toolbar-group"><button type="button" onclick="window.print()" class="btn btn-primary">Cetak Kwitansi</button></div>
    </div>

    <nav class="doc-nav no-print">
        @if($belanja->notaPesanan)<a class="doc-link done" href="{{ route('nota-pesanan.show',$belanja->notaPesanan->id) }}">Nota Pesanan ✓</a>@else<a class="doc-link missing" href="{{ route('nota-pesanan.create',$belanja->id) }}">+ Buat Nota</a>@endif
        <span class="doc-link active">Kwitansi ✓</span>
        @if($belanja->bapb)<a class="doc-link done" href="{{ route('bapb.show',$belanja->bapb->id) }}">BAPB ✓</a>@else<a class="doc-link missing" href="{{ route('bapb.create',$belanja->id) }}">+ Buat BAPB</a>@endif
    </nav>

    <article class="paper">
        <header class="letterhead">
            <h1>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</h1>
            <p>NPSN: {{ $school->npsn ?? '-' }} &nbsp; | &nbsp; {{ $school->alamat ?? '-' }}</p>
        </header>

        <div class="receipt-box">
            <section class="receipt-title"><h1>KWITANSI</h1><p>Nomor: <strong>{{ $kwitansi->nomor_kwitansi }}</strong></p></section>
            <table class="receipt-table">
                <tr><td class="label">Sudah terima dari</td><td class="colon">:</td><td><strong>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</strong></td></tr>
                <tr><td>Uang sejumlah</td><td>:</td><td class="receipt-terbilang">{{ ucfirst($kwitansi->terbilang) }} rupiah</td></tr>
                <tr><td>Untuk pembayaran</td><td>:</td><td>{{ $belanja->uraian }}</td></tr>
                <tr><td>Nomor bukti</td><td>:</td><td>{{ $belanja->nomor_bukti ?: '-' }}</td></tr>
            </table>

            <div class="amount-box">Rp {{ number_format($kwitansi->jumlah_uang,0,',','.') }}</div>

            <div class="receipt-signature">
                <div>{{ optional($kwitansi->tanggal_kwitansi)->translatedFormat('d F Y') }}</div>
                <div style="margin-top:8px">Yang menerima,</div>
                <div class="space"></div>
                <div class="signature-name">{{ $kwitansi->penerima }}</div>
            </div>
        </div>
    </article>
</div>
</body>
</html>
