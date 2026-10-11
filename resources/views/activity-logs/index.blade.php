<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Aktivitas - SILPJ</title>
    <style>
        :root {
            --primary: #1d4ed8;
            --primary-soft: #eff6ff;
            --success: #15803d;
            --success-soft: #f0fdf4;
            --warning: #b45309;
            --warning-soft: #fffbeb;
            --danger: #b91c1c;
            --danger-soft: #fef2f2;
            --purple: #7e22ce;
            --purple-soft: #faf5ff;
            --text: #172033;
            --muted: #667085;
            --border: #e4e7ec;
            --surface: #fff;
            --background: #f5f7fb;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: var(--background); color: var(--text); }
        button, input, select, a { font: inherit; }
        .page { width: min(1380px, 100%); margin: 0 auto; padding: 30px 32px 48px; }
        .page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; margin-bottom: 24px; }
        .page-head h1 { margin: 0; font-size: 28px; letter-spacing: -.4px; }
        .page-head p { margin: 8px 0 0; color: var(--muted); font-size: 14px; line-height: 1.55; }
        .security-note { max-width: 390px; padding: 12px 14px; border: 1px solid #bfdbfe; border-radius: 10px; background: var(--primary-soft); color: #1e40af; font-size: 12px; line-height: 1.5; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 14px; margin-bottom: 18px; }
        .stat { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 18px; box-shadow: 0 4px 14px rgba(16,24,40,.04); }
        .stat-label { color: var(--muted); font-size: 12px; font-weight: 700; }
        .stat-value { margin-top: 8px; font-size: 27px; font-weight: 800; letter-spacing: -.5px; }
        .stat-help { margin-top: 5px; color: #98a2b3; font-size: 11px; }
        .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 4px 14px rgba(16,24,40,.04); }
        .filter { padding: 18px; margin-bottom: 18px; }
        .filter-grid { display: grid; grid-template-columns: minmax(220px, 1.7fr) minmax(160px,.8fr) minmax(150px,.8fr) minmax(150px,.8fr) auto; gap: 10px; align-items: end; }
        .field label { display: block; margin-bottom: 6px; color: #344054; font-size: 11px; font-weight: 800; }
        .field input, .field select { width: 100%; height: 40px; border: 1px solid #d0d5dd; border-radius: 8px; background: #fff; padding: 0 11px; color: #344054; outline: none; }
        .field input:focus, .field select:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(59,130,246,.11); }
        .filter-actions { display: flex; gap: 7px; }
        .btn { height: 40px; padding: 0 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 1px solid transparent; border-radius: 8px; font-size: 12px; font-weight: 800; text-decoration: none; cursor: pointer; white-space: nowrap; }
        .btn-primary { color: #fff; background: var(--primary); }
        .btn-light { color: #475467; background: #fff; border-color: #d0d5dd; }
        .panel-head { padding: 16px 18px; border-bottom: 1px solid #eef0f3; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .panel-head strong { font-size: 14px; }
        .panel-head span { color: var(--muted); font-size: 11px; }
        .timeline { padding: 3px 18px 6px; }
        .log { display: grid; grid-template-columns: 42px minmax(0,1fr) 190px; gap: 13px; padding: 15px 0; border-bottom: 1px solid #eef0f3; }
        .log:last-child { border-bottom: 0; }
        .log-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900; }
        .icon-login { color: var(--success); background: var(--success-soft); }
        .icon-login_failed { color: var(--danger); background: var(--danger-soft); }
        .icon-logout { color: #475467; background: #f2f4f7; }
        .icon-create { color: var(--primary); background: var(--primary-soft); }
        .icon-update { color: var(--warning); background: var(--warning-soft); }
        .icon-delete { color: var(--danger); background: var(--danger-soft); }
        .icon-export { color: var(--purple); background: var(--purple-soft); }
        .icon-admin { color: #344054; background: #f2f4f7; }
        .icon-backup { color: #026aa2; background: #f0f9ff; }
        .log-title { margin-top: 1px; font-size: 13px; font-weight: 800; line-height: 1.45; }
        .log-meta { display: flex; flex-wrap: wrap; gap: 6px 12px; margin-top: 6px; color: var(--muted); font-size: 11px; line-height: 1.45; }
        .log-meta span { display: inline-flex; align-items: center; gap: 4px; }
        .changes { margin-top: 7px; padding: 7px 9px; background: #f8fafc; border: 1px solid #eef2f6; border-radius: 7px; color: #475467; font-size: 10px; }
        .log-time { text-align: right; }
        .log-time strong { display: block; color: #344054; font-size: 12px; }
        .log-time span { display: block; margin-top: 4px; color: #98a2b3; font-size: 10px; }
        .badge { display: inline-flex; align-items: center; min-height: 23px; padding: 0 8px; border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .25px; }
        .badge-login { color: var(--success); background: var(--success-soft); }
        .badge-login_failed { color: var(--danger); background: var(--danger-soft); }
        .badge-logout { color: #475467; background: #f2f4f7; }
        .badge-create { color: var(--primary); background: var(--primary-soft); }
        .badge-update { color: var(--warning); background: var(--warning-soft); }
        .badge-delete { color: var(--danger); background: var(--danger-soft); }
        .badge-export { color: var(--purple); background: var(--purple-soft); }
        .badge-admin { color: #344054; background: #f2f4f7; }
        .badge-backup { color: #026aa2; background: #f0f9ff; }
        .empty { padding: 48px 18px; text-align: center; }
        .empty strong { display: block; font-size: 15px; }
        .empty p { margin: 7px 0 0; color: var(--muted); font-size: 12px; }
        .pagination { padding: 14px 18px 18px; border-top: 1px solid #eef0f3; }
        .pagination nav > div:first-child { display: none; }
        .pagination nav > div:last-child { display: flex; align-items: center; justify-content: space-between; gap: 14px; }
        .pagination p { color: var(--muted); font-size: 11px; }
        .pagination a, .pagination span[aria-current="page"] span { border-radius: 7px !important; }
        @media (max-width: 1020px) {
            .stats { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .filter-grid { grid-template-columns: 1fr 1fr; }
            .filter-actions { grid-column: span 2; }
        }
        @media (max-width: 720px) {
            .page { padding: 22px 14px 36px; }
            .page-head { display: block; }
            .security-note { margin-top: 14px; max-width: none; }
            .stats { gap: 10px; }
            .stat { padding: 14px; }
            .stat-value { font-size: 23px; }
            .filter-grid { grid-template-columns: 1fr; }
            .filter-actions { grid-column: auto; }
            .log { grid-template-columns: 38px minmax(0,1fr); }
            .log-time { grid-column: 2; text-align: left; margin-top: -4px; }
        }
        @media (max-width: 430px) {
            .stats { grid-template-columns: 1fr; }
            .filter-actions { display: grid; grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
@include('partials.app-navigation')

@php
    $actionLabels = [
        'login' => 'Login',
        'login_failed' => 'Login gagal',
        'logout' => 'Logout',
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
        'export' => 'Ekspor',
        'admin' => 'Admin',
        'backup' => 'Backup',
    ];
    $actionIcons = [
        'login' => '↪',
        'login_failed' => '!',
        'logout' => '↩',
        'create' => '+',
        'update' => '✎',
        'delete' => '×',
        'export' => '↓',
        'admin' => '⚙',
        'backup' => '⇩',
    ];
    $subjectLabels = [
        'Belanja' => 'Data Belanja',
        'NotaPesanan' => 'Nota Pesanan',
        'Kwitansi' => 'Kwitansi',
        'Bapb' => 'BAPB',
        'SchoolProfile' => 'Profil Sekolah',
        'User' => 'Profil Akun',
    ];
@endphp

<main class="page">
    <div class="page-head">
        <div>
            <h1>Riwayat Aktivitas</h1>
            <p>Catatan aktivitas akun membantu menelusuri perubahan data dan penggunaan SILPJ.</p>
        </div>
        <div class="security-note">
            Riwayat ini hanya menampilkan aktivitas akun Anda. Kata sandi dan isi sensitif tidak disimpan di dalam catatan aktivitas.
        </div>
    </div>

    <section class="stats" aria-label="Ringkasan aktivitas">
        <div class="stat">
            <div class="stat-label">Total Aktivitas</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
            <div class="stat-help">Sesuai filter yang aktif</div>
        </div>
        <div class="stat">
            <div class="stat-label">Aktivitas Hari Ini</div>
            <div class="stat-value">{{ number_format($stats['hariIni']) }}</div>
            <div class="stat-help">{{ now()->translatedFormat('d F Y') }}</div>
        </div>
        <div class="stat">
            <div class="stat-label">Perubahan Data</div>
            <div class="stat-value">{{ number_format($stats['perubahanData']) }}</div>
            <div class="stat-help">Tambah, ubah, dan hapus data</div>
        </div>
        <div class="stat">
            <div class="stat-label">Login</div>
            <div class="stat-value">{{ number_format($stats['login']) }}</div>
            <div class="stat-help">Riwayat masuk ke aplikasi</div>
        </div>
    </section>

    <section class="panel filter">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="filter-grid">
            <div class="field">
                <label for="q">Cari aktivitas</label>
                <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Contoh: belanja, kwitansi, profil sekolah">
            </div>
            <div class="field">
                <label for="action">Jenis aktivitas</label>
                <select id="action" name="action">
                    <option value="">Semua aktivitas</option>
                    @foreach ($actionLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['action'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="tanggal_mulai">Dari tanggal</label>
                <input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ $filters['tanggal_mulai'] }}">
            </div>
            <div class="field">
                <label for="tanggal_selesai">Sampai tanggal</label>
                <input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ $filters['tanggal_selesai'] }}">
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" type="submit">Terapkan</button>
                <a class="btn btn-light" href="{{ route('activity-logs.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head">
            <strong>Aktivitas terbaru</strong>
            <span>{{ $logs->total() }} catatan ditemukan</span>
        </div>

        @if ($logs->count())
            <div class="timeline">
                @foreach ($logs as $log)
                    @php
                        $action = array_key_exists($log->action, $actionLabels) ? $log->action : 'update';
                        $changedFields = data_get($log->metadata, 'changed_fields', []);
                    @endphp
                    <article class="log">
                        <div class="log-icon icon-{{ $action }}" aria-hidden="true">{{ $actionIcons[$action] ?? '•' }}</div>
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                <div class="log-title">{{ $log->description }}</div>
                                <span class="badge badge-{{ $action }}">{{ $actionLabels[$action] ?? ucfirst($action) }}</span>
                            </div>
                            <div class="log-meta">
                                @if ($log->subject_type)
                                    <span>Data: {{ $subjectLabels[$log->subject_type] ?? $log->subject_type }}@if($log->subject_id) #{{ $log->subject_id }}@endif</span>
                                @endif
                                @if ($log->ip_address)
                                    <span>IP: {{ $log->ip_address }}</span>
                                @endif
                            </div>
                            @if (is_array($changedFields) && count($changedFields))
                                <div class="changes">Field yang berubah: {{ implode(', ', $changedFields) }}</div>
                            @endif
                        </div>
                        <div class="log-time">
                            <strong>{{ $log->created_at->translatedFormat('d M Y, H:i') }}</strong>
                            <span>{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
            @if ($logs->hasPages())
                <div class="pagination">{{ $logs->links() }}</div>
            @endif
        @else
            <div class="empty">
                <strong>Belum ada aktivitas yang sesuai.</strong>
                <p>Coba ubah filter atau lakukan aktivitas baru di SILPJ.</p>
            </div>
        @endif
    </section>
</main>
</body>
</html>
