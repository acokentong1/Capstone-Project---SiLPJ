<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pesanan {{ $nota->nomor_nota }} - SILPJ</title>
    <style>@include('documents._print-styles')</style>
</head>
<body>
@include('partials.app-navigation')
@php $belanja = $nota->belanja; @endphp
<div class="screen-wrap">
    @if(session('success'))<div class="flash no-print">{{ session('success') }}</div>@endif
    <div class="toolbar no-print">
        <div class="toolbar-group"><a href="{{ route('nota-pesanan.index') }}" class="btn btn-secondary">← Daftar Nota</a><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Data Belanja</a></div>
        <div class="toolbar-group"><button type="button" onclick="window.print()" class="btn btn-primary">Cetak Nota Pesanan</button></div>
    </div>

    <nav class="doc-nav no-print">
        <span class="doc-link active">Nota Pesanan ✓</span>
        @if($belanja->kwitansi)<a class="doc-link done" href="{{ route('kwitansi.show',$belanja->kwitansi->id) }}">Kwitansi ✓</a>@else<a class="doc-link missing" href="{{ route('kwitansi.create',$belanja->id) }}">+ Buat Kwitansi</a>@endif
        @if($belanja->bapb)<a class="doc-link done" href="{{ route('bapb.show',$belanja->bapb->id) }}">BAPB ✓</a>@else<a class="doc-link missing" href="{{ route('bapb.create',$belanja->id) }}">+ Buat BAPB</a>@endif
    </nav>

    <article class="paper">
        <header class="letterhead">
            <h1>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</h1>
            <p>NPSN: {{ $school->npsn ?? '-' }}</p>
            <p>{{ $school->alamat ?? '-' }}</p>
        </header>

        <section class="doc-title"><h2>NOTA PESANAN</h2><p>Nomor: <strong>{{ $nota->nomor_nota }}</strong></p></section>

        <table class="meta-table">
            <tr><td class="label">Tanggal</td><td>: {{ optional($nota->tanggal_nota)->translatedFormat('d F Y') }}</td></tr>
            <tr><td class="label">Nomor Bukti</td><td>: {{ $belanja->nomor_bukti ?: '-' }}</td></tr>
            <tr><td class="label">Kategori</td><td>: {{ $belanja->kategori }}</td></tr>
        </table>

        <p class="body-copy" style="margin:18px 0 0">Dengan ini dilakukan pemesanan untuk kebutuhan sekolah dengan rincian sebagai berikut:</p>

        <table class="detail-table">
            <thead><tr><th style="width:42px">No.</th><th>Uraian</th><th style="width:75px">Jumlah</th><th style="width:130px">Harga Satuan</th><th style="width:140px">Total</th></tr></thead>
            <tbody><tr><td class="center">1</td><td>{{ $belanja->uraian }}</td><td class="center">{{ number_format($belanja->jumlah,0,',','.') }}</td><td class="right">Rp {{ number_format($belanja->harga_satuan,0,',','.') }}</td><td class="right"><strong>Rp {{ number_format($belanja->total,0,',','.') }}</strong></td></tr></tbody>
        </table>

        <p class="body-copy" style="margin-top:18px">Nota Pesanan ini dibuat sebagai dasar administrasi pengadaan dan pertanggungjawaban belanja sekolah.</p>

        <table class="signature-table">
            <tr>
                <td>Mengetahui,<br>Kepala Sekolah<div class="signature-space"></div><div class="signature-name">{{ $school->kepala_sekolah ?? '........................' }}</div>@if(!empty($school->nip_kepala))<div>NIP. {{ $school->nip_kepala }}</div>@endif</td>
                <td>Bendahara<div class="signature-space"></div><div class="signature-name">{{ $school->bendahara ?? '........................' }}</div>@if(!empty($school->nip_bendahara))<div>NIP. {{ $school->nip_bendahara }}</div>@endif</td>
            </tr>
        </table>
    </article>
</div>
</body>
</html>
