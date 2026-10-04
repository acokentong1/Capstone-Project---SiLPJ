<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring LPJ - SILPJ</title>
    <style>
        :root{--bg:#f5f7fb;--panel:#fff;--text:#172033;--muted:#667085;--line:#e4e7ec;--primary:#2563eb;--soft:#eff6ff;--ok:#067647;--oksoft:#ecfdf3;--warn:#b54708;--warnsoft:#fffaeb}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.page{width:min(1450px,100%);margin:0 auto;padding:28px}.head{display:flex;justify-content:space-between;gap:18px;align-items:end}.head h1{margin:0;font-size:25px}.head p{margin:6px 0 0;color:var(--muted);font-size:11px}.back{font-size:11px;font-weight:800;color:#1d4ed8;text-decoration:none}.panel{margin-top:16px;background:#fff;border:1px solid var(--line);border-radius:13px;overflow:hidden}.filters{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:9px;padding:14px}.field label{display:block;margin-bottom:5px;color:#475467;font-size:9px;font-weight:900;text-transform:uppercase}.field input,.field select{width:100%;height:39px;border:1px solid #d0d5dd;border-radius:8px;padding:0 10px;background:#fff;color:#344054;font-size:11px}.btn{height:39px;padding:0 13px;border:0;border-radius:8px;background:var(--primary);color:#fff;font-size:11px;font-weight:900;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}.btn-light{background:#fff;color:#344054;border:1px solid #d0d5dd}.filter-actions{display:flex;align-items:end;gap:7px}.panel-head{padding:13px 15px;border-top:1px solid #eef0f3;border-bottom:1px solid #eef0f3;display:flex;justify-content:space-between;font-size:11px}.panel-head span{color:var(--muted)}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:1080px}th,td{padding:11px 12px;border-bottom:1px solid #eef0f3;text-align:left;vertical-align:middle}th{background:#fafbfc;color:#667085;font-size:9px;text-transform:uppercase}td{font-size:10px}.school strong{display:block;font-size:11px}.school span{display:block;margin-top:3px;color:var(--muted)}.badge{display:inline-flex;align-items:center;min-height:22px;padding:0 7px;border-radius:999px;font-size:9px;font-weight:900}.ok{background:var(--oksoft);color:var(--ok)}.warn{background:var(--warnsoft);color:var(--warn)}.progress{width:110px;height:6px;background:#edf2f7;border-radius:999px;overflow:hidden}.progress i{display:block;height:100%;background:#2563eb;border-radius:999px}.doc{display:flex;gap:4px;flex-wrap:wrap}.dot{display:inline-flex;min-width:28px;height:22px;align-items:center;justify-content:center;border-radius:6px;font-size:8px;font-weight:900}.dot.yes{background:#ecfdf3;color:#067647}.dot.no{background:#fff4ed;color:#b54708}.pagination{padding:13px 15px}.empty{padding:34px;text-align:center;color:var(--muted);font-size:11px}@media(max-width:800px){.page{padding:18px 12px}.head{align-items:flex-start;flex-direction:column}.filters{grid-template-columns:1fr}.filter-actions{align-items:center}}
    </style>
</head>
<body>
@include('partials.app-navigation')
<main class="page">
    <div class="head"><div><h1>Monitoring Kelengkapan LPJ</h1><p>Pantau progres dokumen setiap sekolah dan temukan transaksi yang masih perlu dilengkapi.</p></div><a class="back" href="{{ route('admin.dashboard') }}">← Dashboard Admin</a></div>

    <section class="panel">
        <form method="GET" class="filters">
            <div class="field"><label>Pencarian</label><input name="q" value="{{ $filters['q'] }}" placeholder="Nama sekolah, user, email, atau NPSN"></div>
            <div class="field"><label>Status akun</label><select name="status"><option value="">Semua status</option><option value="active" @selected($filters['status']==='active')>Aktif</option><option value="inactive" @selected($filters['status']==='inactive')>Nonaktif</option></select></div>
            <div class="field"><label>Progress LPJ</label><select name="progress"><option value="">Semua progress</option><option value="complete" @selected($filters['progress']==='complete')>Semua LPJ lengkap</option><option value="incomplete" @selected($filters['progress']==='incomplete')>Masih belum lengkap</option><option value="empty" @selected($filters['progress']==='empty')>Belum ada transaksi</option></select></div>
            <div class="filter-actions"><button class="btn" type="submit">Terapkan</button><a class="btn btn-light" href="{{ route('admin.monitoring.index') }}">Reset</a></div>
        </form>
        <div class="panel-head"><strong>Daftar sekolah / akun</strong><span>{{ number_format($users->total()) }} akun ditemukan</span></div>
        @if($users->count())
            <div class="table-wrap"><table>
                <thead><tr><th>Sekolah / Akun</th><th>Status</th><th>Transaksi</th><th>Total Belanja</th><th>Dokumen</th><th>LPJ Lengkap</th><th>Progress</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="school"><strong>{{ $user->schoolProfile?->nama_sekolah ?? 'Profil sekolah belum lengkap' }}</strong><span>{{ $user->name }} · {{ $user->email }}@if($user->schoolProfile?->npsn) · NPSN {{ $user->schoolProfile->npsn }}@endif</span></td>
                        <td><span class="badge {{ $user->isActive() ? 'ok' : 'warn' }}">{{ $user->isActive() ? 'AKTIF' : 'NONAKTIF' }}</span></td>
                        <td><strong>{{ number_format($user->belanjas_count) }}</strong></td>
                        <td>Rp {{ number_format((int)($user->belanjas_sum_total ?? 0),0,',','.') }}</td>
                        <td><div class="doc"><span class="dot {{ $user->nota_count === $user->belanjas_count && $user->belanjas_count>0 ? 'yes':'no' }}">N {{ $user->nota_count }}/{{ $user->belanjas_count }}</span><span class="dot {{ $user->kwitansi_count === $user->belanjas_count && $user->belanjas_count>0 ? 'yes':'no' }}">K {{ $user->kwitansi_count }}/{{ $user->belanjas_count }}</span><span class="dot {{ $user->bapb_count === $user->belanjas_count && $user->belanjas_count>0 ? 'yes':'no' }}">B {{ $user->bapb_count }}/{{ $user->belanjas_count }}</span></div></td>
                        <td><span class="badge {{ $user->lpj_belum_lengkap_count===0 && $user->belanjas_count>0 ? 'ok':'warn' }}">{{ $user->lpj_lengkap_count }}/{{ $user->belanjas_count }}</span></td>
                        <td><div style="display:flex;align-items:center;gap:7px"><div class="progress"><i style="width:{{ $user->lpj_progress }}%"></i></div><strong>{{ $user->lpj_progress }}%</strong></div></td>
                        <td><a class="btn btn-light" style="height:30px" href="{{ route('admin.users.show',$user) }}">Detail</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            @if($users->hasPages())<div class="pagination">{{ $users->links() }}</div>@endif
        @else
            <div class="empty">Tidak ada akun yang sesuai dengan filter.</div>
        @endif
    </section>
</main>
</body>
</html>
