<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil Sekolah - SILPJ</title>
    <style>@include('school-profile._styles')</style>
</head>
<body>
@include('partials.app-navigation')

<header class="school-header">
    <div class="school-header-inner">
        <div>
            <h1>Profil Sekolah</h1>
            <p>Identitas sekolah dan penanggung jawab yang digunakan pada seluruh dokumen LPJ.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Dashboard</a>
    </div>
</header>

<main class="school-container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        $profileFields = [
            $school->nama_sekolah,
            $school->npsn,
            $school->alamat,
            $school->kepala_sekolah,
            $school->nip_kepala,
            $school->bendahara,
            $school->nip_bendahara,
        ];
        $filledFields = collect($profileFields)->filter(fn ($value) => filled($value))->count();
        $completeness = (int) round(($filledFields / count($profileFields)) * 100);
        $initials = collect(preg_split('/\s+/', trim($school->nama_sekolah)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');
    @endphp

    <section class="profile-hero">
        <div class="school-avatar">{{ $initials ?: 'S' }}</div>
        <div>
            <h2>{{ $school->nama_sekolah }}</h2>
            <p>NPSN {{ $school->npsn ?: 'belum diisi' }} · Profil digunakan sebagai identitas resmi dokumen SILPJ.</p>
            <div class="completeness">
                <div class="progress"><span style="width:{{ $completeness }}%"></span></div>
                <span class="progress-text">Kelengkapan {{ $completeness }}%</span>
            </div>
        </div>
        <div class="profile-actions">
            <a href="{{ route('school-profile.edit', $school) }}" class="btn btn-primary">Edit Profil Sekolah</a>
            <a href="{{ route('profile.edit') }}" class="btn btn-secondary">Profil Akun</a>
        </div>
    </section>

    @if($completeness < 100)
        <div class="alert alert-warning" style="margin-top:16px;margin-bottom:0">
            Profil sekolah belum lengkap. Lengkapi data penanggung jawab agar hasil cetak dokumen LPJ tidak memiliki bagian kosong.
        </div>
    @endif

    <section class="info-grid">
        <article class="info-card">
            <div class="info-label">Kepala Sekolah</div>
            <div class="info-value">{{ $school->kepala_sekolah ?: 'Belum diisi' }}</div>
            <div class="info-sub">NIP: {{ $school->nip_kepala ?: '-' }}</div>
        </article>
        <article class="info-card">
            <div class="info-label">Bendahara</div>
            <div class="info-value">{{ $school->bendahara ?: 'Belum diisi' }}</div>
            <div class="info-sub">NIP: {{ $school->nip_bendahara ?: '-' }}</div>
        </article>
        <article class="info-card">
            <div class="info-label">Status Identitas</div>
            <div class="info-value">{{ $completeness === 100 ? 'Lengkap' : $filledFields . ' dari ' . count($profileFields) . ' data terisi' }}</div>
            <div class="info-sub">Perubahan profil otomatis digunakan pada dokumen yang ditampilkan.</div>
        </article>
    </section>

    <section class="address-card">
        <h3>Alamat Sekolah</h3>
        <p>{{ $school->alamat ?: 'Alamat sekolah belum diisi.' }}</p>
    </section>

    <div class="section-note">
        <strong>Catatan dokumen</strong>
        Perubahan identitas sekolah akan memengaruhi identitas yang tampil saat Nota Pesanan, Kwitansi, BAPB, atau Laporan LPJ dibuka dan dicetak kembali.
    </div>
</main>
</body>
</html>
