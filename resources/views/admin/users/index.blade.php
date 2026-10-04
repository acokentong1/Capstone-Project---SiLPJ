<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - SILPJ</title>
    <style>
        :root{--bg:#f6f8fb;--panel:#fff;--text:#172033;--muted:#667085;--line:#e4e7ec;--primary:#2563eb;--primary-soft:#eff6ff;--success:#067647;--success-soft:#ecfdf3;--warning:#b54708;--warning-soft:#fffaeb;--danger:#b42318;--danger-soft:#fef3f2;--purple:#6941c6;--purple-soft:#f4f3ff}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.page{width:min(1450px,100%);margin:0 auto;padding:30px 28px 48px}.head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:20px}.head h1{margin:0;font-size:26px}.head p{margin:7px 0 0;color:var(--muted);font-size:13px}.admin-badge{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border-radius:10px;background:var(--purple-soft);color:var(--purple);font-size:12px;font-weight:800}.flash{margin-bottom:14px;padding:12px 14px;border-radius:10px;font-size:12px;font-weight:700}.flash.ok{background:var(--success-soft);color:var(--success);border:1px solid #abefc6}.flash.err{background:var(--danger-soft);color:var(--danger);border:1px solid #fecdca}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}.stat,.panel{background:var(--panel);border:1px solid var(--line);border-radius:14px;box-shadow:0 1px 2px rgba(16,24,40,.03)}.stat{padding:16px}.stat-label{color:var(--muted);font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.45px}.stat-value{margin-top:7px;font-size:25px;font-weight:900}.stat-help{margin-top:4px;color:#98a2b3;font-size:10px}.filter{padding:14px;margin-bottom:16px}.filter-grid{display:grid;grid-template-columns:minmax(220px,2fr) 1fr 1fr auto;gap:10px;align-items:end}.field label{display:block;margin-bottom:6px;color:#475467;font-size:11px;font-weight:800}.field input,.field select{width:100%;height:39px;border:1px solid #d0d5dd;border-radius:8px;padding:0 10px;background:#fff;color:#344054;outline:none}.field input:focus,.field select:focus{border-color:#84adff;box-shadow:0 0 0 3px #eff4ff}.actions{display:flex;gap:7px}.btn{height:39px;padding:0 13px;border-radius:8px;border:1px solid transparent;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-size:11px;font-weight:800;cursor:pointer}.btn-primary{background:var(--primary);color:white}.btn-light{background:white;color:#344054;border-color:#d0d5dd}.btn-success{background:var(--success-soft);color:var(--success);border-color:#abefc6}.btn-danger{background:var(--danger-soft);color:var(--danger);border-color:#fecdca}.panel-head{padding:15px 18px;border-bottom:1px solid #eef0f3;display:flex;justify-content:space-between;align-items:center}.panel-head strong{font-size:13px}.panel-head span{font-size:11px;color:var(--muted)}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:1050px}th,td{padding:12px 14px;border-bottom:1px solid #eef0f3;text-align:left;vertical-align:top}th{background:#fcfcfd;color:#667085;font-size:10px;text-transform:uppercase;letter-spacing:.35px}td{font-size:12px}.user-main strong{display:block;font-size:12px}.user-main span{display:block;margin-top:3px;color:var(--muted);font-size:10px}.badge{display:inline-flex;align-items:center;min-height:23px;padding:0 8px;border-radius:999px;font-size:10px;font-weight:900}.badge-admin{background:var(--purple-soft);color:var(--purple)}.badge-user{background:#f2f4f7;color:#475467}.badge-active{background:var(--success-soft);color:var(--success)}.badge-inactive{background:var(--danger-soft);color:var(--danger)}.mini{color:var(--muted);font-size:10px}.inline-form{display:flex;gap:6px;align-items:center;flex-wrap:wrap}.inline-form select{height:33px;border:1px solid #d0d5dd;border-radius:7px;padding:0 7px;font-size:11px}.inline-form .btn{height:33px;padding:0 9px}.row-actions{display:flex;gap:6px;flex-wrap:wrap}.pagination{padding:14px 18px 18px}.pagination nav>div:first-child{display:none}.empty{padding:40px 20px;text-align:center;color:var(--muted)}@media(max-width:980px){.stats{grid-template-columns:repeat(2,1fr)}.filter-grid{grid-template-columns:1fr 1fr}.actions{grid-column:span 2}}@media(max-width:650px){.page{padding:22px 14px 36px}.head{display:block}.admin-badge{margin-top:12px}.stats{grid-template-columns:1fr 1fr}.filter-grid{grid-template-columns:1fr}.actions{grid-column:auto}.actions .btn{flex:1}}@media(max-width:420px){.stats{grid-template-columns:1fr}}
    </style>
</head>
<body>
@include('partials.app-navigation')
<main class="page">
    <div class="head">
        <div>
            <h1>Manajemen User</h1>
            <p>Kelola role dan status akun tanpa menghapus data transaksi sekolah.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap"><a class="btn btn-light" href="{{ route('admin.backups.index') }}">Backup Database</a><div class="admin-badge">Area Administrator</div></div>
    </div>

    @if(session('success'))<div class="flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="flash err">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="flash err">{{ $errors->first() }}</div>@endif

    <section class="stats">
        <div class="stat"><div class="stat-label">Total Akun</div><div class="stat-value">{{ number_format($stats['total']) }}</div><div class="stat-help">Seluruh akun SILPJ</div></div>
        <div class="stat"><div class="stat-label">Akun Aktif</div><div class="stat-value">{{ number_format($stats['active']) }}</div><div class="stat-help">Dapat masuk ke aplikasi</div></div>
        <div class="stat"><div class="stat-label">Dinonaktifkan</div><div class="stat-value">{{ number_format($stats['inactive']) }}</div><div class="stat-help">Akses login diblokir</div></div>
        <div class="stat"><div class="stat-label">Administrator</div><div class="stat-value">{{ number_format($stats['admins']) }}</div><div class="stat-help">Memiliki akses admin</div></div>
    </section>

    <section class="panel filter">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-grid">
            <div class="field"><label for="q">Cari akun</label><input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Nama, email, atau nama sekolah"></div>
            <div class="field"><label for="role">Role</label><select id="role" name="role"><option value="">Semua role</option><option value="admin" @selected($filters['role']==='admin')>Admin</option><option value="user" @selected($filters['role']==='user')>User</option></select></div>
            <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">Semua status</option><option value="active" @selected($filters['status']==='active')>Aktif</option><option value="inactive" @selected($filters['status']==='inactive')>Nonaktif</option></select></div>
            <div class="actions"><button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-light" href="{{ route('admin.users.index') }}">Reset</a></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head"><strong>Daftar akun</strong><span>{{ $users->total() }} akun ditemukan</span></div>
        @if($users->count())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Akun</th><th>Sekolah</th><th>Role</th><th>Status</th><th>Data</th><th>Dibuat</th><th>Pengaturan</th></tr></thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td><div class="user-main"><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span>@if(auth()->id()===$user->id)<span>• akun Anda</span>@endif</div></td>
                            <td><strong>{{ $user->schoolProfile?->nama_sekolah ?? 'Belum diisi' }}</strong><div class="mini">{{ $user->schoolProfile?->npsn ? 'NPSN '.$user->schoolProfile->npsn : 'Profil sekolah belum lengkap' }}</div></td>
                            <td><span class="badge {{ $user->isAdmin() ? 'badge-admin' : 'badge-user' }}">{{ $user->isAdmin() ? 'ADMIN' : 'USER' }}</span></td>
                            <td><span class="badge {{ $user->isActive() ? 'badge-active' : 'badge-inactive' }}">{{ $user->isActive() ? 'AKTIF' : 'NONAKTIF' }}</span></td>
                            <td><strong>{{ number_format($user->belanjas_count) }}</strong> transaksi<div class="mini">{{ number_format($user->activity_logs_count) }} aktivitas</div></td>
                            <td>{{ $user->created_at?->translatedFormat('d M Y') }}<div class="mini">{{ $user->created_at?->diffForHumans() }}</div></td>
                            <td>
                                <div class="row-actions" style="margin-bottom:6px"><a class="btn btn-light" href="{{ route('admin.users.show',$user) }}">Detail</a></div>
                                <form method="POST" action="{{ route('admin.users.role',$user) }}" class="inline-form" style="margin-bottom:6px">@csrf @method('PATCH')
                                    <select name="role"><option value="user" @selected($user->role==='user')>User</option><option value="admin" @selected($user->role==='admin')>Admin</option></select>
                                    <button class="btn btn-light" type="submit">Simpan role</button>
                                </form>
                                @if(auth()->id() !== $user->id)
                                    <form method="POST" action="{{ route('admin.users.status',$user) }}" class="inline-form">@csrf @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $user->isActive() ? 0 : 1 }}">
                                        <button class="btn {{ $user->isActive() ? 'btn-danger' : 'btn-success' }}" type="submit" onclick="return confirm('{{ $user->isActive() ? 'Nonaktifkan akun ini? Sesi aktifnya akan diputus.' : 'Aktifkan akun ini?' }}')">{{ $user->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                @else
                                    <span class="mini">Status akun sendiri tidak dapat dinonaktifkan.</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())<div class="pagination">{{ $users->links() }}</div>@endif
        @else
            <div class="empty">Tidak ada akun yang sesuai dengan filter.</div>
        @endif
    </section>
</main>
</body>
</html>
