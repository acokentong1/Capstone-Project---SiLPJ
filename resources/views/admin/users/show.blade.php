<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - SILPJ</title>
    <style>
        :root{--bg:#f6f8fb;--panel:#fff;--text:#172033;--muted:#667085;--line:#e4e7ec;--primary:#2563eb;--primary-soft:#eff6ff;--success:#067647;--success-soft:#ecfdf3;--danger:#b42318;--danger-soft:#fef3f2;--purple:#6941c6;--purple-soft:#f4f3ff}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.page{width:min(1200px,100%);margin:0 auto;padding:30px 28px 48px}.head{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;margin-bottom:18px}.head h1{margin:0;font-size:25px}.head p{margin:6px 0 0;color:var(--muted);font-size:12px}.btn{min-height:38px;padding:0 13px;border-radius:8px;border:1px solid #d0d5dd;background:#fff;color:#344054;text-decoration:none;display:inline-flex;align-items:center;font-size:11px;font-weight:800}.grid{display:grid;grid-template-columns:1.15fr .85fr;gap:14px}.panel{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden}.panel-head{padding:14px 17px;border-bottom:1px solid #eef0f3;font-size:13px;font-weight:900}.panel-body{padding:17px}.identity{display:flex;gap:13px;align-items:center}.avatar{width:50px;height:50px;border-radius:14px;background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:900}.identity strong{display:block;font-size:16px}.identity span{display:block;margin-top:4px;color:var(--muted);font-size:11px}.badges{display:flex;gap:6px;margin-top:9px}.badge{padding:5px 8px;border-radius:999px;font-size:9px;font-weight:900}.admin{background:var(--purple-soft);color:var(--purple)}.user{background:#f2f4f7;color:#475467}.active{background:var(--success-soft);color:var(--success)}.inactive{background:var(--danger-soft);color:var(--danger)}.details{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:17px}.item{padding:11px;border:1px solid #eef0f3;border-radius:9px;background:#fcfcfd}.item small{display:block;color:var(--muted);font-size:9px;text-transform:uppercase;font-weight:800}.item strong{display:block;margin-top:5px;font-size:12px;word-break:break-word}.stats{display:grid;grid-template-columns:1fr 1fr;gap:9px}.stat{padding:13px;border:1px solid #eef0f3;border-radius:10px}.stat small{color:var(--muted);font-size:9px;font-weight:800;text-transform:uppercase}.stat strong{display:block;margin-top:6px;font-size:20px}.activity{display:grid;gap:8px}.log{padding:10px 11px;border:1px solid #eef0f3;border-radius:9px}.log strong{font-size:11px}.log span{display:block;margin-top:4px;color:var(--muted);font-size:9px}.empty{color:var(--muted);font-size:11px}.side{display:grid;gap:14px;align-content:start}@media(max-width:850px){.grid{grid-template-columns:1fr}}@media(max-width:600px){.page{padding:22px 14px 36px}.head{display:block}.head .btn{margin-top:12px}.details,.stats{grid-template-columns:1fr}}
    </style>
</head>
<body>
@include('partials.app-navigation')
<main class="page">
    <div class="head"><div><h1>Detail Akun</h1><p>Ringkasan akun, sekolah, transaksi, dan aktivitas terakhir.</p></div><a class="btn" href="{{ route('admin.users.index') }}">← Kembali ke Manajemen User</a></div>
    <div class="grid">
        <section class="panel"><div class="panel-head">Identitas akun</div><div class="panel-body">
            <div class="identity"><div class="avatar">{{ strtoupper(substr($user->name,0,1)) }}</div><div><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span><div class="badges"><span class="badge {{ $user->isAdmin()?'admin':'user' }}">{{ $user->isAdmin()?'ADMIN':'USER' }}</span><span class="badge {{ $user->isActive()?'active':'inactive' }}">{{ $user->isActive()?'AKTIF':'NONAKTIF' }}</span></div></div></div>
            <div class="details">
                <div class="item"><small>Sekolah</small><strong>{{ $user->schoolProfile?->nama_sekolah ?? 'Belum diisi' }}</strong></div>
                <div class="item"><small>NPSN</small><strong>{{ $user->schoolProfile?->npsn ?? '-' }}</strong></div>
                <div class="item"><small>Terdaftar</small><strong>{{ $user->created_at?->translatedFormat('d F Y, H:i') }}</strong></div>
                <div class="item"><small>Login terakhir</small><strong>{{ $lastLogin?->created_at?->translatedFormat('d F Y, H:i') ?? 'Belum tercatat' }}</strong></div>
                <div class="item" style="grid-column:1/-1"><small>Alamat sekolah</small><strong>{{ $user->schoolProfile?->alamat ?? 'Belum diisi' }}</strong></div>
            </div>
        </div></section>
        <div class="side">
            <section class="panel"><div class="panel-head">Ringkasan data</div><div class="panel-body"><div class="stats"><div class="stat"><small>Transaksi</small><strong>{{ number_format($jumlahBelanja) }}</strong></div><div class="stat"><small>LPJ Lengkap</small><strong>{{ number_format($lpjLengkap) }}</strong></div><div class="stat"><small>Total Belanja</small><strong style="font-size:15px">Rp {{ number_format($totalBelanja,0,',','.') }}</strong></div><div class="stat"><small>Aktivitas</small><strong>{{ number_format($user->activityLogs()->count()) }}</strong></div></div></div></section>
            <section class="panel"><div class="panel-head">10 aktivitas terakhir</div><div class="panel-body"><div class="activity">@forelse($recentActivities as $log)<div class="log"><strong>{{ $log->description }}</strong><span>{{ $log->created_at->translatedFormat('d M Y, H:i') }} · {{ strtoupper($log->action) }}</span></div>@empty<div class="empty">Belum ada aktivitas.</div>@endforelse</div></div></section>
        </div>
    </div>
</main>
</body>
</html>
