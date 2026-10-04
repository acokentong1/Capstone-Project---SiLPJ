@php
    $navUser = Auth::user();
    $navSchool = $navUser?->schoolProfile;
@endphp

<style>
    .silpj-nav {
        position: sticky;
        top: 0;
        z-index: 50;
        background: rgba(255, 255, 255, 0.97);
        border-bottom: 1px solid #e4e7ec;
        box-shadow: 0 2px 10px rgba(16, 24, 40, 0.04);
        backdrop-filter: blur(10px);
    }
    .silpj-nav * { box-sizing: border-box; }
    .silpj-nav-inner {
        width: min(1500px, 100%);
        min-height: 62px;
        margin: 0 auto;
        padding: 0 28px;
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .silpj-nav-brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
        color: #172033;
        text-decoration: none;
    }
    .silpj-nav-logo {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        color: #fff;
        box-shadow: 0 6px 16px rgba(37, 99, 235, .22);
    }
    .silpj-nav-logo svg { width: 21px; height: 21px; }
    .silpj-nav-brand-text strong {
        display: block;
        font-size: 15px;
        line-height: 1.1;
        letter-spacing: .1px;
    }
    .silpj-nav-brand-text span {
        display: block;
        margin-top: 3px;
        color: #667085;
        font-size: 10px;
        line-height: 1.1;
        white-space: nowrap;
    }
    .silpj-nav-links {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 3px;
        min-width: 0;
    }
    .silpj-nav-link {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border-radius: 8px;
        color: #475467;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        transition: .16s ease;
    }
    .silpj-nav-link:hover {
        color: #1d4ed8;
        background: #eff6ff;
    }
    .silpj-nav-link.active {
        color: #1d4ed8;
        background: #eff6ff;
    }
    .silpj-nav-account {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .silpj-nav-user {
        text-align: right;
        min-width: 0;
    }
    .silpj-nav-user strong {
        display: block;
        max-width: 190px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #344054;
        font-size: 12px;
    }
    .silpj-nav-user span {
        display: block;
        max-width: 190px;
        margin-top: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #98a2b3;
        font-size: 10px;
    }
    .silpj-nav-icon-link,
    .silpj-nav-logout {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        background: #fff;
        color: #475467;
        text-decoration: none;
        cursor: pointer;
        transition: .16s ease;
    }
    .silpj-nav-icon-link:hover { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; }
    .silpj-nav-logout:hover { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
    .silpj-nav-icon-link svg,
    .silpj-nav-logout svg { width: 17px; height: 17px; }
    .silpj-nav-mobile { display: none; margin-left: auto; }
    .silpj-nav-mobile summary {
        width: 38px;
        height: 38px;
        list-style: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d0d5dd;
        border-radius: 9px;
        background: #fff;
        color: #344054;
        cursor: pointer;
    }
    .silpj-nav-mobile summary::-webkit-details-marker { display: none; }
    .silpj-nav-mobile summary svg { width: 19px; height: 19px; }
    .silpj-nav-mobile-panel {
        position: absolute;
        top: 62px;
        left: 12px;
        right: 12px;
        padding: 12px;
        background: #fff;
        border: 1px solid #e4e7ec;
        border-radius: 12px;
        box-shadow: 0 14px 30px rgba(16, 24, 40, .12);
    }
    .silpj-nav-mobile-user {
        padding: 5px 7px 12px;
        margin-bottom: 8px;
        border-bottom: 1px solid #eef0f3;
    }
    .silpj-nav-mobile-user strong { display: block; color: #344054; font-size: 13px; }
    .silpj-nav-mobile-user span { display: block; margin-top: 3px; color: #667085; font-size: 11px; }
    .silpj-nav-mobile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .silpj-nav-mobile-grid .silpj-nav-link {
        justify-content: flex-start;
        min-height: 38px;
        padding: 0 10px;
    }
    .silpj-nav-mobile-footer {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eef0f3;
    }
    .silpj-nav-mobile-action {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        background: #fff;
        color: #344054;
        text-decoration: none;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }
    .silpj-nav-mobile-action.danger { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
    .silpj-nav-mobile-action svg { width: 15px; height: 15px; }

    @media (max-width: 1120px) {
        .silpj-nav-links,
        .silpj-nav-account { display: none; }
        .silpj-nav-mobile { display: block; }
    }
    @media (max-width: 600px) {
        .silpj-nav-inner { min-height: 58px; padding: 0 14px; }
        .silpj-nav-brand-text span { display: none; }
        .silpj-nav-mobile-panel { top: 58px; }
    }
    @media print {
        .silpj-nav { display: none !important; }
    }
</style>

<nav class="silpj-nav no-print" aria-label="Navigasi utama SILPJ">
    <div class="silpj-nav-inner">
        <a href="{{ route('dashboard') }}" class="silpj-nav-brand" aria-label="SILPJ Dashboard">
            <span class="silpj-nav-logo" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 3.5h7l3 3V20a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z"/>
                    <path d="M14 3.5V7h3.5M9 11h6M9 14h6M9 17h4"/>
                    <path d="m4.7 13.4 1.3 1.3 2.2-2.4"/>
                </svg>
            </span>
            <span class="silpj-nav-brand-text">
                <strong>SILPJ</strong>
                <span>Sistem Informasi LPJ Sekolah</span>
            </span>
        </a>

        <div class="silpj-nav-links">
            <a href="{{ route('dashboard') }}" class="silpj-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('belanja.index') }}" class="silpj-nav-link {{ request()->routeIs('belanja.*') ? 'active' : '' }}">Data Belanja</a>
            <a href="{{ route('nota-pesanan.index') }}" class="silpj-nav-link {{ request()->routeIs('nota-pesanan.*') ? 'active' : '' }}">Nota Pesanan</a>
            <a href="{{ route('kwitansi.index') }}" class="silpj-nav-link {{ request()->routeIs('kwitansi.*') ? 'active' : '' }}">Kwitansi</a>
            <a href="{{ route('bapb.index') }}" class="silpj-nav-link {{ request()->routeIs('bapb.*') ? 'active' : '' }}">BAPB</a>
            <a href="{{ route('laporan-lpj.index') }}" class="silpj-nav-link {{ request()->routeIs('laporan-lpj.*') ? 'active' : '' }}">Laporan LPJ</a>
            <a href="{{ route('activity-logs.index') }}" class="silpj-nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">Aktivitas</a>
            @if($navUser?->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="silpj-nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">Admin</a>
            @endif
        </div>

        <div class="silpj-nav-account">
            <div class="silpj-nav-user">
                <strong>{{ $navUser?->name }}</strong>
                <span>{{ $navSchool?->nama_sekolah ?? 'Profil sekolah belum lengkap' }}@if($navUser?->isAdmin()) · Admin @endif</span>
            </div>
            <a href="{{ route('school-profile.index') }}" class="silpj-nav-icon-link" title="Profil Sekolah" aria-label="Profil Sekolah">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M5 21V8l7-4 7 4v13M9 21v-6h6v6M8 11h.01M12 11h.01M16 11h.01"/>
                </svg>
            </a>
            <a href="{{ route('profile.edit') }}" class="silpj-nav-icon-link" title="Profil Akun" aria-label="Profil Akun">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="3.5"/><path d="M5 20c.8-4 3.1-6 7-6s6.2 2 7 6"/>
                </svg>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="silpj-nav-logout" title="Keluar" aria-label="Keluar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4M14 8l4 4-4 4M18 12H9"/>
                    </svg>
                </button>
            </form>
        </div>

        <details class="silpj-nav-mobile">
            <summary aria-label="Buka menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </summary>
            <div class="silpj-nav-mobile-panel">
                <div class="silpj-nav-mobile-user">
                    <strong>{{ $navUser?->name }}</strong>
                    <span>{{ $navSchool?->nama_sekolah ?? $navUser?->email }}</span>
                </div>
                <div class="silpj-nav-mobile-grid">
                    <a href="{{ route('dashboard') }}" class="silpj-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('belanja.index') }}" class="silpj-nav-link {{ request()->routeIs('belanja.*') ? 'active' : '' }}">Data Belanja</a>
                    <a href="{{ route('nota-pesanan.index') }}" class="silpj-nav-link {{ request()->routeIs('nota-pesanan.*') ? 'active' : '' }}">Nota Pesanan</a>
                    <a href="{{ route('kwitansi.index') }}" class="silpj-nav-link {{ request()->routeIs('kwitansi.*') ? 'active' : '' }}">Kwitansi</a>
                    <a href="{{ route('bapb.index') }}" class="silpj-nav-link {{ request()->routeIs('bapb.*') ? 'active' : '' }}">BAPB</a>
                    <a href="{{ route('laporan-lpj.index') }}" class="silpj-nav-link {{ request()->routeIs('laporan-lpj.*') ? 'active' : '' }}">Laporan LPJ</a>
                    <a href="{{ route('activity-logs.index') }}" class="silpj-nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">Aktivitas</a>
                    @if($navUser?->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="silpj-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard Admin</a>
                        <a href="{{ route('admin.monitoring.index') }}" class="silpj-nav-link {{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}">Monitoring LPJ</a>
                        <a href="{{ route('admin.users.index') }}" class="silpj-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Admin User</a>
                        <a href="{{ route('admin.backups.index') }}" class="silpj-nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">Backup & Restore</a>
                    @endif
                </div>
                <div class="silpj-nav-mobile-footer">
                    <a href="{{ route('school-profile.index') }}" class="silpj-nav-mobile-action">Profil Sekolah</a>
                    <a href="{{ route('profile.edit') }}" class="silpj-nav-mobile-action">Profil Akun</a>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:7px 0 0">
                    @csrf
                    <button type="submit" class="silpj-nav-mobile-action danger" style="width:100%">Keluar dari akun</button>
                </form>
            </div>
        </details>
    </div>
</nav>
