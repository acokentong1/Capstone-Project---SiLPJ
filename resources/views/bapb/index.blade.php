<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAPB - SILPJ</title>
    <style>@include('documents._app-styles')</style>
</head>
<body>
@include('partials.app-navigation')
<header class="header"><div class="header-inner"><div class="brand"><h1>BAPB</h1><p>Berita Acara Pemeriksaan Barang/Jasa untuk mendokumentasikan hasil pemeriksaan transaksi.</p></div><div class="actions"><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Data Belanja</a><a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a></div></div></header>
<main class="container">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    <div class="topbar"><div class="page-title"><h2>Daftar BAPB</h2><p>Pantau hasil pemeriksaan, cari dokumen, dan akses dokumen LPJ terkait dari satu halaman.</p></div></div>

    <section class="stats-grid four">
        <div class="stat-card"><div class="stat-label">Total BAPB</div><div class="stat-value">{{ number_format($totalBapb,0,',','.') }}</div><div class="stat-note">Seluruh dokumen pemeriksaan</div></div>
        <div class="stat-card"><div class="stat-label">Baik dan Sesuai</div><div class="stat-value">{{ number_format($sesuai,0,',','.') }}</div><div class="stat-note">Tanpa catatan pemeriksaan</div></div>
        <div class="stat-card"><div class="stat-label">Dengan Catatan</div><div class="stat-value">{{ number_format($denganCatatan,0,',','.') }}</div><div class="stat-note">Memerlukan perhatian</div></div>
        <div class="stat-card"><div class="stat-label">Tidak Sesuai</div><div class="stat-value">{{ number_format($tidakSesuai,0,',','.') }}</div><div class="stat-note">Perlu tindak lanjut</div></div>
    </section>

    <form method="GET" action="{{ route('bapb.index') }}" class="card filter-card">
        <div class="filter-grid with-select">
            <div class="field"><label>Pencarian</label><input type="text" name="q" value="{{ request('q') }}" placeholder="Nomor BAPB, nomor bukti, uraian, atau catatan"></div>
            <div class="field"><label>Hasil pemeriksaan</label><select name="hasil"><option value="">Semua hasil</option><option value="Baik dan sesuai" @selected(request('hasil')==='Baik dan sesuai')>Baik dan sesuai</option><option value="Baik, terdapat catatan" @selected(request('hasil')==='Baik, terdapat catatan')>Baik, terdapat catatan</option><option value="Tidak sesuai" @selected(request('hasil')==='Tidak sesuai')>Tidak sesuai</option></select></div>
            <div class="field"><label>Dari tanggal</label><input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"></div>
            <div class="field"><label>Sampai tanggal</label><input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"></div>
            <div class="filter-buttons"><button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-secondary" href="{{ route('bapb.index') }}">Reset</a></div>
        </div>
    </form>

    <section class="card table-card">
        <div class="table-meta"><strong>Dokumen BAPB</strong><span>{{ $bapbs->total() }} hasil</span></div>
        <div class="table-wrapper"><table class="data"><thead><tr><th>No.</th><th>Nomor BAPB</th><th>Tanggal</th><th>Transaksi</th><th>Kategori</th><th>Hasil</th><th class="right">Total</th><th>Aksi</th></tr></thead><tbody>
        @forelse($bapbs as $bapb)
            @php
                $badge = $bapb->hasil_pemeriksaan === 'Baik dan sesuai' ? 'badge-success' : ($bapb->hasil_pemeriksaan === 'Tidak sesuai' ? 'badge-danger' : 'badge-warning');
            @endphp
            <tr>
                <td class="center">{{ $bapbs->firstItem()+$loop->index }}</td><td class="strong nowrap">{{ $bapb->nomor_bapb }}</td><td class="nowrap">{{ optional($bapb->tanggal_bapb)->translatedFormat('d M Y') }}</td>
                <td><div class="strong">{{ $bapb->belanja->uraian ?? '-' }}</div><div class="muted">Bukti: {{ $bapb->belanja->nomor_bukti ?? '-' }}</div></td>
                <td><span class="badge badge-blue">{{ $bapb->belanja->kategori ?? '-' }}</span></td><td><span class="badge {{ $badge }}">{{ $bapb->hasil_pemeriksaan }}</span></td><td class="right nowrap">Rp {{ number_format($bapb->belanja->total ?? 0,0,',','.') }}</td>
                <td><div class="row-actions"><a class="action-link done" href="{{ route('bapb.show',$bapb->id) }}">Lihat / Cetak</a>
                    @if($bapb->belanja?->notaPesanan)<a class="action-link done" href="{{ route('nota-pesanan.show',$bapb->belanja->notaPesanan->id) }}">Nota ✓</a>@else<a class="action-link create" href="{{ route('nota-pesanan.create',$bapb->belanja_id) }}">+ Nota</a>@endif
                    @if($bapb->belanja?->kwitansi)<a class="action-link done" href="{{ route('kwitansi.show',$bapb->belanja->kwitansi->id) }}">Kwitansi ✓</a>@else<a class="action-link create" href="{{ route('kwitansi.create',$bapb->belanja_id) }}">+ Kwitansi</a>@endif
                </div></td>
            </tr>
        @empty<tr><td colspan="8" class="empty-state"><div class="empty-title">Belum ada BAPB</div>Buat BAPB dari halaman Data Belanja.</td></tr>@endforelse
        </tbody></table></div>
        @if($bapbs->hasPages())<div class="pagination-wrap"><div class="pagination-info">Menampilkan {{ $bapbs->firstItem() }}–{{ $bapbs->lastItem() }} dari {{ $bapbs->total() }} data</div><div class="pagination"><a class="page-link {{ $bapbs->onFirstPage()?'disabled':'' }}" href="{{ $bapbs->previousPageUrl() ?? '#' }}">‹</a>@foreach($bapbs->getUrlRange(max(1,$bapbs->currentPage()-2),min($bapbs->lastPage(),$bapbs->currentPage()+2)) as $page=>$url)<a class="page-link {{ $page===$bapbs->currentPage()?'active':'' }}" href="{{ $url }}">{{ $page }}</a>@endforeach<a class="page-link {{ $bapbs->hasMorePages()?'':'disabled' }}" href="{{ $bapbs->nextPageUrl() ?? '#' }}">›</a></div></div>@endif
    </section>
</main>
</body>
</html>
