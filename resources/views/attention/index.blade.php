<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pusat Perhatian LPJ - SILPJ</title>
    <style>
        :root {
            --primary:#1d4ed8; --primary-soft:#eff6ff;
            --success:#15803d; --success-soft:#f0fdf4;
            --warning:#b45309; --warning-soft:#fffbeb;
            --danger:#b91c1c; --danger-soft:#fef2f2;
            --text:#172033; --muted:#667085; --border:#e4e7ec;
            --surface:#fff; --background:#f5f7fb;
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--background);color:var(--text)}
        button,input,select,a{font:inherit}
        .page{width:min(1380px,100%);margin:0 auto;padding:30px 32px 48px}
        .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:24px;margin-bottom:22px}
        .page-head h1{margin:0;font-size:28px;letter-spacing:-.4px}
        .page-head p{margin:8px 0 0;color:var(--muted);font-size:14px;line-height:1.55;max-width:760px}
        .note{max-width:420px;padding:12px 14px;border:1px solid #fde68a;border-radius:10px;background:var(--warning-soft);color:#92400e;font-size:12px;line-height:1.5}
        .stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
        .stat{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px;box-shadow:0 4px 14px rgba(16,24,40,.04)}
        .stat-label{color:var(--muted);font-size:12px;font-weight:800}
        .stat-value{margin-top:8px;font-size:27px;font-weight:800;letter-spacing:-.5px}
        .stat-help{margin-top:5px;color:#98a2b3;font-size:11px;line-height:1.4}
        .panel{background:var(--surface);border:1px solid var(--border);border-radius:12px;box-shadow:0 4px 14px rgba(16,24,40,.04)}
        .filter{padding:18px;margin-bottom:18px}
        .filter-grid{display:grid;grid-template-columns:minmax(220px,1.5fr) repeat(3,minmax(145px,.8fr)) auto;gap:10px;align-items:end}
        .field label{display:block;margin-bottom:6px;color:#344054;font-size:11px;font-weight:800}
        .field input,.field select{width:100%;height:40px;border:1px solid #d0d5dd;border-radius:8px;background:#fff;padding:0 11px;color:#344054;outline:none}
        .field input:focus,.field select:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.11)}
        .filter-actions{display:flex;gap:7px}
        .btn{min-height:40px;padding:0 14px;display:inline-flex;align-items:center;justify-content:center;gap:6px;border:1px solid transparent;border-radius:8px;font-size:12px;font-weight:800;text-decoration:none;cursor:pointer;white-space:nowrap}
        .btn-primary{color:#fff;background:var(--primary)}
        .btn-light{color:#475467;background:#fff;border-color:#d0d5dd}
        .btn-warning{color:#92400e;background:#fff7ed;border-color:#fed7aa}
        .panel-head{padding:16px 18px;border-bottom:1px solid #eef0f3;display:flex;align-items:center;justify-content:space-between;gap:16px}
        .panel-head strong{font-size:14px}.panel-head span{color:var(--muted);font-size:11px}
        .items{display:grid;gap:0;padding:0 18px}
        .item{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(150px,.45fr) minmax(260px,.8fr);gap:18px;padding:17px 0;border-bottom:1px solid #eef0f3;align-items:center}
        .item:last-child{border-bottom:0}
        .item-title{font-size:14px;font-weight:800;line-height:1.45}.item-sub{margin-top:5px;color:var(--muted);font-size:11px;line-height:1.5}
        .money{margin-top:6px;font-size:13px;font-weight:800;color:#344054}
        .urgency{display:inline-flex;align-items:center;min-height:25px;padding:0 9px;border-radius:999px;font-size:10px;font-weight:900}
        .urgency-new{color:#1e40af;background:var(--primary-soft)}
        .urgency-mid{color:#92400e;background:var(--warning-soft)}
        .urgency-high{color:var(--danger);background:var(--danger-soft)}
        .age{margin-top:7px;color:var(--muted);font-size:11px}
        .docs{display:flex;flex-wrap:wrap;gap:7px;justify-content:flex-end}
        .doc{min-height:32px;padding:0 10px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-size:10px;font-weight:800}
        .doc-ready{color:#166534;background:#dcfce7}
        .doc-missing{color:#9a3412;background:#ffedd5;border:1px solid #fed7aa}
        .doc-missing:hover{background:#fed7aa}
        .missing-label{margin-bottom:7px;text-align:right;color:#667085;font-size:10px;font-weight:700}
        .empty{padding:52px 18px;text-align:center}.empty strong{display:block;font-size:16px}.empty p{margin:8px 0 0;color:var(--muted);font-size:12px;line-height:1.5}
        .pagination{padding:14px 18px 18px;border-top:1px solid #eef0f3}.pagination nav>div:first-child{display:none}.pagination nav>div:last-child{display:flex;align-items:center;justify-content:space-between;gap:14px}.pagination p{color:var(--muted);font-size:11px}.pagination a,.pagination span[aria-current="page"] span{border-radius:7px!important}
        .legend{margin-top:14px;padding:13px 15px;border:1px solid #e4e7ec;border-radius:10px;background:#fff;color:#667085;font-size:11px;line-height:1.55}
        @media(max-width:1050px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-grid{grid-template-columns:1fr 1fr}.filter-actions{grid-column:span 2}.item{grid-template-columns:minmax(0,1fr) 170px}.item>div:last-child{grid-column:1/-1}.docs{justify-content:flex-start}.missing-label{text-align:left}}
        @media(max-width:720px){.page{padding:22px 14px 36px}.page-head{display:block}.note{margin-top:14px;max-width:none}.filter-grid{grid-template-columns:1fr}.filter-actions{grid-column:auto}.item{grid-template-columns:1fr;gap:10px}.item>div:last-child{grid-column:auto}.docs{justify-content:flex-start}.missing-label{text-align:left}}
        @media(max-width:430px){.stats{grid-template-columns:1fr}.filter-actions{display:grid;grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
@include('partials.app-navigation')

<main class="page">
    <div class="page-head">
        <div>
            <h1>Pusat Perhatian LPJ</h1>
            <p>Menampilkan transaksi yang belum memiliki Nota Pesanan, Kwitansi, atau BAPB lengkap. Gunakan halaman ini sebagai daftar kerja untuk menyelesaikan LPJ secara bertahap.</p>
        </div>
        <div class="note">
            Penanda 7 dan 14 hari di halaman ini adalah <strong>prioritas internal SILPJ berdasarkan umur transaksi</strong>, bukan batas waktu hukum atau ketentuan resmi.
        </div>
    </div>

    <section class="stats" aria-label="Ringkasan perhatian LPJ">
        <div class="stat">
            <div class="stat-label">LPJ Belum Lengkap</div>
            <div class="stat-value">{{ number_format($summary['total']) }}</div>
            <div class="stat-help">Transaksi yang masih kekurangan minimal satu dokumen</div>
        </div>
        <div class="stat">
            <div class="stat-label">Umur ≥ 7 Hari</div>
            <div class="stat-value">{{ number_format($summary['age_7_plus']) }}</div>
            <div class="stat-help">Perlu ditinjau agar tidak terlalu lama tertunda</div>
        </div>
        <div class="stat">
            <div class="stat-label">Umur ≥ 14 Hari</div>
            <div class="stat-value">{{ number_format($summary['age_14_plus']) }}</div>
            <div class="stat-help">Prioritas internal tertinggi untuk diselesaikan</div>
        </div>
        <div class="stat">
            <div class="stat-label">Dokumen Masih Kurang</div>
            <div class="stat-value">{{ number_format($summary['missing_nota'] + $summary['missing_kwitansi'] + $summary['missing_bapb']) }}</div>
            <div class="stat-help">Nota {{ $summary['missing_nota'] }} · Kwitansi {{ $summary['missing_kwitansi'] }} · BAPB {{ $summary['missing_bapb'] }}</div>
        </div>
    </section>

    <section class="panel filter">
        <form method="GET" action="{{ route('attention.index') }}" class="filter-grid">
            <div class="field">
                <label for="q">Cari transaksi</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Nomor bukti atau uraian belanja">
            </div>
            <div class="field">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="">Semua kategori</option>
                    <option value="Barang" @selected(request('kategori') === 'Barang')>Barang</option>
                    <option value="Jasa" @selected(request('kategori') === 'Jasa')>Jasa</option>
                </select>
            </div>
            <div class="field">
                <label for="dokumen">Dokumen yang kurang</label>
                <select id="dokumen" name="dokumen">
                    <option value="">Semua dokumen</option>
                    <option value="nota" @selected(request('dokumen') === 'nota')>Nota Pesanan</option>
                    <option value="kwitansi" @selected(request('dokumen') === 'kwitansi')>Kwitansi</option>
                    <option value="bapb" @selected(request('dokumen') === 'bapb')>BAPB</option>
                </select>
            </div>
            <div class="field">
                <label for="umur">Umur transaksi</label>
                <select id="umur" name="umur">
                    <option value="">Semua umur</option>
                    <option value="baru" @selected(request('umur') === 'baru')>&lt; 7 hari</option>
                    <option value="7_13" @selected(request('umur') === '7_13')>7–13 hari</option>
                    <option value="14_plus" @selected(request('umur') === '14_plus')>≥ 14 hari</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="{{ route('attention.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head">
            <strong>Transaksi yang perlu diselesaikan</strong>
            <span>{{ $items->total() }} transaksi ditemukan</span>
        </div>

        @if($items->count())
            <div class="items">
                @foreach($items as $belanja)
                    @php
                        $ageDays = max(0, (int) \Carbon\Carbon::parse($belanja->tanggal)->startOfDay()->diffInDays(now()->startOfDay(), false));
                        $urgencyClass = $ageDays >= 14 ? 'high' : ($ageDays >= 7 ? 'mid' : 'new');
                        $urgencyLabel = $ageDays >= 14 ? 'Prioritas tinggi' : ($ageDays >= 7 ? 'Perlu ditinjau' : 'Baru');
                        $missing = collect([
                            !$belanja->notaPesanan ? 'Nota Pesanan' : null,
                            !$belanja->kwitansi ? 'Kwitansi' : null,
                            !$belanja->bapb ? 'BAPB' : null,
                        ])->filter()->values();
                    @endphp
                    <article class="item">
                        <div>
                            <div class="item-title">{{ $belanja->uraian }}</div>
                            <div class="item-sub">
                                {{ \Carbon\Carbon::parse($belanja->tanggal)->translatedFormat('d M Y') }} · {{ $belanja->nomor_bukti ?: 'Tanpa nomor bukti' }} · {{ $belanja->kategori }}
                            </div>
                            <div class="money">Rp {{ number_format($belanja->total, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="urgency urgency-{{ $urgencyClass }}">{{ $urgencyLabel }}</span>
                            <div class="age">Umur transaksi: {{ $ageDays }} hari</div>
                        </div>
                        <div>
                            <div class="missing-label">Kurang: {{ $missing->join(', ') }}</div>
                            <div class="docs">
                                @if($belanja->notaPesanan)
                                    <a class="doc doc-ready" href="{{ route('nota-pesanan.show', $belanja->notaPesanan->id) }}">Nota ✓</a>
                                @else
                                    <a class="doc doc-missing" href="{{ route('nota-pesanan.create', $belanja->id) }}">+ Buat Nota</a>
                                @endif

                                @if($belanja->kwitansi)
                                    <a class="doc doc-ready" href="{{ route('kwitansi.show', $belanja->kwitansi->id) }}">Kwitansi ✓</a>
                                @else
                                    <a class="doc doc-missing" href="{{ route('kwitansi.create', $belanja->id) }}">+ Buat Kwitansi</a>
                                @endif

                                @if($belanja->bapb)
                                    <a class="doc doc-ready" href="{{ route('bapb.show', $belanja->bapb->id) }}">BAPB ✓</a>
                                @else
                                    <a class="doc doc-missing" href="{{ route('bapb.create', $belanja->id) }}">+ Buat BAPB</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if($items->hasPages())
                <div class="pagination">{{ $items->links() }}</div>
            @endif
        @else
            <div class="empty">
                <strong>Tidak ada LPJ yang perlu perhatian.</strong>
                <p>Jika filter sedang aktif, coba reset filter. Jika tidak, seluruh transaksi Anda sudah memiliki dokumen LPJ lengkap.</p>
            </div>
        @endif
    </section>

    <div class="legend">
        <strong>Cara membaca prioritas:</strong> kurang dari 7 hari ditandai “Baru”, 7–13 hari “Perlu ditinjau”, dan 14 hari atau lebih “Prioritas tinggi”. Kategori ini hanya membantu pengurutan pekerjaan internal dan tidak menggantikan ketentuan administrasi sekolah atau aturan resmi yang berlaku.
    </div>
</main>
</body>
</html>
