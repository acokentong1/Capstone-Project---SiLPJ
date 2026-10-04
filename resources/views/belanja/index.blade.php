<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Belanja - SILPJ</title>

    <style>
        :root {
            --primary: #1d4ed8;
            --primary-dark: #1e40af;
            --primary-soft: #eff6ff;
            --success: #15803d;
            --success-soft: #f0fdf4;
            --warning: #b45309;
            --warning-soft: #fffbeb;
            --danger: #b91c1c;
            --danger-soft: #fef2f2;
            --purple: #6d28d9;
            --purple-soft: #f5f3ff;
            --text: #172033;
            --muted: #667085;
            --border: #e4e7ec;
            --surface: #ffffff;
            --background: #f5f7fb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--background);
            color: var(--text);
        }

        button, input, select, a { font: inherit; }

        .header {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: white;
            padding: 22px 32px;
            box-shadow: 0 8px 24px rgba(29, 78, 216, 0.16);
        }

        .header-inner,
        .container {
            width: min(1500px, 100%);
            margin: 0 auto;
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .brand h1 {
            margin: 0;
            font-size: 25px;
            letter-spacing: -0.4px;
        }

        .brand p {
            margin: 6px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }

        .container { padding: 28px 30px 42px; }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 18px;
        }

        .page-title h2 {
            margin: 0 0 7px;
            font-size: 24px;
        }

        .page-title p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .actions {
            display: flex;
            gap: 9px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
            padding: 9px 13px;
            border-radius: 8px;
            border: 1px solid transparent;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            transition: 0.18s ease;
            white-space: nowrap;
        }

        .btn:hover:not(:disabled) { transform: translateY(-1px); }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: white; color: #344054; border-color: #d0d5dd; }
        .btn-secondary:hover { background: #f9fafb; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger-soft { background: white; color: var(--danger); border-color: #fecaca; }
        .btn-danger-soft:hover { background: var(--danger-soft); }
        .btn-disabled { background: #f2f4f7; color: #98a2b3; border-color: #e4e7ec; cursor: not-allowed; }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 15px;
            margin-bottom: 16px;
            border-radius: 9px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-success { background: var(--success-soft); color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: var(--danger-soft); color: #991b1b; border: 1px solid #fecaca; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 13px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 16px 17px;
            box-shadow: 0 2px 8px rgba(16, 24, 40, 0.035);
        }

        .stat-label { color: var(--muted); font-size: 12px; font-weight: 700; }
        .stat-value { margin-top: 7px; font-size: 22px; font-weight: 800; letter-spacing: -0.3px; }
        .stat-note { margin-top: 5px; font-size: 12px; color: var(--muted); }

        .filter-card,
        .table-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(16, 24, 40, 0.04);
        }

        .filter-card { padding: 17px; margin-bottom: 16px; }

        .filter-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 13px;
        }

        .filter-title { font-size: 14px; font-weight: 800; }
        .filter-result { font-size: 12px; color: var(--muted); text-align: right; }

        .filter-grid {
            display: grid;
            grid-template-columns: minmax(220px, 1.6fr) repeat(4, minmax(135px, 0.8fr)) auto;
            gap: 10px;
            align-items: end;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            color: #475467;
            font-size: 12px;
            font-weight: 700;
        }

        .field input,
        .field select {
            width: 100%;
            height: 40px;
            border: 1px solid #d0d5dd;
            border-radius: 8px;
            padding: 0 11px;
            background: white;
            color: var(--text);
            outline: none;
        }

        .field input:focus,
        .field select:focus {
            border-color: #84adff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.09);
        }

        .filter-buttons { display: flex; gap: 7px; }

        .table-card { overflow: hidden; }

        .table-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }

        .table-meta strong { font-size: 14px; }
        .table-meta span { color: var(--muted); font-size: 12px; }

        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1180px; }

        thead th {
            background: #f8fafc;
            color: #475467;
            padding: 12px 11px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            white-space: nowrap;
        }

        tbody td {
            padding: 13px 11px;
            border-bottom: 1px solid #eef0f3;
            font-size: 13px;
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fbfcfe; }
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .uraian { min-width: 230px; font-weight: 700; color: #24324a; line-height: 1.45; }
        .muted { color: var(--muted); }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .badge-success { color: #166534; background: #dcfce7; }
        .badge-warning { color: #92400e; background: #fef3c7; }
        .badge-muted { color: #475467; background: #f2f4f7; }
        .badge-blue { color: #1d4ed8; background: #eff6ff; }

        .doc-progress {
            margin-top: 7px;
            display: flex;
            gap: 4px;
        }

        .doc-dot {
            width: 24px;
            height: 4px;
            border-radius: 999px;
            background: #e4e7ec;
        }

        .doc-dot.done { background: #22c55e; }

        .row-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            min-width: 360px;
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 6px 9px;
            border-radius: 7px;
            border: 1px solid #d0d5dd;
            background: white;
            color: #344054;
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .action-link:hover { background: #f8fafc; }
        .action-link.done { color: #166534; border-color: #bbf7d0; background: #f0fdf4; }
        .action-link.purple { color: var(--purple); border-color: #ddd6fe; background: var(--purple-soft); }
        .action-link.warning { color: #92400e; border-color: #fde68a; background: var(--warning-soft); }
        .action-link.edit { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; }
        .action-link.delete { color: #b91c1c; border-color: #fecaca; background: #fff; }
        .action-link:disabled { color: #98a2b3; background: #f9fafb; border-color: #e4e7ec; cursor: not-allowed; }

        .empty-state {
            padding: 50px 20px !important;
            text-align: center;
            color: var(--muted);
        }

        .empty-title { color: #344054; font-weight: 800; margin-bottom: 6px; }

        .pagination-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-top: 1px solid var(--border);
        }

        .pagination-info { color: var(--muted); font-size: 12px; }
        .pagination { display: flex; gap: 5px; flex-wrap: wrap; }
        .page-link {
            min-width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 9px;
            border: 1px solid #d0d5dd;
            border-radius: 7px;
            text-decoration: none;
            color: #344054;
            background: white;
            font-size: 12px;
            font-weight: 700;
        }
        .page-link:hover { background: #f8fafc; }
        .page-link.active { background: var(--primary); border-color: var(--primary); color: white; }
        .page-link.disabled { color: #98a2b3; background: #f9fafb; pointer-events: none; }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.48);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }

        .modal-backdrop.show { display: flex; }
        .modal {
            width: min(430px, 100%);
            background: white;
            border-radius: 13px;
            padding: 22px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
        }
        .modal h3 { margin: 0 0 8px; font-size: 19px; }
        .modal p { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.55; }
        .modal-item { margin: 15px 0; padding: 11px 12px; border-radius: 8px; background: #f8fafc; font-size: 13px; font-weight: 700; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 17px; }

        @media (max-width: 1180px) {
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .filter-search { grid-column: span 2; }
        }

        @media (max-width: 760px) {
            .header { padding: 19px 18px; }
            .header-inner { align-items: flex-start; }
            .brand h1 { font-size: 21px; }
            .brand p { font-size: 12px; }
            .container { padding: 20px 14px 34px; }
            .topbar { flex-direction: column; align-items: stretch; }
            .actions { width: 100%; }
            .actions .btn { flex: 1; }
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
            .stat-card { padding: 13px; }
            .stat-value { font-size: 18px; }
            .filter-top { align-items: flex-start; flex-direction: column; }
            .filter-result { text-align: left; }
            .filter-grid { grid-template-columns: 1fr 1fr; }
            .filter-search { grid-column: 1 / -1; }
            .filter-buttons { grid-column: 1 / -1; }
            .filter-buttons .btn { flex: 1; }
            .table-meta { align-items: flex-start; flex-direction: column; }

            .table-wrapper { overflow: visible; }
            table, thead, tbody, tr, td { display: block; width: 100%; min-width: 0; }
            thead { display: none; }
            tbody { padding: 10px; }
            tbody tr {
                border: 1px solid var(--border);
                border-radius: 10px;
                margin-bottom: 10px;
                padding: 5px 11px;
                background: white;
            }
            tbody tr:hover { background: white; }
            tbody td {
                display: grid;
                grid-template-columns: 115px minmax(0, 1fr);
                gap: 10px;
                padding: 9px 0;
                border-bottom: 1px dashed #eaecf0;
                text-align: left !important;
                min-width: 0;
            }
            tbody td:last-child { border-bottom: none; }
            tbody td::before {
                content: attr(data-label);
                color: var(--muted);
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
            }
            tbody td.mobile-hide { display: none; }
            tbody td.empty-state { display: block; }
            tbody td.empty-state::before { display: none; }
            .uraian { min-width: 0; }
            .row-actions { min-width: 0; }
            .pagination-wrap { flex-direction: column; align-items: flex-start; }
        }

        @media (max-width: 470px) {
            .stats-grid { grid-template-columns: 1fr; }
            .filter-grid { grid-template-columns: 1fr; }
            .filter-search,
            .filter-buttons { grid-column: auto; }
            tbody td { grid-template-columns: 95px minmax(0, 1fr); }
        }
    </style>
</head>
<body>
@include('partials.app-navigation')
    <header class="header">
        <div class="header-inner">
            <div class="brand">
                <h1>Data Belanja</h1>
                <p>Kelola transaksi dan kelengkapan dokumen pertanggungjawaban.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Dashboard</a>
        </div>
    </header>

    <main class="container">
        <div class="topbar">
            <div class="page-title">
                <h2>Transaksi Belanja</h2>
                <p>Cari transaksi, pantau kelengkapan LPJ, lalu buka atau buat dokumen dari satu halaman.</p>
            </div>
            <div class="actions">
                <a href="{{ route('laporan-lpj.index') }}" class="btn btn-secondary">Laporan LPJ</a>
                <a href="{{ route('belanja.create') }}" class="btn btn-primary">+ Tambah Belanja</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><strong>✓</strong><span>{{ session('success') }}</span></div>
        @endif

        @if(session('error'))
            <div class="alert alert-error"><strong>!</strong><span>{{ session('error') }}</span></div>
        @endif

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">TOTAL TRANSAKSI</div>
                <div class="stat-value">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                <div class="stat-note">Seluruh transaksi akun ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">TOTAL BELANJA</div>
                <div class="stat-value">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</div>
                <div class="stat-note">Akumulasi seluruh transaksi</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">LPJ LENGKAP</div>
                <div class="stat-value">{{ number_format($lpjLengkap, 0, ',', '.') }}</div>
                <div class="stat-note">Nota, Kwitansi, dan BAPB tersedia</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">BELUM LENGKAP</div>
                <div class="stat-value">{{ number_format($lpjBelumLengkap, 0, ',', '.') }}</div>
                <div class="stat-note">Masih membutuhkan dokumen LPJ</div>
            </div>
        </section>

        <section class="filter-card">
            <div class="filter-top">
                <div class="filter-title">Pencarian dan Filter</div>
                <div class="filter-result">
                    Ditemukan <strong>{{ number_format($belanjas->total(), 0, ',', '.') }}</strong> transaksi
                    dengan nilai <strong>Rp {{ number_format($filteredTotal, 0, ',', '.') }}</strong>
                </div>
            </div>

            <form method="GET" action="{{ route('belanja.index') }}" class="filter-grid">
                <div class="field filter-search">
                    <label for="q">Cari transaksi</label>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Nomor bukti atau uraian belanja">
                </div>

                <div class="field">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="">Semua kategori</option>
                        <option value="Barang" @selected($filters['kategori'] === 'Barang')>Barang</option>
                        <option value="Jasa" @selected($filters['kategori'] === 'Jasa')>Jasa</option>
                    </select>
                </div>

                <div class="field">
                    <label for="status">Status LPJ</label>
                    <select id="status" name="status">
                        <option value="">Semua status</option>
                        <option value="lengkap" @selected($filters['status'] === 'lengkap')>Lengkap</option>
                        <option value="proses" @selected($filters['status'] === 'proses')>Dalam proses</option>
                        <option value="belum" @selected($filters['status'] === 'belum')>Belum ada dokumen</option>
                    </select>
                </div>

                <div class="field">
                    <label for="tanggal_mulai">Dari tanggal</label>
                    <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ $filters['tanggal_mulai'] }}">
                </div>

                <div class="field">
                    <label for="tanggal_selesai">Sampai tanggal</label>
                    <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ $filters['tanggal_selesai'] }}">
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    @if($hasFilters)
                        <a href="{{ route('belanja.index') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="table-card">
            <div class="table-meta">
                <div>
                    <strong>Daftar Belanja</strong><br>
                    <span>Urutan terbaru berdasarkan tanggal transaksi.</span>
                </div>
                @if($hasFilters)
                    <span>Filter aktif</span>
                @endif
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width:55px;">No</th>
                            <th style="width:120px;">Tanggal</th>
                            <th style="width:110px;">No. Bukti</th>
                            <th>Uraian</th>
                            <th style="width:95px;">Kategori</th>
                            <th style="width:140px;">Total</th>
                            <th style="width:145px;">Status LPJ</th>
                            <th style="min-width:370px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($belanjas as $item)
                            @php
                                $docCount = ($item->notaPesanan ? 1 : 0)
                                    + ($item->kwitansi ? 1 : 0)
                                    + ($item->bapb ? 1 : 0);
                                $isComplete = $docCount === 3;
                                $hasDocuments = $docCount > 0;
                            @endphp

                            <tr>
                                <td class="center mobile-hide" data-label="No">
                                    {{ $belanjas->firstItem() + $loop->index }}
                                </td>
                                <td class="nowrap" data-label="Tanggal">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                                </td>
                                <td data-label="No. Bukti">{{ $item->nomor_bukti }}</td>
                                <td class="uraian" data-label="Uraian">
                                    {{ $item->uraian }}
                                    <div class="muted" style="margin-top:4px; font-size:11px;">
                                        {{ number_format($item->jumlah, 0, ',', '.') }} × Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td data-label="Kategori">
                                    <span class="badge badge-blue">{{ $item->kategori }}</span>
                                </td>
                                <td class="right nowrap" data-label="Total">
                                    <strong>Rp {{ number_format($item->total, 0, ',', '.') }}</strong>
                                </td>
                                <td data-label="Status LPJ">
                                    @if($isComplete)
                                        <span class="badge badge-success">✓ Lengkap</span>
                                    @elseif($hasDocuments)
                                        <span class="badge badge-warning">{{ $docCount }}/3 dokumen</span>
                                    @else
                                        <span class="badge badge-muted">Belum ada</span>
                                    @endif
                                    <div class="doc-progress" aria-label="{{ $docCount }} dari 3 dokumen lengkap">
                                        <span class="doc-dot {{ $item->notaPesanan ? 'done' : '' }}"></span>
                                        <span class="doc-dot {{ $item->kwitansi ? 'done' : '' }}"></span>
                                        <span class="doc-dot {{ $item->bapb ? 'done' : '' }}"></span>
                                    </div>
                                </td>
                                <td data-label="Aksi">
                                    <div class="row-actions">
                                        <a href="{{ route('belanja.edit', $item->id) }}" class="action-link edit">Edit</a>

                                        @if($item->notaPesanan)
                                            <a href="{{ route('nota-pesanan.show', $item->notaPesanan->id) }}" class="action-link done">Nota ✓</a>
                                        @else
                                            <a href="{{ route('nota-pesanan.create', $item->id) }}" class="action-link">+ Nota</a>
                                        @endif

                                        @if($item->kwitansi)
                                            <a href="{{ route('kwitansi.show', $item->kwitansi->id) }}" class="action-link done">Kwitansi ✓</a>
                                        @else
                                            <a href="{{ route('kwitansi.create', $item->id) }}" class="action-link purple">+ Kwitansi</a>
                                        @endif

                                        @if($item->bapb)
                                            <a href="{{ route('bapb.show', $item->bapb->id) }}" class="action-link done">BAPB ✓</a>
                                        @else
                                            <a href="{{ route('bapb.create', $item->id) }}" class="action-link warning">+ BAPB</a>
                                        @endif

                                        @if($hasDocuments)
                                            <button type="button" class="action-link" disabled title="Hapus tidak tersedia karena transaksi sudah memiliki dokumen LPJ.">Hapus</button>
                                        @else
                                            <button
                                                type="button"
                                                class="action-link delete js-delete"
                                                data-action="{{ route('belanja.destroy', $item->id) }}"
                                                data-uraian="{{ $item->uraian }}"
                                            >Hapus</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <div class="empty-title">
                                        {{ $hasFilters ? 'Tidak ada transaksi yang cocok.' : 'Belum ada transaksi belanja.' }}
                                    </div>
                                    <div>
                                        {{ $hasFilters ? 'Ubah atau reset filter untuk melihat data lain.' : 'Tambahkan transaksi pertama untuk mulai menyusun LPJ.' }}
                                    </div>
                                    @if(!$hasFilters)
                                        <div style="margin-top:14px;">
                                            <a href="{{ route('belanja.create') }}" class="btn btn-primary">+ Tambah Belanja</a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($belanjas->hasPages())
                <div class="pagination-wrap">
                    <div class="pagination-info">
                        Menampilkan {{ $belanjas->firstItem() }}–{{ $belanjas->lastItem() }} dari {{ $belanjas->total() }} transaksi
                    </div>
                    <nav class="pagination" aria-label="Navigasi halaman">
                        <a class="page-link {{ $belanjas->onFirstPage() ? 'disabled' : '' }}" href="{{ $belanjas->previousPageUrl() ?? '#' }}">‹</a>
                        @foreach($belanjas->getUrlRange(max(1, $belanjas->currentPage() - 2), min($belanjas->lastPage(), $belanjas->currentPage() + 2)) as $page => $url)
                            <a class="page-link {{ $page === $belanjas->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
                        @endforeach
                        <a class="page-link {{ $belanjas->hasMorePages() ? '' : 'disabled' }}" href="{{ $belanjas->nextPageUrl() ?? '#' }}">›</a>
                    </nav>
                </div>
            @endif
        </section>
    </main>

    <div class="modal-backdrop" id="deleteModal" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="deleteTitle">
            <h3 id="deleteTitle">Hapus transaksi?</h3>
            <p>Data yang belum memiliki dokumen LPJ dapat dihapus. Tindakan ini tidak dapat dibatalkan.</p>
            <div class="modal-item" id="deleteItem">-</div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelDelete">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('deleteModal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteItem = document.getElementById('deleteItem');
        const cancelDelete = document.getElementById('cancelDelete');

        document.querySelectorAll('.js-delete').forEach((button) => {
            button.addEventListener('click', () => {
                deleteForm.action = button.dataset.action;
                deleteItem.textContent = button.dataset.uraian || 'Transaksi belanja';
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
            });
        });

        function closeDeleteModal() {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }

        cancelDelete?.addEventListener('click', closeDeleteModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) closeDeleteModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeDeleteModal();
        });
    </script>
</body>
</html>
