<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SILPJ - Sistem Informasi Laporan Pertanggungjawaban Sekolah</title>
    <style>
        :root{--primary:#1d4ed8;--primary-dark:#1e40af;--text:#172033;--muted:#667085;--border:#e4e7ec;--surface:#fff;--background:#f7f9fc}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--background);color:var(--text)}a,button{font:inherit}.landing-nav{background:rgba(255,255,255,.96);border-bottom:1px solid #e4e7ec;position:sticky;top:0;z-index:20;backdrop-filter:blur(10px)}.landing-nav-inner{width:min(1180px,100%);height:68px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between;gap:20px}.landing-brand{display:flex;align-items:center;gap:11px;color:#172033;text-decoration:none}.landing-logo{width:38px;height:38px;color:#1d4ed8}.landing-brand strong{display:block;font-size:16px}.landing-brand span{display:block;margin-top:2px;color:#667085;font-size:10px}.landing-actions{display:flex;align-items:center;gap:8px}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:9px 15px;border:1px solid transparent;border-radius:9px;text-decoration:none;font-size:12px;font-weight:900;transition:.16s ease}.btn:hover{transform:translateY(-1px)}.btn-primary{background:#1d4ed8;color:#fff}.btn-primary:hover{background:#1e40af}.btn-secondary{background:#fff;color:#344054;border-color:#d0d5dd}.btn-secondary:hover{background:#f9fafb}.hero{position:relative;overflow:hidden;background:linear-gradient(150deg,#f8fbff,#eef5ff 58%,#f7f9fc);border-bottom:1px solid #e4e7ec}.hero:before{content:"";position:absolute;width:520px;height:520px;border-radius:999px;background:rgba(37,99,235,.08);right:-230px;top:-250px}.hero-inner{position:relative;width:min(1180px,100%);margin:0 auto;padding:86px 24px 78px;display:grid;grid-template-columns:minmax(0,1.12fr) minmax(360px,.88fr);gap:64px;align-items:center}.eyebrow{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;border:1px solid #bfdbfe;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:900}.hero h1{margin:18px 0 16px;max-width:720px;font-size:48px;line-height:1.08;letter-spacing:-1.2px}.hero-copy{margin:0;max-width:690px;color:#667085;font-size:16px;line-height:1.75}.hero-buttons{display:flex;gap:10px;flex-wrap:wrap;margin-top:28px}.hero-buttons .btn{min-height:46px;padding:11px 18px;font-size:13px}.hero-note{margin-top:14px;color:#98a2b3;font-size:11px}.preview-card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;box-shadow:0 28px 60px rgba(30,64,175,.14);overflow:hidden}.preview-top{display:flex;align-items:center;gap:6px;padding:12px 14px;border-bottom:1px solid #eef0f3;background:#fbfcfe}.dot{width:8px;height:8px;border-radius:999px;background:#d0d5dd}.preview-body{padding:21px}.preview-title{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}.preview-title strong{font-size:14px}.preview-pill{padding:5px 8px;border-radius:999px;background:#f0fdf4;color:#15803d;font-size:10px;font-weight:900}.preview-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px}.preview-stat{padding:13px;border:1px solid #eef0f3;border-radius:10px;background:#fff}.preview-stat span{display:block;color:#98a2b3;font-size:9px;text-transform:uppercase;letter-spacing:.4px;font-weight:800}.preview-stat strong{display:block;margin-top:7px;font-size:17px}.preview-progress{margin-top:14px;padding:14px;border-radius:11px;background:#f8fafc}.preview-progress-row{display:flex;justify-content:space-between;align-items:center;font-size:10px;color:#667085}.bar{height:7px;margin-top:9px;border-radius:999px;background:#e4e7ec;overflow:hidden}.bar span{display:block;width:78%;height:100%;border-radius:999px;background:#2563eb}.preview-docs{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px}.preview-doc{padding:9px 7px;border:1px solid #dbeafe;border-radius:8px;background:#eff6ff;color:#1e40af;text-align:center;font-size:9px;font-weight:900}.section{width:min(1180px,100%);margin:0 auto;padding:72px 24px}.section-head{max-width:720px;margin-bottom:28px}.section-head span{color:#1d4ed8;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.65px}.section-head h2{margin:8px 0 10px;font-size:31px;letter-spacing:-.6px}.section-head p{margin:0;color:#667085;font-size:14px;line-height:1.7}.feature-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}.feature-card{padding:21px;background:#fff;border:1px solid #e4e7ec;border-radius:13px;box-shadow:0 3px 12px rgba(16,24,40,.035)}.feature-icon{width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:#eff6ff;color:#1d4ed8;font-weight:900}.feature-card h3{margin:14px 0 7px;font-size:16px}.feature-card p{margin:0;color:#667085;font-size:12px;line-height:1.65}.workflow{background:#fff;border-top:1px solid #e4e7ec;border-bottom:1px solid #e4e7ec}.workflow-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.workflow-card{position:relative;padding:19px;border:1px solid #e4e7ec;border-radius:12px}.workflow-number{width:29px;height:29px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:#1d4ed8;color:#fff;font-size:11px;font-weight:900}.workflow-card h3{margin:13px 0 6px;font-size:14px}.workflow-card p{margin:0;color:#667085;font-size:11px;line-height:1.6}.cta{width:min(1180px,calc(100% - 48px));margin:70px auto;padding:32px 36px;display:flex;justify-content:space-between;align-items:center;gap:24px;background:linear-gradient(135deg,#1e40af,#2563eb);border-radius:18px;color:#fff;box-shadow:0 20px 45px rgba(37,99,235,.18)}.cta h2{margin:0 0 7px;font-size:24px}.cta p{margin:0;opacity:.85;font-size:13px;line-height:1.6}.cta .btn-secondary{border-color:rgba(255,255,255,.35);background:#fff;color:#1e40af}.footer{padding:26px 24px;border-top:1px solid #e4e7ec;background:#fff;color:#98a2b3;text-align:center;font-size:11px}
        @media(max-width:900px){.hero-inner{grid-template-columns:1fr;gap:38px;padding-top:60px}.hero h1{font-size:39px}.preview-card{max-width:620px}.feature-grid{grid-template-columns:1fr 1fr}.workflow-grid{grid-template-columns:1fr 1fr}}
        @media(max-width:600px){.landing-nav-inner{height:62px;padding:0 15px}.landing-brand span{display:none}.landing-actions .btn-secondary{display:none}.hero-inner{padding:46px 16px 52px}.hero h1{font-size:32px}.hero-copy{font-size:14px}.section{padding:52px 16px}.section-head h2{font-size:26px}.feature-grid,.workflow-grid{grid-template-columns:1fr}.cta{width:calc(100% - 32px);margin:52px auto;padding:25px 21px;align-items:flex-start;flex-direction:column}.preview-body{padding:16px}}
    </style>
</head>
<body>
<nav class="landing-nav">
    <div class="landing-nav-inner">
        <a href="{{ url('/') }}" class="landing-brand">
            <x-application-logo class="landing-logo" />
            <span><strong>SILPJ</strong><span>Sistem Informasi LPJ Sekolah</span></span>
        </a>
        <div class="landing-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Buka Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-secondary">Masuk</a>
                @if(Route::has('register'))<a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>@endif
            @endauth
        </div>
    </div>
</nav>

<header class="hero">
    <div class="hero-inner">
        <div>
            <div class="eyebrow">Sistem administrasi LPJ sekolah</div>
            <h1>Kelola transaksi dan dokumen LPJ dalam satu alur yang jelas.</h1>
            <p class="hero-copy">SILPJ membantu sekolah mencatat belanja, menyiapkan Nota Pesanan, Kwitansi, BAPB, memantau kelengkapan dokumen, dan menyusun laporan pertanggungjawaban secara terstruktur.</p>
            <div class="hero-buttons">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Masuk ke Dashboard</a>
                    <a href="{{ route('laporan-lpj.index') }}" class="btn btn-secondary">Lihat Laporan LPJ</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Masuk ke SILPJ</a>
                    @if(Route::has('register'))<a href="{{ route('register') }}" class="btn btn-secondary">Buat Akun</a>@endif
                @endauth
            </div>
            <div class="hero-note">Dirancang untuk membantu administrasi dokumen pertanggungjawaban sekolah lebih konsisten.</div>
        </div>

        <div class="preview-card" aria-label="Pratinjau Dashboard SILPJ">
            <div class="preview-top"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
            <div class="preview-body">
                <div class="preview-title"><strong>Ringkasan LPJ</strong><span class="preview-pill">78% lengkap</span></div>
                <div class="preview-stats">
                    <div class="preview-stat"><span>Total Belanja</span><strong>Rp 18,7 jt</strong></div>
                    <div class="preview-stat"><span>Transaksi</span><strong>12</strong></div>
                    <div class="preview-stat"><span>LPJ Lengkap</span><strong>8</strong></div>
                    <div class="preview-stat"><span>Perlu Dilengkapi</span><strong>4</strong></div>
                </div>
                <div class="preview-progress">
                    <div class="preview-progress-row"><span>Kelengkapan dokumen</span><strong>28 / 36</strong></div>
                    <div class="bar"><span></span></div>
                    <div class="preview-docs"><div class="preview-doc">Nota ✓</div><div class="preview-doc">Kwitansi ✓</div><div class="preview-doc">BAPB</div></div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="section">
    <div class="section-head"><span>Fitur utama</span><h2>Administrasi LPJ yang terhubung dari awal sampai laporan.</h2><p>Setiap modul menggunakan transaksi yang sama sehingga pengguna tidak perlu mengulang data dasar pada setiap dokumen.</p></div>
    <div class="feature-grid">
        <article class="feature-card"><div class="feature-icon">01</div><h3>Data Belanja</h3><p>Catat transaksi Barang dan Jasa, cari data, gunakan filter, dan pantau status dokumen dari satu tabel.</p></article>
        <article class="feature-card"><div class="feature-icon">02</div><h3>Dokumen LPJ</h3><p>Buat Nota Pesanan, Kwitansi, dan BAPB langsung dari transaksi yang sudah tercatat.</p></article>
        <article class="feature-card"><div class="feature-icon">03</div><h3>Laporan & Rekap</h3><p>Periksa kelengkapan, filter periode, cetak laporan, dan ekspor data yang diperlukan untuk rekap.</p></article>
    </div>
</section>

<section class="workflow">
    <div class="section">
        <div class="section-head"><span>Alur kerja</span><h2>Empat langkah utama.</h2><p>Alurnya dibuat sederhana agar pengguna memahami apa yang harus dikerjakan berikutnya.</p></div>
        <div class="workflow-grid">
            <article class="workflow-card"><div class="workflow-number">1</div><h3>Lengkapi Profil Sekolah</h3><p>Isi identitas sekolah, kepala sekolah, dan bendahara untuk kebutuhan dokumen.</p></article>
            <article class="workflow-card"><div class="workflow-number">2</div><h3>Catat Belanja</h3><p>Masukkan transaksi lengkap dengan kategori, jumlah, harga, tanggal, dan nomor bukti.</p></article>
            <article class="workflow-card"><div class="workflow-number">3</div><h3>Lengkapi Dokumen</h3><p>Buat Nota Pesanan, Kwitansi, dan BAPB sesuai transaksi yang membutuhkan dokumen.</p></article>
            <article class="workflow-card"><div class="workflow-number">4</div><h3>Periksa Laporan</h3><p>Gunakan Laporan LPJ untuk memastikan seluruh dokumen lengkap sebelum dicetak atau diekspor.</p></article>
        </div>
    </div>
</section>

<section class="cta">
    <div><h2>Siap melanjutkan administrasi LPJ?</h2><p>Masuk ke akun SILPJ dan lanjutkan dari Dashboard.</p></div>
    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Buka Dashboard</a>
    @else
        <a href="{{ route('login') }}" class="btn btn-secondary">Masuk Sekarang</a>
    @endauth
</section>

<footer class="footer">SILPJ · Sistem Informasi Laporan Pertanggungjawaban Sekolah</footer>
</body>
</html>
