<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SILPJ</title>
    <style>
        :root{--bg:#f5f7fb;--panel:#fff;--text:#172033;--muted:#667085;--line:#e4e7ec;--primary:#2563eb;--primary-soft:#eff6ff;--success:#067647;--success-soft:#ecfdf3;--warning:#b54708;--warning-soft:#fffaeb;--danger:#b42318;--danger-soft:#fef3f2}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.page{width:min(1420px,100%);margin:0 auto;padding:28px 28px 48px}.hero{display:flex;justify-content:space-between;gap:20px;align-items:center;padding:22px 24px;border-radius:16px;background:linear-gradient(135deg,#172554,#1d4ed8);color:#fff;box-shadow:0 12px 28px rgba(29,78,216,.18)}.hero h1{margin:0;font-size:26px}.hero p{margin:7px 0 0;font-size:12px;line-height:1.6;opacity:.86}.actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}.btn{min-height:39px;padding:0 13px;border:1px solid transparent;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;cursor:pointer}.btn-white{background:#fff;color:#1d4ed8}.btn-ghost{border-color:rgba(255,255,255,.35);color:#fff;background:rgba(255,255,255,.08)}.stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-top:16px}.stat{background:#fff;border:1px solid var(--line);border-radius:13px;padding:16px;box-shadow:0 2px 8px rgba(16,24,40,.03)}.stat span{display:block;color:var(--muted);font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.stat strong{display:block;margin-top:7px;font-size:22px}.stat small{display:block;margin-top:5px;color:var(--muted);font-size:10px;line-height:1.4}.grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(310px,.75fr);gap:14px;margin-top:16px}.panel{background:#fff;border:1px solid var(--line);border-radius:13px;overflow:hidden}.panel-head{padding:14px 16px;border-bottom:1px solid #eef0f3;display:flex;justify-content:space-between;gap:12px;align-items:center}.panel-head strong{font-size:13px}.panel-head span,.panel-head a{color:var(--muted);font-size:10px}.panel-head a{color:#1d4ed8;font-weight:800;text-decoration:none}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:760px}th,td{padding:11px 13px;border-bottom:1px solid #eef0f3;text-align:left;vertical-align:middle}th{background:#fafbfc;color:#667085;font-size:9px;text-transform:uppercase;letter-spacing:.04em}td{font-size:11px}.name strong{display:block;font-size:11px}.name span{display:block;margin-top:3px;color:var(--muted);font-size:10px}.progress{height:6px;border-radius:999px;background:#eef2f6;overflow:hidden;min-width:100px}.progress>i{display:block;height:100%;background:#2563eb;border-radius:999px}.badge{display:inline-flex;align-items:center;min-height:22px;padding:0 7px;border-radius:999px;font-size:9px;font-weight:900}.badge-ok{background:var(--success-soft);color:var(--success)}.badge-warn{background:var(--warning-soft);color:var(--warning)}.side-stack{display:grid;gap:14px;align-content:start}.list{padding:5px 14px 10px}.item{padding:10px 2px;border-bottom:1px solid #eef0f3}.item:last-child{border-bottom:0}.item strong{display:block;font-size:11px}.item span{display:block;margin-top:4px;color:var(--muted);font-size:10px;line-height:1.45}.doc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:14px}.doc{padding:12px;border:1px solid #eef0f3;border-radius:10px;text-align:center}.doc strong{display:block;font-size:17px}.doc span{display:block;margin-top:3px;color:var(--muted);font-size:9px}.doc em{display:block;margin-top:6px;color:#b54708;font-style:normal;font-size:9px;font-weight:800}@media(max-width:1100px){.stats{grid-template-columns:repeat(3,1fr)}.grid{grid-template-columns:1fr}}@media(max-width:680px){.page{padding:18px 12px 34px}.hero{align-items:flex-start;flex-direction:column}.actions{justify-content:flex-start}.stats{grid-template-columns:1fr 1fr}.stat:last-child{grid-column:1/-1}}
    </style>
</head>
<body>
@include('partials.app-navigation')
<main class="page">
    <section class="hero">
        <div><h1>Dashboard Administrator</h1><p>Pantau akun, sekolah, transaksi, dan kelengkapan LPJ seluruh pengguna SILPJ dari satu halaman.</p></div>
        <div class="actions">
            <a class="btn btn-white" href="{{ route('admin.monitoring.index') }}">Monitoring LPJ</a>
            <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Kelola User</a>
            <a class="btn btn-ghost" href="{{ route('admin.backups.index') }}">Backup & Restore</a>
        </div>
    </section>

    <section class="stats">
        <div class="stat"><span>Total akun</span><strong>{{ number_format($totalUsers) }}</strong><small>{{ number_format($activeUsers) }} akun aktif</small></div>
        <div class="stat"><span>Profil sekolah</span><strong>{{ number_format($totalSchools) }}</strong><small>{{ max(0,$totalUsers-$totalSchools) }} akun belum melengkapi profil</small></div>
        <div class="stat"><span>Transaksi</span><strong>{{ number_format($totalTransactions) }}</strong><small>Total seluruh sekolah</small></div>
        <div class="stat"><span>Nilai belanja</span><strong style="font-size:18px">Rp {{ number_format($totalValue,0,',','.') }}</strong><small>Akumulasi seluruh transaksi</small></div>
        <div class="stat"><span>LPJ lengkap</span><strong>{{ $completionRate }}%</strong><small>{{ number_format($completeLpj) }} dari {{ number_format($totalTransactions) }} transaksi</small></div>
    </section>

    <section class="grid">
        <div class="panel">
            <div class="panel-head"><strong>Sekolah yang perlu perhatian</strong><a href="{{ route('admin.monitoring.index') }}">Lihat semua monitoring →</a></div>
            <div class="table-wrap"><table>
                <thead><tr><th>Sekolah / Akun</th><th>Transaksi</th><th>Nilai</th><th>LPJ Lengkap</th><th>Progress</th><th>Belum Lengkap</th></tr></thead>
                <tbody>
                @forelse($schoolMonitoring as $user)
                    <tr>
                        <td class="name"><strong>{{ $user->schoolProfile?->nama_sekolah ?? $user->name }}</strong><span>{{ $user->schoolProfile?->nama_sekolah ? $user->name.' · '.$user->email : $user->email.' · profil sekolah belum lengkap' }}</span></td>
                        <td>{{ number_format($user->belanjas_count) }}</td>
                        <td>Rp {{ number_format((int)($user->belanjas_sum_total ?? 0),0,',','.') }}</td>
                        <td>{{ number_format($user->lpj_lengkap_count) }}</td>
                        <td><div style="display:flex;align-items:center;gap:8px"><div class="progress"><i style="width:{{ $user->lpj_progress }}%"></i></div><strong>{{ $user->lpj_progress }}%</strong></div></td>
                        <td><span class="badge {{ $user->lpj_belum_lengkap_count > 0 ? 'badge-warn' : 'badge-ok' }}">{{ $user->lpj_belum_lengkap_count > 0 ? $user->lpj_belum_lengkap_count.' transaksi' : 'Selesai' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:#667085;padding:24px">Belum ada data pengguna.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>

        <div class="side-stack">
            <section class="panel">
                <div class="panel-head"><strong>Kelengkapan dokumen</strong><span>{{ $totalTransactions }} transaksi</span></div>
                <div class="doc-grid">
                    <div class="doc"><strong>{{ number_format($documentCounts['nota']) }}</strong><span>Nota</span><em>{{ $missingCounts['nota'] }} kurang</em></div>
                    <div class="doc"><strong>{{ number_format($documentCounts['kwitansi']) }}</strong><span>Kwitansi</span><em>{{ $missingCounts['kwitansi'] }} kurang</em></div>
                    <div class="doc"><strong>{{ number_format($documentCounts['bapb']) }}</strong><span>BAPB</span><em>{{ $missingCounts['bapb'] }} kurang</em></div>
                </div>
            </section>
            <section class="panel">
                <div class="panel-head"><strong>Aktivitas terbaru</strong><span>{{ number_format($activityLast7Days) }} dalam 7 hari</span></div>
                <div class="list">
                    @forelse($recentActivities as $activity)
                        <div class="item"><strong>{{ $activity->description }}</strong><span>{{ $activity->user?->name ?? 'User' }} · {{ $activity->created_at?->translatedFormat('d M Y H:i') }}</span></div>
                    @empty
                        <div class="item"><span>Belum ada aktivitas.</span></div>
                    @endforelse
                </div>
            </section>
            <section class="panel">
                <div class="panel-head"><strong>Akun terbaru</strong><a href="{{ route('admin.users.index') }}">Kelola →</a></div>
                <div class="list">
                    @forelse($recentUsers as $user)
                        <div class="item"><strong>{{ $user->name }}</strong><span>{{ $user->schoolProfile?->nama_sekolah ?? $user->email }} · {{ $user->isActive() ? 'Aktif' : 'Nonaktif' }}</span></div>
                    @empty
                        <div class="item"><span>Belum ada akun.</span></div>
                    @endforelse
                </div>
            </section>
        </div>
    </section>
</main>
</body>
</html>
