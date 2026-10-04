<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - SILPJ</title>
    <style>
        :root{--bg:#f6f8fb;--panel:#fff;--text:#172033;--muted:#667085;--line:#e4e7ec;--primary:#2563eb;--primary-soft:#eff6ff;--warning:#b54708;--warning-soft:#fffaeb;--danger:#b42318;--danger-soft:#fef3f2;--success:#067647;--success-soft:#ecfdf3}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.page{width:min(1180px,100%);margin:0 auto;padding:28px 28px 48px}.head{display:flex;justify-content:space-between;gap:18px;align-items:end}.head h1{margin:0;font-size:26px}.head p{margin:7px 0 0;color:var(--muted);font-size:11px;line-height:1.5}.head a{color:#1d4ed8;font-size:11px;font-weight:900;text-decoration:none}.flash{margin-top:14px;padding:12px 14px;border-radius:10px;font-size:11px;line-height:1.55}.flash-success{background:var(--success-soft);border:1px solid #abefc6;color:#05603a}.flash-error{background:var(--danger-soft);border:1px solid #fecdca;color:#912018}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:16px}.panel{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden}.panel-head{padding:14px 17px;border-bottom:1px solid #eef0f3;display:flex;justify-content:space-between;gap:12px;align-items:center}.panel-head strong{font-size:13px}.panel-head span{color:var(--muted);font-size:9px}.panel-body{padding:17px}.hero{padding:18px;border-radius:11px;background:linear-gradient(135deg,#eff6ff,#f8fbff);border:1px solid #bfdbfe}.hero.danger{background:linear-gradient(135deg,#fff7ed,#fff);border-color:#fed7aa}.hero strong{display:block;font-size:16px}.hero p{margin:7px 0 0;color:#475467;font-size:10px;line-height:1.6}.btn{margin-top:13px;min-height:39px;padding:0 14px;border:1px solid transparent;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:900;cursor:pointer}.btn-primary{background:var(--primary);color:#fff}.btn-danger{background:#b42318;color:#fff}.btn-light{background:#fff;color:#344054;border-color:#d0d5dd}.notice{margin-top:12px;padding:11px 12px;border-radius:9px;background:var(--warning-soft);border:1px solid #fedf89;color:#7a2e0e;font-size:10px;line-height:1.6}.safe{margin-top:10px;padding:11px 12px;border-radius:9px;background:var(--success-soft);border:1px solid #abefc6;color:#05603a;font-size:10px;line-height:1.6}.rows{display:grid;gap:7px}.row{display:flex;justify-content:space-between;gap:15px;padding:9px 10px;border:1px solid #eef0f3;border-radius:8px}.row span{color:var(--muted);font-size:9px}.row strong{font-size:11px}.upload{margin-top:13px}.upload input[type=file]{width:100%;padding:10px;border:1px dashed #98a2b3;border-radius:9px;background:#fff;font-size:10px}.error{margin-top:5px;color:#b42318;font-size:9px}.steps{margin:0;padding-left:18px;color:#475467;font-size:10px;line-height:1.8}.safety{margin-top:14px}.safety-item{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #eef0f3}.safety-item:last-child{border-bottom:0}.safety-item strong{display:block;font-size:10px}.safety-item span{display:block;margin-top:3px;color:var(--muted);font-size:9px}.safety-item a{font-size:9px;color:#1d4ed8;font-weight:900;text-decoration:none}.full{grid-column:1/-1}@media(max-width:800px){.grid{grid-template-columns:1fr}.full{grid-column:auto}.head{align-items:flex-start;flex-direction:column}}@media(max-width:600px){.page{padding:20px 12px 36px}}
    </style>
</head>
<body>
@include('partials.app-navigation')
<main class="page">
    <div class="head"><div><h1>Backup & Restore Database</h1><p>Cadangkan data secara terenkripsi dan pulihkan backup melalui validasi berlapis.</p></div><a href="{{ route('admin.dashboard') }}">← Dashboard Admin</a></div>

    @if(session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="flash flash-error">{{ session('error') }}</div>@endif

    <div class="grid">
        <section class="panel">
            <div class="panel-head"><strong>Backup terenkripsi</strong><span>AMAN UNTUK DIUNDUH</span></div>
            <div class="panel-body">
                <div class="hero"><strong>Cadangkan database sekarang</strong><p>Backup membawa data inti SILPJ, tetapi tidak menyertakan session, cache, queue, migration, dan token reset password.</p><a class="btn btn-primary" href="{{ route('admin.backups.download') }}">Unduh Backup .silpjbackup</a></div>
                <div class="notice"><strong>Penting:</strong> file terenkripsi memakai <code>APP_KEY</code>. Backup dari APP_KEY yang berbeda tidak dapat dibuka. Simpan APP_KEY produksi di tempat aman.</div>
                <div class="rows" style="margin-top:13px">
                    @php($labels=['users'=>'Akun','school_profiles'=>'Profil Sekolah','belanjas'=>'Transaksi Belanja','nota_pesanans'=>'Nota Pesanan','kwitansis'=>'Kwitansi','bapbs'=>'BAPB','activity_logs'=>'Activity Log'])
                    @foreach($labels as $table=>$label)<div class="row"><span>{{ $label }}</span><strong>{{ number_format($counts[$table] ?? 0) }}</strong></div>@endforeach
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><strong>Restore dengan preview</strong><span>ADMIN + KONFIRMASI PASSWORD</span></div>
            <div class="panel-body">
                <div class="hero danger"><strong>Pulihkan dari file backup</strong><p>File tidak langsung diterapkan. SILPJ akan mendekripsi, memvalidasi schema, relasi, ID, dan kepemilikan data lalu menampilkan preview terlebih dahulu.</p></div>
                <form class="upload" method="POST" action="{{ route('admin.backups.restore.preview') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="backup_file" accept=".silpjbackup" required>
                    @error('backup_file')<div class="error">{{ $message }}</div>@enderror
                    <button class="btn btn-danger" type="submit">Validasi & Tampilkan Preview</button>
                </form>
                <div class="safe"><strong>Pengaman restore:</strong> backup database saat ini dibuat otomatis sebelum restore. Proses database dijalankan dalam transaction. Jika terjadi error saat penggantian data, perubahan dibatalkan. Password admin yang menjalankan restore juga dipertahankan agar tidak terkunci keluar.</div>
            </div>
        </section>

        <section class="panel full">
            <div class="panel-head"><strong>Urutan keamanan restore</strong><span>TIDAK ADA RESTORE SATU KLIK</span></div>
            <div class="panel-body"><ol class="steps"><li>Admin mengunggah file <code>.silpjbackup</code>.</li><li>SILPJ memastikan file dapat didekripsi dengan APP_KEY aktif.</li><li>Schema, tabel, ID, relasi transaksi, dan pemilik dokumen diperiksa.</li><li>Preview jumlah data dan tanggal backup ditampilkan.</li><li>Restore hanya bisa diteruskan jika akun admin saat ini ada di backup.</li><li>SILPJ membuat backup pengaman otomatis dari database yang sedang aktif.</li><li>Admin mengetik konfirmasi khusus sebelum proses berjalan.</li><li>Semua sesi login dikeluarkan setelah restore dan admin harus login kembali.</li></ol></div>
        </section>

        <section class="panel full">
            <div class="panel-head"><strong>Backup pengaman sebelum restore</strong><span>{{ count($safetyBackups) }} terbaru</span></div>
            <div class="panel-body">
                @forelse($safetyBackups as $backup)
                    <div class="safety-item"><div><strong>{{ $backup['filename'] }}</strong><span>{{ number_format($backup['bytes']/1024,1) }} KB · {{ \Carbon\Carbon::createFromTimestamp($backup['modified_at'])->translatedFormat('d M Y H:i') }}</span></div><a href="{{ route('admin.backups.safety.download',$backup['filename']) }}">Unduh</a></div>
                @empty
                    <div style="color:#667085;font-size:10px">Belum ada backup pengaman. File ini akan dibuat otomatis saat restore pertama dijalankan.</div>
                @endforelse
            </div>
        </section>
    </div>
</main>
</body>
</html>
