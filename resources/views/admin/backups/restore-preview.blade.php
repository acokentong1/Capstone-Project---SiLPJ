<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Restore - SILPJ</title>
    <style>
        :root{--bg:#f6f8fb;--text:#172033;--muted:#667085;--line:#e4e7ec;--primary:#2563eb;--danger:#b42318;--danger-soft:#fef3f2;--warning:#b54708;--warning-soft:#fffaeb;--ok:#067647;--oksoft:#ecfdf3}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.page{width:min(980px,100%);margin:0 auto;padding:28px}.head h1{margin:0;font-size:25px}.head p{margin:7px 0 0;color:var(--muted);font-size:11px}.alert{margin-top:14px;padding:12px 14px;border-radius:10px;font-size:10px;line-height:1.55}.error{background:var(--danger-soft);border:1px solid #fecdca;color:#912018}.warn{background:var(--warning-soft);border:1px solid #fedf89;color:#7a2e0e}.ok{background:var(--oksoft);border:1px solid #abefc6;color:#05603a}.panel{margin-top:14px;background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden}.panel-head{padding:14px 16px;border-bottom:1px solid #eef0f3;font-size:12px;font-weight:900}.body{padding:16px}.meta{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}.meta div{padding:11px;border:1px solid #eef0f3;border-radius:9px}.meta span{display:block;color:var(--muted);font-size:8px;text-transform:uppercase;font-weight:900}.meta strong{display:block;margin-top:5px;font-size:11px}.rows{display:grid;grid-template-columns:repeat(2,1fr);gap:7px;margin-top:12px}.row{display:flex;justify-content:space-between;padding:9px 10px;border:1px solid #eef0f3;border-radius:8px}.row span{color:var(--muted);font-size:9px}.row strong{font-size:10px}.confirm{margin-top:12px;padding:14px;border:1px solid #fecdca;background:#fffafa;border-radius:10px}.confirm label{display:block;margin-top:10px;font-size:10px;font-weight:800}.confirm input[type=text]{width:100%;height:39px;margin-top:5px;border:1px solid #d0d5dd;border-radius:8px;padding:0 10px;font-size:11px}.check{display:flex!important;gap:8px;align-items:flex-start;font-weight:600!important;line-height:1.5}.actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:13px}.btn{min-height:39px;padding:0 14px;border:1px solid transparent;border-radius:8px;font-size:10px;font-weight:900;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}.btn-danger{background:#b42318;color:#fff}.btn-light{background:#fff;border-color:#d0d5dd;color:#344054}.error-text{margin-top:4px;color:#b42318;font-size:9px}@media(max-width:700px){.page{padding:18px 12px}.meta,.rows{grid-template-columns:1fr 1fr}}@media(max-width:450px){.meta,.rows{grid-template-columns:1fr}}
    </style>
</head>
<body>
@include('partials.app-navigation')
<main class="page">
    <div class="head"><h1>Preview Restore Database</h1><p>Belum ada perubahan pada database. Periksa informasi berikut sebelum memberi konfirmasi akhir.</p></div>

    @if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif

    @php($preview=$pending['preview'])
    <div class="alert {{ $preview['current_admin_found'] ? 'ok':'error' }}">
        @if($preview['current_admin_found'])
            Akun admin yang sedang digunakan ditemukan di backup. Saat restore, password admin Anda saat ini akan dipertahankan dan role dipastikan tetap Admin aktif.
        @else
            Akun admin yang sedang digunakan tidak ditemukan di backup. Restore diblokir untuk mencegah kehilangan akses administrator.
        @endif
    </div>

    @foreach($preview['warnings'] ?? [] as $warning)<div class="alert warn">{{ $warning }}</div>@endforeach

    <section class="panel">
        <div class="panel-head">Identitas file backup</div>
        <div class="body">
            <div class="meta">
                <div><span>Nama file</span><strong>{{ $pending['original_name'] }}</strong></div>
                <div><span>Format</span><strong>Versi {{ $preview['format_version'] }}</strong></div>
                <div><span>Dibuat</span><strong>{{ $preview['created_at'] ? \Carbon\Carbon::parse($preview['created_at'])->translatedFormat('d M Y H:i') : 'Tidak diketahui' }}</strong></div>
                <div><span>Total record</span><strong>{{ number_format($preview['total_records']) }}</strong></div>
            </div>
            <div class="rows">
                @php($labels=['users'=>'Akun','school_profiles'=>'Profil Sekolah','belanjas'=>'Transaksi Belanja','nota_pesanans'=>'Nota Pesanan','kwitansis'=>'Kwitansi','bapbs'=>'BAPB','activity_logs'=>'Activity Log'])
                @foreach($labels as $table=>$label)<div class="row"><span>{{ $label }}</span><strong>{{ number_format($preview['counts'][$table] ?? 0) }}</strong></div>@endforeach
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">Konfirmasi restore</div>
        <div class="body">
            <div class="alert warn" style="margin-top:0"><strong>Dampak:</strong> data inti SILPJ saat ini akan diganti dengan isi backup. Sebelum itu SILPJ akan menyimpan backup pengaman otomatis. Setelah sukses, seluruh pengguna akan logout.</div>
            @if($preview['current_admin_found'])
                <form class="confirm" method="POST" action="{{ route('admin.backups.restore.execute',$token) }}">
                    @csrf
                    <label>Konfirmasi kata sandi admin</label>
                    <input type="password" name="current_password" autocomplete="current-password" placeholder="Kata sandi akun Anda" required>
                    @error('current_password')<div class="error-text">{{ $message }}</div>@enderror
                    <label>Ketik persis <strong>RESTORE SILPJ</strong></label>
                    <input type="text" name="confirmation" autocomplete="off" placeholder="RESTORE SILPJ" required>
                    @error('confirmation')<div class="error-text">{{ $message }}</div>@enderror
                    <label class="check"><input type="checkbox" name="understand_logout" value="1" required><span>Saya memahami bahwa data saat ini akan diganti sesuai backup dan seluruh sesi login akan dikeluarkan setelah restore.</span></label>
                    @error('understand_logout')<div class="error-text">{{ $message }}</div>@enderror
                    <div class="actions"><button class="btn btn-danger" type="submit" onclick="return confirm('Konfirmasi terakhir: jalankan restore database sekarang?')">Jalankan Restore</button></div>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.backups.restore.cancel',$token) }}" style="margin-top:10px">@csrf @method('DELETE')<button class="btn btn-light" type="submit">Batalkan & Hapus Preview</button></form>
        </div>
    </section>
</main>
</body>
</html>
