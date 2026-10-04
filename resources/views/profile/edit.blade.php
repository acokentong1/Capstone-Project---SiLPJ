<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil Akun - SILPJ</title>
    <style>@include('profile._styles')</style>
</head>
<body>
@include('partials.app-navigation')

<header class="account-header">
    <div class="account-header-inner">
        <div>
            <h1>Profil Akun</h1>
            <p>Kelola identitas pengguna, kata sandi, dan keamanan akun SILPJ.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Dashboard</a>
    </div>
</header>

<main class="account-container">
    @php
        $accountInitials = collect(preg_split('/\s+/', trim($user->name)))
            ->filter()->take(2)
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');
        $school = $user->schoolProfile;
    @endphp

    <section class="account-summary">
        <div class="account-avatar">{{ $accountInitials ?: 'U' }}</div>
        <div>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }} · {{ $school?->nama_sekolah ?? 'Profil sekolah belum dilengkapi' }}</p>
        </div>
        <div class="account-summary-actions">
            <a href="{{ route('school-profile.index') }}" class="btn btn-secondary">Profil Sekolah</a>
            <a href="{{ route('belanja.index') }}" class="btn btn-primary">Data Belanja</a>
        </div>
    </section>

    <div class="account-grid">
        <section class="account-card">
            <div class="card-head">
                <h3>Informasi Akun</h3>
                <p>Nama dan email yang digunakan untuk masuk ke SILPJ.</p>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

        <section class="account-card">
            <div class="card-head">
                <h3>Keamanan Akun</h3>
                <p>Gunakan kata sandi yang kuat dan tidak digunakan pada layanan lain.</p>
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </section>

        <section class="account-card full danger-card">
            <div class="card-head">
                <h3>Zona Berisiko</h3>
                <p>Penghapusan akun bersifat permanen dan ikut menghapus data yang bergantung pada akun.</p>
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </section>
    </div>
</main>
</body>
</html>
