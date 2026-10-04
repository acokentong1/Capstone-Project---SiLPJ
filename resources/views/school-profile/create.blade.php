<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lengkapi Profil Sekolah - SILPJ</title>
    <style>@include('school-profile._styles')</style>
</head>
<body>
@include('partials.app-navigation')

<header class="school-header">
    <div class="school-header-inner">
        <div>
            <h1>Lengkapi Profil Sekolah</h1>
            <p>Isi identitas sekolah sebelum membuat dokumen pertanggungjawaban.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Dashboard</a>
    </div>
</header>

<main class="school-container">
    <div class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> / Profil Sekolah / Lengkapi</div>

    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Data belum dapat disimpan. Periksa kembali isian berikut:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="form-card">
        <div class="form-card-head">
            <h2>Informasi Sekolah</h2>
            <p>Nama sekolah wajib diisi. Data lainnya sangat disarankan karena dipakai pada hasil cetak dokumen LPJ.</p>
        </div>
        <form action="{{ route('school-profile.store') }}" method="POST" class="school-form">
            @csrf
            @include('school-profile._form')
            <div class="form-actions">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Profil Sekolah</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
