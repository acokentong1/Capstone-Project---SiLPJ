<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAPB {{ $bapb->nomor_bapb }} - SILPJ</title>
    <style>@include('documents._print-styles')</style>
</head>
<body>
@include('partials.app-navigation')
@php
    $belanja = $bapb->belanja;
    $objek = strtolower($belanja->kategori ?? 'barang/jasa');
    $judulObjek = strtoupper($belanja->kategori ?? 'BARANG/JASA');
@endphp
<div class="screen-wrap">
    @if(session('success'))<div class="flash no-print">{{ session('success') }}</div>@endif
    <div class="toolbar no-print">
        <div class="toolbar-group"><a href="{{ route('bapb.index') }}" class="btn btn-secondary">← Daftar BAPB</a><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Data Belanja</a></div>
        <div class="toolbar-group"><button type="button" onclick="window.print()" class="btn btn-primary">Cetak BAPB</button></div>
    </div>

    <nav class="doc-nav no-print">
        @if($belanja->notaPesanan)<a class="doc-link done" href="{{ route('nota-pesanan.show',$belanja->notaPesanan->id) }}">Nota Pesanan ✓</a>@else<a class="doc-link missing" href="{{ route('nota-pesanan.create',$belanja->id) }}">+ Buat Nota</a>@endif
        @if($belanja->kwitansi)<a class="doc-link done" href="{{ route('kwitansi.show',$belanja->kwitansi->id) }}">Kwitansi ✓</a>@else<a class="doc-link missing" href="{{ route('kwitansi.create',$belanja->id) }}">+ Buat Kwitansi</a>@endif
        <span class="doc-link active">BAPB ✓</span>
    </nav>

    <article class="paper">
        <header class="letterhead">
            <h1>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</h1>
            <p>NPSN: {{ $school->npsn ?? '-' }}</p>
            <p>{{ $school->alamat ?? '-' }}</p>
        </header>

        <section class="doc-title"><h2>BERITA ACARA PEMERIKSAAN {{ $judulObjek }}</h2><p>Nomor: <strong>{{ $bapb->nomor_bapb }}</strong></p></section>

        <div class="body-copy">
            <p>Pada tanggal <strong>{{ optional($bapb->tanggal_bapb)->translatedFormat('d F Y') }}</strong> telah dilakukan pemeriksaan terhadap {{ $objek }} yang diterima oleh sekolah.</p>
            <p>Berdasarkan hasil pemeriksaan, rincian {{ $objek }} adalah sebagai berikut:</p>
        </div>

        <table class="detail-table">
            <thead><tr><th style="width:42px">No.</th><th>Uraian {{ $belanja->kategori }}</th><th style="width:75px">Jumlah</th><th style="width:130px">Harga Satuan</th><th style="width:140px">Total</th></tr></thead>
            <tbody><tr><td class="center">1</td><td>{{ $belanja->uraian }}</td><td class="center">{{ number_format($belanja->jumlah,0,',','.') }}</td><td class="right">Rp {{ number_format($belanja->harga_satuan,0,',','.') }}</td><td class="right"><strong>Rp {{ number_format($belanja->total,0,',','.') }}</strong></td></tr></tbody>
        </table>

        <div class="body-copy" style="margin-top:18px">
            <p><strong>Hasil Pemeriksaan:</strong> {{ $bapb->hasil_pemeriksaan }}</p>
            <p><strong>Keterangan:</strong> {{ $bapb->keterangan ?: '-' }}</p>
            <p>Berdasarkan hasil pemeriksaan tersebut, {{ $objek }} dinyatakan <strong>{{ strtolower($bapb->hasil_pemeriksaan) }}</strong>.</p>
            <p>Demikian Berita Acara Pemeriksaan {{ $belanja->kategori }} ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
        </div>

        <table class="signature-table">
            <tr>
                <td>Mengetahui,<br>Kepala Sekolah<div class="signature-space"></div><div class="signature-name">{{ $school->kepala_sekolah ?? '........................' }}</div>@if(!empty($school->nip_kepala))<div>NIP. {{ $school->nip_kepala }}</div>@endif</td>
                <td>Pemeriksa {{ $belanja->kategori }}<div class="signature-space"></div><div class="signature-name">{{ $school->bendahara ?? '........................' }}</div>@if(!empty($school->nip_bendahara))<div>NIP. {{ $school->nip_bendahara }}</div>@endif</td>
            </tr>
        </table>
    </article>
</div>
</body>
</html>
