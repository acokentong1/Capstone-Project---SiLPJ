<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pesanan - SILPJ</title>
    <style>@include('documents._app-styles')</style>
</head>
<body>
@include('partials.app-navigation')
<header class="header">
    <div class="header-inner">
        <div class="brand">
            <h1>Nota Pesanan</h1>
            <p>Kelola dan cetak dokumen pemesanan dari transaksi belanja sekolah.</p>
        </div>
        <div class="actions">
            <a href="{{ route('belanja.index') }}" class="btn btn-secondary">Data Belanja</a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</header>

<main class="container">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

    <div class="topbar">
        <div class="page-title">
            <h2>Daftar Nota Pesanan</h2>
            <p>Cari dokumen, periksa status LPJ terkait, lalu buka atau cetak Nota Pesanan.</p>
        </div>
    </div>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Nota Pesanan</div>
            <div class="stat-value">{{ number_format($totalNota, 0, ',', '.') }}</div>
            <div class="stat-note">Dokumen yang sudah dibuat</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Nilai Transaksi</div>
            <div class="stat-value">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
            <div class="stat-note">Total belanja yang memiliki Nota</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">LPJ Lengkap</div>
            <div class="stat-value">{{ number_format($notaLengkap, 0, ',', '.') }}</div>
            <div class="stat-note">Nota + Kwitansi + BAPB tersedia</div>
        </div>
    </section>

    <form method="GET" action="{{ route('nota-pesanan.index') }}" class="card filter-card">
        <div class="filter-grid">
            <div class="field">
                <label>Pencarian</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nomor nota, nomor bukti, atau uraian">
            </div>
            <div class="field">
                <label>Dari tanggal</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="field">
                <label>Sampai tanggal</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="{{ route('nota-pesanan.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <section class="card table-card">
        <div class="table-meta">
            <strong>Dokumen Nota Pesanan</strong>
            <span>{{ $notas->total() }} hasil</span>
        </div>
        <div class="table-wrapper">
            <table class="data">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Nota</th>
                        <th>Tanggal</th>
                        <th>Transaksi</th>
                        <th class="right">Total</th>
                        <th>Status LPJ</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($notas as $nota)
                    @php
                        $docCount = 1 + ($nota->belanja?->kwitansi ? 1 : 0) + ($nota->belanja?->bapb ? 1 : 0);
                    @endphp
                    <tr>
                        <td class="center">{{ $notas->firstItem() + $loop->index }}</td>
                        <td class="strong nowrap">{{ $nota->nomor_nota }}</td>
                        <td class="nowrap">{{ optional($nota->tanggal_nota)->translatedFormat('d M Y') }}</td>
                        <td>
                            <div class="strong">{{ $nota->belanja->uraian ?? '-' }}</div>
                            <div class="muted">Bukti: {{ $nota->belanja->nomor_bukti ?? '-' }}</div>
                        </td>
                        <td class="right nowrap">Rp {{ number_format($nota->belanja->total ?? 0, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $docCount === 3 ? 'badge-success' : 'badge-warning' }}">{{ $docCount }}/3 dokumen</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <a class="action-link done" href="{{ route('nota-pesanan.show', $nota->id) }}">Lihat / Cetak</a>
                                @if($nota->belanja?->kwitansi)
                                    <a class="action-link done" href="{{ route('kwitansi.show', $nota->belanja->kwitansi->id) }}">Kwitansi ✓</a>
                                @else
                                    <a class="action-link create" href="{{ route('kwitansi.create', $nota->belanja_id) }}">+ Kwitansi</a>
                                @endif
                                @if($nota->belanja?->bapb)
                                    <a class="action-link done" href="{{ route('bapb.show', $nota->belanja->bapb->id) }}">BAPB ✓</a>
                                @else
                                    <a class="action-link create" href="{{ route('bapb.create', $nota->belanja_id) }}">+ BAPB</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state"><div class="empty-title">Belum ada Nota Pesanan</div>Buat Nota dari halaman Data Belanja.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($notas->hasPages())
        <div class="pagination-wrap">
            <div class="pagination-info">Menampilkan {{ $notas->firstItem() }}–{{ $notas->lastItem() }} dari {{ $notas->total() }} data</div>
            <div class="pagination">
                <a class="page-link {{ $notas->onFirstPage() ? 'disabled' : '' }}" href="{{ $notas->previousPageUrl() ?? '#' }}">‹</a>
                @foreach($notas->getUrlRange(max(1,$notas->currentPage()-2), min($notas->lastPage(),$notas->currentPage()+2)) as $page => $url)
                    <a class="page-link {{ $page === $notas->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
                @endforeach
                <a class="page-link {{ $notas->hasMorePages() ? '' : 'disabled' }}" href="{{ $notas->nextPageUrl() ?? '#' }}">›</a>
            </div>
        </div>
        @endif
    </section>
</main>
</body>
</html>
