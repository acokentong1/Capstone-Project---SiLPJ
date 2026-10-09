<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan LPJ</title>

    <style>
        @include('documents._app-styles')

        .report-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:16px;margin-bottom:18px}
        .panel{background:#fff;border:1px solid var(--border);border-radius:12px;padding:18px;box-shadow:0 3px 12px rgba(16,24,40,.04)}
        .panel h3{margin:0 0 14px;font-size:15px}.panel-sub{margin:-7px 0 15px;color:var(--muted);font-size:12px;line-height:1.45}
        .progress-row{display:grid;grid-template-columns:125px 1fr 72px;gap:10px;align-items:center;margin:11px 0}.progress-label{font-size:12px;font-weight:700;color:#475467}.progress-track{height:9px;border-radius:999px;background:#eef2f6;overflow:hidden}.progress-bar{height:100%;background:#2563eb;border-radius:999px}.progress-number{text-align:right;font-size:12px;font-weight:800;color:#344054}.missing-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.missing-box{padding:12px;border:1px solid #e4e7ec;border-radius:9px;background:#fafbfc}.missing-box strong{display:block;font-size:20px;margin-top:5px}.missing-box span{font-size:11px;color:var(--muted);font-weight:700}
        .category-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.category-box{padding:15px;border-radius:10px;border:1px solid #dbeafe;background:#eff6ff}.category-box.jasa{border-color:#d1fae5;background:#f0fdf4}.category-label{font-size:11px;text-transform:uppercase;letter-spacing:.3px;font-weight:800;color:#475467}.category-value{font-size:20px;font-weight:800;margin:7px 0 4px}.category-note{font-size:12px;color:#667085}
        .report-filter-grid{display:grid;grid-template-columns:minmax(210px,1.4fr) minmax(135px,.7fr) minmax(150px,.8fr) minmax(150px,.8fr) minmax(145px,.75fr) minmax(145px,.75fr);gap:10px;align-items:end}.report-filter-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px;flex-wrap:wrap}
        .filter-note{font-size:12px;color:#667085;margin-top:10px}.filter-note strong{color:#344054}
        .status-docs{display:flex;justify-content:center;gap:5px}.doc-pill{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:28px;padding:0 7px;border-radius:7px;font-size:10px;font-weight:900;text-decoration:none}.doc-pill.done{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}.doc-pill.missing{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}.doc-pill:hover{filter:brightness(.98)}
        .row-incomplete{background:#fffdf8}.row-empty{background:#fffbfa}.screen-status{min-width:96px;justify-content:center}.table-summary{display:flex;gap:14px;flex-wrap:wrap;color:#667085;font-size:12px}.table-summary strong{color:#344054}.report-table{min-width:1240px!important}.report-table tfoot td{background:#f8fafc;font-weight:800;border-top:2px solid #d0d5dd}.print-only{display:none}
        .paper-report{display:none}

        @media(max-width:1100px){.report-filter-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.report-grid{grid-template-columns:1fr}}
        @media(max-width:720px){.report-filter-grid{grid-template-columns:1fr 1fr}.missing-grid{grid-template-columns:1fr}.progress-row{grid-template-columns:100px 1fr 60px}.category-grid{grid-template-columns:1fr}}
        @media(max-width:520px){.report-filter-grid{grid-template-columns:1fr}.report-filter-actions .btn{flex:1}.table-meta{align-items:flex-start;flex-direction:column}}

        @media print{
            @page{size:A4 landscape;margin:8mm}
            body{background:#fff;color:#000;font-size:9px}
            .no-print,.header,.container > *{display:none!important}
            .container{display:block!important;width:auto!important;max-width:none!important;padding:0!important;margin:0!important}
            .paper-report{display:block!important}
            .print-only{display:block!important}
            .print-head{text-align:center;border-bottom:3px double #000;padding-bottom:7px;margin-bottom:9px}.print-head h1{margin:0 0 3px;font-size:15px;text-transform:uppercase}.print-head p{margin:2px 0;font-size:8px}
            .print-title{text-align:center;margin:8px 0 10px}.print-title h2{font-size:13px;text-decoration:underline;margin:0 0 3px}.print-title p{margin:2px 0;font-size:8px}
            .print-summary{display:grid;grid-template-columns:repeat(5,1fr);gap:5px;margin-bottom:8px}.print-summary div{border:1px solid #999;padding:5px;text-align:center}.print-summary span{display:block;font-size:7px}.print-summary strong{font-size:10px}
            table.print-data{width:100%;border-collapse:collapse;font-size:7.2px}table.print-data th,table.print-data td{border:1px solid #777;padding:3px 4px;vertical-align:top}table.print-data th{text-align:center;background:#eee!important;color:#000!important}.p-center{text-align:center}.p-right{text-align:right}.p-status{font-weight:700}.print-total{font-weight:800;background:#f3f3f3!important}
            .print-signatures{width:100%;border-collapse:collapse;margin-top:14px;page-break-inside:avoid}.print-signatures td{border:none;width:50%;text-align:center;font-size:8px;vertical-align:top}.sign-space{height:34px}.sign-name{font-weight:800;text-decoration:underline}
            .print-footer{font-size:7px;margin-top:6px;color:#444;text-align:right}
        }
    </style>
</head>
<body>
@include('partials.app-navigation')
    <header class="header no-print">
        <div class="header-inner">
            <div class="brand">
                <h1>SILPJ</h1>
                <p>Laporan Pertanggungjawaban dan Rekap Kelengkapan Dokumen</p>
            </div>
            <div class="actions">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Dashboard</a>
                <a href="{{ route('belanja.index') }}" class="btn btn-secondary">Data Belanja</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="topbar no-print">
            <div class="page-title">
                <h2>Laporan LPJ</h2>
                <p>Rekap transaksi, nilai belanja, serta kelengkapan Nota Pesanan, Kwitansi, dan BAPB dalam satu halaman.</p>
            </div>
            <div class="actions">
                <a href="{{ route('laporan-lpj.export-csv', request()->query()) }}" class="btn btn-success">Ekspor CSV (Excel)</a>
                <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Laporan</button>
            </div>
        </div>

        @if(!$school)
            <div class="alert alert-warning no-print">
                Profil sekolah belum tersedia. Laporan tetap dapat dilihat, tetapi identitas dan tanda tangan akan belum lengkap saat dicetak.
                <a href="{{ route('school-profile.create') }}" style="font-weight:800;color:inherit">Lengkapi profil sekolah</a>.
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success no-print">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error no-print">{{ session('error') }}</div>
        @endif

        <section class="stats-grid four no-print">
            <div class="stat-card">
                <div class="stat-label">Transaksi dalam laporan</div>
                <div class="stat-value">{{ number_format($stats['jumlahTransaksi'], 0, ',', '.') }}</div>
                <div class="stat-note">Periode: {{ $periodeLabel }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total belanja</div>
                <div class="stat-value">Rp {{ number_format($stats['totalBelanja'], 0, ',', '.') }}</div>
                <div class="stat-note">Nilai sesuai filter aktif</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">LPJ lengkap</div>
                <div class="stat-value">{{ $stats['jumlahLengkap'] }} / {{ $stats['jumlahTransaksi'] }}</div>
                <div class="stat-note">{{ $stats['persenLpjLengkap'] }}% transaksi sudah lengkap</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Kelengkapan dokumen</div>
                <div class="stat-value">{{ $stats['persenKelengkapanDokumen'] }}%</div>
                <div class="stat-note">Dihitung dari seluruh Nota, Kwitansi, dan BAPB</div>
            </div>
        </section>

        <section class="report-grid no-print">
            <div class="panel">
                <h3>Status Kelengkapan Dokumen</h3>
                <p class="panel-sub">Gunakan bagian ini untuk melihat dokumen mana yang paling banyak belum dibuat.</p>

                @php
                    $maxDocs = max(1, $stats['jumlahTransaksi']);
                    $notaPercent = (int) round(($stats['jumlahNota'] / $maxDocs) * 100);
                    $kwitansiPercent = (int) round(($stats['jumlahKwitansi'] / $maxDocs) * 100);
                    $bapbPercent = (int) round(($stats['jumlahBapb'] / $maxDocs) * 100);
                @endphp

                <div class="progress-row">
                    <div class="progress-label">Nota Pesanan</div>
                    <div class="progress-track"><div class="progress-bar" style="width:{{ $notaPercent }}%"></div></div>
                    <div class="progress-number">{{ $stats['jumlahNota'] }}/{{ $stats['jumlahTransaksi'] }}</div>
                </div>
                <div class="progress-row">
                    <div class="progress-label">Kwitansi</div>
                    <div class="progress-track"><div class="progress-bar" style="width:{{ $kwitansiPercent }}%"></div></div>
                    <div class="progress-number">{{ $stats['jumlahKwitansi'] }}/{{ $stats['jumlahTransaksi'] }}</div>
                </div>
                <div class="progress-row">
                    <div class="progress-label">BAPB</div>
                    <div class="progress-track"><div class="progress-bar" style="width:{{ $bapbPercent }}%"></div></div>
                    <div class="progress-number">{{ $stats['jumlahBapb'] }}/{{ $stats['jumlahTransaksi'] }}</div>
                </div>

                <div class="missing-grid" style="margin-top:16px">
                    <div class="missing-box"><span>Nota belum dibuat</span><strong>{{ $stats['kurangNota'] }}</strong></div>
                    <div class="missing-box"><span>Kwitansi belum dibuat</span><strong>{{ $stats['kurangKwitansi'] }}</strong></div>
                    <div class="missing-box"><span>BAPB belum dibuat</span><strong>{{ $stats['kurangBapb'] }}</strong></div>
                </div>
            </div>

            <div class="panel">
                <h3>Komposisi Belanja</h3>
                <p class="panel-sub">Ringkasan nominal berdasarkan kategori transaksi pada laporan saat ini.</p>
                <div class="category-grid">
                    <div class="category-box">
                        <div class="category-label">Barang</div>
                        <div class="category-value">Rp {{ number_format($stats['totalBarang'], 0, ',', '.') }}</div>
                        <div class="category-note">{{ $stats['jumlahBarang'] }} transaksi</div>
                    </div>
                    <div class="category-box jasa">
                        <div class="category-label">Jasa</div>
                        <div class="category-value">Rp {{ number_format($stats['totalJasa'], 0, ',', '.') }}</div>
                        <div class="category-note">{{ $stats['jumlahJasa'] }} transaksi</div>
                    </div>
                </div>

                <div style="margin-top:14px;padding:13px;border-radius:9px;background:#f8fafc;border:1px solid #e4e7ec">
                    <div style="font-size:11px;color:#667085;font-weight:800;text-transform:uppercase">Nilai transaksi dengan LPJ lengkap</div>
                    <div style="font-size:20px;font-weight:800;margin-top:6px">Rp {{ number_format($stats['nilaiLpjLengkap'], 0, ',', '.') }}</div>
                    <div style="font-size:12px;color:#667085;margin-top:4px">{{ $stats['jumlahProses'] }} transaksi masih dalam proses dan {{ $stats['jumlahBelumAdaDokumen'] }} belum memiliki dokumen.</div>
                </div>
            </div>
        </section>

        <section class="card filter-card no-print">
            <form method="GET" action="{{ route('laporan-lpj.index') }}">
                <div class="report-filter-grid">
                    <div class="field">
                        <label for="q">Cari transaksi</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="No. bukti atau uraian">
                    </div>
                    <div class="field">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori">
                            <option value="">Semua</option>
                            <option value="Barang" @selected($filters['kategori'] === 'Barang')>Barang</option>
                            <option value="Jasa" @selected($filters['kategori'] === 'Jasa')>Jasa</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="status">Status LPJ</label>
                        <select id="status" name="status">
                            <option value="">Semua</option>
                            <option value="lengkap" @selected($filters['status'] === 'lengkap')>Lengkap</option>
                            <option value="proses" @selected($filters['status'] === 'proses')>Dalam proses</option>
                            <option value="belum" @selected($filters['status'] === 'belum')>Belum ada dokumen</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="dokumen_kurang">Dokumen yang kurang</label>
                        <select id="dokumen_kurang" name="dokumen_kurang">
                            <option value="">Semua</option>
                            <option value="nota" @selected($filters['dokumen_kurang'] === 'nota')>Nota Pesanan</option>
                            <option value="kwitansi" @selected($filters['dokumen_kurang'] === 'kwitansi')>Kwitansi</option>
                            <option value="bapb" @selected($filters['dokumen_kurang'] === 'bapb')>BAPB</option>
                        </select>
                    </div>
                    
<div class="field">
    <label for="tahun">Tahun Anggaran</label>
    <select name="tahun" id="tahun">
        <option value="">Semua Tahun</option>
        @foreach(($tahunAnggaran ?? []) as $tahun)
            <option value="{{ $tahun }}" @selected(($filters['tahun'] ?? '') == $tahun)>{{ $tahun }}</option>
        @endforeach
    </select>
</div>

<div class="field">
                        <label for="tanggal_mulai">Tanggal mulai</label>
                        <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ $filters['tanggal_mulai'] }}">
                    </div>
                    <div class="field">
                        <label for="tanggal_selesai">Tanggal selesai</label>
                        <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ $filters['tanggal_selesai'] }}">
                    </div>
                </div>
                <div class="report-filter-actions">
                    @if($hasFilters)
                        <a href="{{ route('laporan-lpj.index') }}" class="btn btn-secondary">Reset Filter</a>
                    @endif
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                </div>
                <div class="filter-note">
                    <strong>{{ $stats['jumlahTransaksi'] }} transaksi</strong> ditampilkan. Filter yang aktif juga digunakan saat mencetak dan mengekspor CSV.
                </div>
            </form>
        </section>

        <section class="card table-card no-print">
            <div class="table-meta">
                <div>
                    <strong>Rekap Transaksi LPJ</strong>
                    <span style="display:block;margin-top:4px">Periode {{ $periodeLabel }}</span>
                </div>
                <div class="table-summary">
                    <span>Lengkap: <strong>{{ $stats['jumlahLengkap'] }}</strong></span>
                    <span>Proses: <strong>{{ $stats['jumlahProses'] }}</strong></span>
                    <span>Belum: <strong>{{ $stats['jumlahBelumAdaDokumen'] }}</strong></span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data report-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>No. Bukti</th>
                            <th>Uraian / Kategori</th>
                            <th class="center">Jumlah</th>
                            <th class="right">Harga Satuan</th>
                            <th class="right">Total</th>
                            <th class="center">Nota</th>
                            <th class="center">Kwitansi</th>
                            <th class="center">BAPB</th>
                            <th class="center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($belanjas as $item)
                            @php
                                $jumlahDokumen = (int) (bool) $item->notaPesanan + (int) (bool) $item->kwitansi + (int) (bool) $item->bapb;
                                $rowClass = $jumlahDokumen === 0 ? 'row-empty' : ($jumlahDokumen < 3 ? 'row-incomplete' : '');
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="center">{{ $loop->iteration }}</td>
                                <td class="nowrap">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</td>
                                <td class="strong">{{ $item->nomor_bukti ?? '-' }}</td>
                                <td>
                                    <div class="strong">{{ $item->uraian }}</div>
                                    <div class="muted" style="margin-top:4px">{{ $item->kategori ?? '-' }}</div>
                                </td>
                                <td class="center">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td class="right nowrap">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td class="right nowrap strong">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                <td class="center">
                                    @if($item->notaPesanan)
                                        <a class="doc-pill done" title="Buka Nota Pesanan" href="{{ route('nota-pesanan.show', $item->notaPesanan->id) }}">✓ Nota</a>
                                    @else
                                        <a class="doc-pill missing" title="Buat Nota Pesanan" href="{{ route('nota-pesanan.create', $item->id) }}">+ Nota</a>
                                    @endif
                                </td>
                                <td class="center">
                                    @if($item->kwitansi)
                                        <a class="doc-pill done" title="Buka Kwitansi" href="{{ route('kwitansi.show', $item->kwitansi->id) }}">✓ Kwit</a>
                                    @else
                                        <a class="doc-pill missing" title="Buat Kwitansi" href="{{ route('kwitansi.create', $item->id) }}">+ Kwit</a>
                                    @endif
                                </td>
                                <td class="center">
                                    @if($item->bapb)
                                        <a class="doc-pill done" title="Buka BAPB" href="{{ route('bapb.show', $item->bapb->id) }}">✓ BAPB</a>
                                    @else
                                        <a class="doc-pill missing" title="Buat BAPB" href="{{ route('bapb.create', $item->id) }}">+ BAPB</a>
                                    @endif
                                </td>
                                <td class="center">
                                    @if($jumlahDokumen === 3)
                                        <span class="badge badge-success screen-status">Lengkap</span>
                                    @elseif($jumlahDokumen === 0)
                                        <span class="badge badge-danger screen-status">Belum ada</span>
                                    @else
                                        <span class="badge badge-warning screen-status">Proses {{ $jumlahDokumen }}/3</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="empty-state">
                                    <div class="empty-title">Tidak ada transaksi yang sesuai</div>
                                    <div>Coba ubah filter atau tambahkan transaksi baru.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($belanjas->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="6" class="right">TOTAL KESELURUHAN</td>
                                <td class="right nowrap">Rp {{ number_format($stats['totalBelanja'], 0, ',', '.') }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        <section class="paper-report">
            <div class="print-head">
                <h1>{{ $school?->nama_sekolah ?? 'NAMA SEKOLAH' }}</h1>
                <p>NPSN: {{ $school?->npsn ?? '-' }}</p>
                <p>{{ $school?->alamat ?? '-' }}</p>
            </div>

            <div class="print-title">
                <h2>LAPORAN PERTANGGUNGJAWABAN</h2>
                <p>Rekapitulasi Belanja dan Kelengkapan Dokumen</p>
                <p><strong>Periode: {{ $periodeLabel }}</strong></p>
            </div>

            <div class="print-summary">
                <div><span>Transaksi</span><strong>{{ $stats['jumlahTransaksi'] }}</strong></div>
                <div><span>Total Belanja</span><strong>Rp {{ number_format($stats['totalBelanja'], 0, ',', '.') }}</strong></div>
                <div><span>LPJ Lengkap</span><strong>{{ $stats['jumlahLengkap'] }}</strong></div>
                <div><span>Dalam Proses</span><strong>{{ $stats['jumlahProses'] }}</strong></div>
                <div><span>Kelengkapan Dokumen</span><strong>{{ $stats['persenKelengkapanDokumen'] }}%</strong></div>
            </div>

            <table class="print-data">
                <thead>
                    <tr>
                        <th style="width:25px">No</th>
                        <th style="width:68px">Tanggal</th>
                        <th style="width:70px">No. Bukti</th>
                        <th>Uraian</th>
                        <th style="width:50px">Kategori</th>
                        <th style="width:35px">Jml</th>
                        <th style="width:72px">Harga Satuan</th>
                        <th style="width:80px">Total</th>
                        <th style="width:38px">Nota</th>
                        <th style="width:45px">Kwit.</th>
                        <th style="width:38px">BAPB</th>
                        <th style="width:62px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($belanjas as $item)
                        @php
                            $jumlahDokumenPrint = (int) (bool) $item->notaPesanan + (int) (bool) $item->kwitansi + (int) (bool) $item->bapb;
                            $statusPrint = $jumlahDokumenPrint === 3 ? 'Lengkap' : ($jumlahDokumenPrint === 0 ? 'Belum ada' : 'Proses '.$jumlahDokumenPrint.'/3');
                        @endphp
                        <tr>
                            <td class="p-center">{{ $loop->iteration }}</td>
                            <td class="p-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ $item->nomor_bukti ?? '-' }}</td>
                            <td>{{ $item->uraian }}</td>
                            <td class="p-center">{{ $item->kategori ?? '-' }}</td>
                            <td class="p-center">{{ $item->jumlah }}</td>
                            <td class="p-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="p-right">{{ number_format($item->total, 0, ',', '.') }}</td>
                            <td class="p-center">{{ $item->notaPesanan ? 'Ada' : '-' }}</td>
                            <td class="p-center">{{ $item->kwitansi ? 'Ada' : '-' }}</td>
                            <td class="p-center">{{ $item->bapb ? 'Ada' : '-' }}</td>
                            <td class="p-center p-status">{{ $statusPrint }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="p-center">Tidak ada data transaksi.</td></tr>
                    @endforelse

                    @if($belanjas->isNotEmpty())
                        <tr class="print-total">
                            <td colspan="7" class="p-right">TOTAL KESELURUHAN</td>
                            <td class="p-right">{{ number_format($stats['totalBelanja'], 0, ',', '.') }}</td>
                            <td colspan="4"></td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <table class="print-signatures">
                <tr>
                    <td>
                        Mengetahui,<br>Kepala Sekolah
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $school?->kepala_sekolah ?? '........................' }}</div>
                        @if($school?->nip_kepala)<div>NIP. {{ $school->nip_kepala }}</div>@endif
                    </td>
                    <td>
                        Bendahara
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $school?->bendahara ?? '........................' }}</div>
                        @if($school?->nip_bendahara)<div>NIP. {{ $school->nip_bendahara }}</div>@endif
                    </td>
                </tr>
            </table>

            <div class="print-footer">Dicetak dari SILPJ pada {{ now()->translatedFormat('d F Y H:i') }}</div>
        </section>
    </main>
</body>
</html>
