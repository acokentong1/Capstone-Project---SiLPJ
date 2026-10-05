<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard SILPJ</title>

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
            --text: #172033;
            --muted: #667085;
            --border: #e4e7ec;
            --surface: #ffffff;
            --background: #f5f7fb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--background);
            color: var(--text);
        }

        button, a {
            font: inherit;
        }

        .header {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: white;
            padding: 24px 40px;
            box-shadow: 0 8px 24px rgba(29, 78, 216, 0.18);
        }

        .header-inner,
        .container {
            width: min(1380px, 100%);
            margin: 0 auto;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
        }

        .brand-title {
            margin: 0;
            font-size: 28px;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            margin: 7px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 700;
            font-size: 14px;
        }

        .user-school {
            margin-top: 3px;
            font-size: 12px;
            opacity: 0.8;
        }

        .logout-btn {
            border: 1px solid rgba(255, 255, 255, 0.45);
            background: rgba(255, 255, 255, 0.12);
            color: white;
            padding: 9px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: 0.2s;
        }

        .logout-btn:hover {
            background: white;
            color: var(--danger);
        }

        .container {
            padding: 30px 32px 40px;
        }

        .welcome {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            padding: 24px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(16, 24, 40, 0.04);
        }

        .welcome h2 {
            margin: 0 0 7px;
            font-size: 23px;
        }

        .welcome p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 9px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 8px;
            padding: 10px 14px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid transparent;
            transition: 0.18s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            color: #344054;
            background: white;
            border-color: #d0d5dd;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .alert {
            margin-top: 18px;
            padding: 15px 17px;
            border: 1px solid #fcd34d;
            background: var(--warning-soft);
            color: #92400e;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            line-height: 1.5;
        }

        .alert a {
            color: #92400e;
            font-weight: 700;
            white-space: nowrap;
        }

        .section-title {
            margin: 28px 0 14px;
            font-size: 18px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 19px;
            box-shadow: 0 2px 8px rgba(16, 24, 40, 0.035);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .stat-value {
            margin-top: 8px;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .stat-note {
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: var(--primary-soft);
            display: grid;
            place-items: center;
            font-size: 19px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(280px, 0.9fr);
            gap: 18px;
            margin-top: 18px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(16, 24, 40, 0.035);
        }

        .panel-header {
            padding: 18px 19px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .panel-title {
            margin: 0;
            font-size: 16px;
        }

        .panel-link {
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .panel-link:hover {
            text-decoration: underline;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th, td {
            padding: 13px 16px;
            border-bottom: 1px solid #eef0f3;
            text-align: left;
            vertical-align: middle;
            font-size: 13px;
        }

        th {
            color: #475467;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.45px;
            background: #fcfcfd;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .money {
            white-space: nowrap;
            font-weight: 700;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-success {
            color: var(--success);
            background: var(--success-soft);
        }

        .badge-warning {
            color: var(--warning);
            background: var(--warning-soft);
        }

        .docs {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .doc-chip {
            padding: 4px 7px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
        }

        .doc-ready {
            color: #166534;
            background: #dcfce7;
        }

        .doc-missing {
            color: #9a3412;
            background: #ffedd5;
        }

        .progress-panel {
            padding: 19px;
        }

        .progress-number {
            margin: 2px 0 5px;
            font-size: 36px;
            font-weight: 800;
            color: var(--primary);
        }

        .progress-caption {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .progress-track {
            height: 10px;
            background: #e9eef8;
            border-radius: 999px;
            overflow: hidden;
            margin: 15px 0 17px;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary);
            border-radius: inherit;
        }

        .document-summary {
            display: grid;
            gap: 10px;
        }

        .document-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #eef0f3;
            font-size: 13px;
        }

        .document-row:last-child {
            border-bottom: 0;
        }

        .document-row span {
            color: var(--muted);
        }

        .document-row strong {
            color: var(--text);
        }

        .empty-state {
            padding: 35px 20px;
            text-align: center;
            color: var(--muted);
        }

        .empty-state strong {
            display: block;
            color: var(--text);
            margin-bottom: 7px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 15px;
        }

        .menu-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            padding: 20px;
            min-height: 132px;
            transition: 0.18s ease;
            box-shadow: 0 2px 8px rgba(16, 24, 40, 0.03);
        }

        .menu-card:hover {
            transform: translateY(-2px);
            border-color: #93b4f7;
            box-shadow: 0 8px 20px rgba(16, 24, 40, 0.075);
        }

        .menu-card .icon {
            font-size: 24px;
            margin-bottom: 13px;
        }

        .menu-card h3 {
            margin: 0 0 6px;
            font-size: 16px;
        }

        .menu-card p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .footer {
            padding: 30px 15px;
            text-align: center;
            color: #98a2b3;
            font-size: 12px;
        }

        @media (max-width: 1050px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 820px) {
            .menu-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .welcome {
                align-items: flex-start;
                flex-direction: column;
            }

            .quick-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 620px) {
            .header {
                padding: 20px 18px;
            }

            .header-top {
                align-items: flex-start;
                flex-direction: column;
            }

            .user-area {
                width: 100%;
                justify-content: space-between;
            }

            .user-info {
                text-align: left;
            }

            .container {
                padding: 20px 15px 30px;
            }

            .stats-grid,
            .menu-grid {
                grid-template-columns: 1fr;
            }

            .welcome h2 {
                font-size: 20px;
            }

            .alert {
                align-items: flex-start;
                flex-direction: column;
            }

            .quick-actions,
            .quick-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
@include('partials.app-navigation')
    <header class="header">
        <div class="header-inner header-top">
            <div>
                <h1 class="brand-title">Dashboard SILPJ</h1>
                <p class="brand-subtitle">Ringkasan transaksi dan kelengkapan dokumen pertanggungjawaban sekolah.</p>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="welcome">
            <div>
                <h2>Selamat Datang, {{ auth()->user()->name }}</h2>
                <p>
                    Pantau transaksi, kelengkapan dokumen, dan progres LPJ sekolah dari satu halaman.
                </p>
            </div>

            <div class="quick-actions">
                <a href="{{ route('belanja.create') }}" class="btn btn-primary">+ Tambah Belanja</a>
                @if(($attentionSummary['total'] ?? 0) > 0)
                    <a href="{{ route('attention.index') }}" class="btn btn-secondary">Perlu Perhatian ({{ $attentionSummary['total'] }})</a>
                @endif
                <a href="{{ route('laporan-lpj.index') }}" class="btn btn-secondary">Lihat Laporan LPJ</a>
            </div>
        </section>

        @if (!$school)
            <div class="alert" role="alert">
                <div>
                    <strong>Profil sekolah belum lengkap.</strong>
                    Lengkapi data sekolah agar Nota Pesanan, Kwitansi, dan BAPB dapat dibuat dengan identitas yang benar.
                </div>
                <a href="{{ route('school-profile.create') }}">Lengkapi sekarang →</a>
            </div>
        @endif

        @if (($attentionSummary['total'] ?? 0) > 0)
            <div class="alert" role="status">
                <div>
                    <strong>{{ $attentionSummary['total'] }} transaksi masih memerlukan dokumen LPJ.</strong>
                    @if(($attentionSummary['age_14_plus'] ?? 0) > 0)
                        {{ $attentionSummary['age_14_plus'] }} transaksi sudah berumur 14 hari atau lebih dan menjadi prioritas internal tertinggi.
                    @elseif(($attentionSummary['age_7_plus'] ?? 0) > 0)
                        {{ $attentionSummary['age_7_plus'] }} transaksi sudah berumur 7 hari atau lebih dan sebaiknya ditinjau.
                    @else
                        Selesaikan dokumen secara bertahap melalui Pusat Perhatian LPJ.
                    @endif
                </div>
                <a href="{{ route('attention.index') }}">Buka pusat perhatian →</a>
            </div>
        @endif

        <h2 class="section-title">Ringkasan LPJ</h2>

        <section class="stats-grid" aria-label="Ringkasan LPJ">
            <article class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Total Belanja</div>
                        <div class="stat-value">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</div>
                        <div class="stat-note">Akumulasi seluruh transaksi</div>
                    </div>
                    <div class="stat-icon">💰</div>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Jumlah Transaksi</div>
                        <div class="stat-value">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</div>
                        <div class="stat-note">Data belanja yang tercatat</div>
                    </div>
                    <div class="stat-icon">🛒</div>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">LPJ Lengkap</div>
                        <div class="stat-value">{{ number_format($jumlahLengkap, 0, ',', '.') }}</div>
                        <div class="stat-note">Nota, kwitansi, dan BAPB tersedia</div>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Belum Lengkap</div>
                        <div class="stat-value">{{ number_format($jumlahBelumLengkap, 0, ',', '.') }}</div>
                        <div class="stat-note">Transaksi yang masih perlu dokumen</div>
                    </div>
                    <div class="stat-icon">⏳</div>
                </div>
            </article>
        </section>

        <section class="dashboard-grid">
            <article class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Transaksi Terbaru</h2>
                    <a href="{{ route('belanja.index') }}" class="panel-link">Lihat semua</a>
                </div>

                @if ($transaksiTerbaru->isEmpty())
                    <div class="empty-state">
                        <strong>Belum ada transaksi belanja.</strong>
                        Tambahkan transaksi pertama untuk mulai menyusun dokumen LPJ.
                        <div style="margin-top: 14px;">
                            <a href="{{ route('belanja.create') }}" class="btn btn-primary">Tambah Belanja</a>
                        </div>
                    </div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Uraian</th>
                                    <th>Total</th>
                                    <th>Dokumen</th>
                                    <th>Status LPJ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transaksiTerbaru as $belanja)
                                    @php
                                        $lengkap = $belanja->notaPesanan && $belanja->kwitansi && $belanja->bapb;
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($belanja->tanggal)->format('d/m/Y') }}</td>
                                        <td>
                                            <strong>{{ $belanja->uraian }}</strong><br>
                                            <span style="color:#98a2b3;font-size:11px;">{{ $belanja->kategori }}</span>
                                        </td>
                                        <td class="money">Rp {{ number_format($belanja->total, 0, ',', '.') }}</td>
                                        <td>
                                            <div class="docs">
                                                @if ($belanja->notaPesanan)
                                                    <a class="doc-chip doc-ready" href="{{ route('nota-pesanan.show', $belanja->notaPesanan->id) }}">Nota ✓</a>
                                                @else
                                                    <a class="doc-chip doc-missing" href="{{ route('nota-pesanan.create', $belanja->id) }}">+ Nota</a>
                                                @endif

                                                @if ($belanja->kwitansi)
                                                    <a class="doc-chip doc-ready" href="{{ route('kwitansi.show', $belanja->kwitansi->id) }}">Kwitansi ✓</a>
                                                @else
                                                    <a class="doc-chip doc-missing" href="{{ route('kwitansi.create', $belanja->id) }}">+ Kwitansi</a>
                                                @endif

                                                @if ($belanja->bapb)
                                                    <a class="doc-chip doc-ready" href="{{ route('bapb.show', $belanja->bapb->id) }}">BAPB ✓</a>
                                                @else
                                                    <a class="doc-chip doc-missing" href="{{ route('bapb.create', $belanja->id) }}">+ BAPB</a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($lengkap)
                                                <span class="badge badge-success">Lengkap</span>
                                            @else
                                                <span class="badge badge-warning">Belum lengkap</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>

            <aside class="panel progress-panel">
                <h2 class="panel-title">Progres Kelengkapan</h2>
                <div class="progress-number">{{ $persentaseLengkap }}%</div>
                <div class="progress-caption">
                    {{ $jumlahLengkap }} dari {{ $jumlahTransaksi }} transaksi telah memiliki dokumen LPJ lengkap.
                </div>

                <div class="progress-track" aria-label="Progres kelengkapan LPJ">
                    <div class="progress-bar" style="width: {{ $persentaseLengkap }}%;"></div>
                </div>

                <div class="document-summary">
                    <div class="document-row">
                        <span>Nota Pesanan</span>
                        <strong>{{ $jumlahNota }} / {{ $jumlahTransaksi }}</strong>
                    </div>
                    <div class="document-row">
                        <span>Kwitansi</span>
                        <strong>{{ $jumlahKwitansi }} / {{ $jumlahTransaksi }}</strong>
                    </div>
                    <div class="document-row">
                        <span>BAPB</span>
                        <strong>{{ $jumlahBapb }} / {{ $jumlahTransaksi }}</strong>
                    </div>
                </div>

                <a href="{{ route('laporan-lpj.index') }}" class="btn btn-primary" style="width:100%;margin-top:16px;">
                    Periksa Laporan LPJ
                </a>
            </aside>
        </section>

        <h2 class="section-title">Menu Utama</h2>

        <section class="menu-grid">
            <a href="{{ route('school-profile.index') }}" class="menu-card">
                <div class="icon">🏫</div>
                <h3>Profil Sekolah</h3>
                <p>Kelola identitas sekolah, kepala sekolah, bendahara, NPSN, dan alamat.</p>
            </a>

            <a href="{{ route('belanja.index') }}" class="menu-card">
                <div class="icon">🛒</div>
                <h3>Data Belanja</h3>
                <p>Input, edit, dan kelola seluruh transaksi pengeluaran sekolah.</p>
            </a>

            <a href="{{ route('nota-pesanan.index') }}" class="menu-card">
                <div class="icon">📄</div>
                <h3>Nota Pesanan</h3>
                <p>Lihat riwayat Nota Pesanan dan dokumen berdasarkan transaksi belanja.</p>
            </a>

            <a href="{{ route('kwitansi.index') }}" class="menu-card">
                <div class="icon">🧾</div>
                <h3>Kwitansi</h3>
                <p>Kelola dan cetak bukti pembayaran transaksi sekolah.</p>
            </a>

            <a href="{{ route('bapb.index') }}" class="menu-card">
                <div class="icon">📦</div>
                <h3>BAPB</h3>
                <p>Kelola Berita Acara Pemeriksaan Barang dan dokumen pemeriksaan.</p>
            </a>

            <a href="{{ route('laporan-lpj.index') }}" class="menu-card">
                <div class="icon">📊</div>
                <h3>Laporan LPJ</h3>
                <p>Rekap transaksi dan periksa kelengkapan seluruh dokumen LPJ.</p>
            </a>

            <a href="{{ route('attention.index') }}" class="menu-card">
                <div class="icon">🔔</div>
                <h3>Perlu Perhatian</h3>
                <p>Prioritaskan transaksi yang masih kekurangan Nota Pesanan, Kwitansi, atau BAPB.</p>
            </a>
        </section>
    </main>

    <footer class="footer">
        SILPJ Online · Sistem Informasi Laporan Pertanggungjawaban Sekolah
    </footer>
</body>
</html>
