<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi - SILPJ</title>
    <style>@include('documents._app-styles')</style>
</head>
<body>
@include('partials.app-navigation')
<header class="header">
    <div class="header-inner">
        <div class="brand"><h1>Kwitansi</h1><p>Kelola bukti penerimaan pembayaran untuk setiap transaksi belanja.</p></div>
        <div class="actions"><a href="{{ route('belanja.index') }}" class="btn btn-secondary">Data Belanja</a><a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a></div>
    </div>
</header>
<main class="container">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    <div class="topbar"><div class="page-title"><h2>Daftar Kwitansi</h2><p>Cari berdasarkan nomor, penerima, atau transaksi dan lengkapi dokumen LPJ yang belum tersedia.</p></div></div>

    <section class="stats-grid">
        <div class="stat-card"><div class="stat-label">Total Kwitansi</div><div class="stat-value">{{ number_format($totalKwitansi,0,',','.') }}</div><div class="stat-note">Dokumen yang sudah dibuat</div></div>
        <div class="stat-card"><div class="stat-label">Total Pembayaran</div><div class="stat-value">Rp {{ number_format($totalNilai,0,',','.') }}</div><div class="stat-note">Akumulasi nominal kwitansi</div></div>
        <div class="stat-card"><div class="stat-label">LPJ Lengkap</div><div class="stat-value">{{ number_format($kwitansiLengkap,0,',','.') }}</div><div class="stat-note">Nota + Kwitansi + BAPB tersedia</div></div>
    </section>

    <form method="GET" action="{{ route('kwitansi.index') }}" class="card filter-card">
        <div class="filter-grid">
            <div class="field"><label>Pencarian</label><input type="text" name="q" value="{{ request('q') }}" placeholder="Nomor kwitansi, penerima, bukti, atau uraian"></div>
            <div class="field"><label>Dari tanggal</label><input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"></div>
            <div class="field"><label>Sampai tanggal</label><input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"></div>
            <div class="filter-buttons"><button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-secondary" href="{{ route('kwitansi.index') }}">Reset</a></div>
        </div>
    </form>

    <section class="card table-card">
        <div class="table-meta"><strong>Dokumen Kwitansi</strong><span>{{ $kwitansi->total() }} hasil</span></div>
        <div class="table-wrapper">
            <table class="data">
                <thead><tr><th>No.</th><th>Nomor Kwitansi</th><th>Tanggal</th><th>Penerima</th><th>Transaksi</th><th class="right">Nominal</th><th>Status LPJ</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($kwitansi as $item)
                    @php $docCount = 1 + ($item->belanja?->notaPesanan ? 1 : 0) + ($item->belanja?->bapb ? 1 : 0); @endphp
                    <tr>
                        <td class="center">{{ $kwitansi->firstItem() + $loop->index }}</td>
                        <td class="strong nowrap">{{ $item->nomor_kwitansi }}</td>
                        <td class="nowrap">{{ optional($item->tanggal_kwitansi)->translatedFormat('d M Y') }}</td>
                        <td>{{ $item->penerima }}</td>
                        <td><div class="strong">{{ $item->belanja->uraian ?? '-' }}</div><div class="muted">Bukti: {{ $item->belanja->nomor_bukti ?? '-' }}</div></td>
                        <td class="right nowrap">Rp {{ number_format($item->jumlah_uang,0,',','.') }}</td>
                        <td><span class="badge {{ $docCount === 3 ? 'badge-success' : 'badge-warning' }}">{{ $docCount }}/3 dokumen</span></td>
                        <td><div class="row-actions">
                            <a class="action-link done" href="{{ route('kwitansi.show',$item->id) }}">Lihat / Cetak</a>
                            @if($item->belanja?->notaPesanan)<a class="action-link done" href="{{ route('nota-pesanan.show',$item->belanja->notaPesanan->id) }}">Nota ✓</a>@else<a class="action-link create" href="{{ route('nota-pesanan.create',$item->belanja_id) }}">+ Nota</a>@endif
                            @if($item->belanja?->bapb)<a class="action-link done" href="{{ route('bapb.show',$item->belanja->bapb->id) }}">BAPB ✓</a>@else<a class="action-link create" href="{{ route('bapb.create',$item->belanja_id) }}">+ BAPB</a>@endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state"><div class="empty-title">Belum ada Kwitansi</div>Buat Kwitansi dari halaman Data Belanja.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($kwitansi->hasPages())
        <div class="pagination-wrap"><div class="pagination-info">Menampilkan {{ $kwitansi->firstItem() }}–{{ $kwitansi->lastItem() }} dari {{ $kwitansi->total() }} data</div><div class="pagination">
            <a class="page-link {{ $kwitansi->onFirstPage() ? 'disabled' : '' }}" href="{{ $kwitansi->previousPageUrl() ?? '#' }}">‹</a>
            @foreach($kwitansi->getUrlRange(max(1,$kwitansi->currentPage()-2),min($kwitansi->lastPage(),$kwitansi->currentPage()+2)) as $page=>$url)<a class="page-link {{ $page === $kwitansi->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>@endforeach
            <a class="page-link {{ $kwitansi->hasMorePages() ? '' : 'disabled' }}" href="{{ $kwitansi->nextPageUrl() ?? '#' }}">›</a>
        </div></div>
        @endif
    </section>
</main>
</body>
</html>
