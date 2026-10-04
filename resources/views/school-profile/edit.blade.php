<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Profil Sekolah - SILPJ</title>
    <style>@include('school-profile._styles')</style>
</head>
<body>
@include('partials.app-navigation')

<header class="school-header">
    <div class="school-header-inner">
        <div>
            <h1>Edit Profil Sekolah</h1>
            <p>Perbarui identitas yang digunakan pada dokumen dan laporan pertanggungjawaban.</p>
        </div>
        <a href="{{ route('school-profile.index') }}" class="btn btn-secondary">← Kembali ke Profil</a>
    </div>
</header>

<main class="school-container">
    <div class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> / <a href="{{ route('school-profile.index') }}">Profil Sekolah</a> / Edit</div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Perubahan belum dapat disimpan. Periksa kembali:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="form-card">
        <div class="form-card-head">
            <h2>Perbarui Informasi Sekolah</h2>
            <p>Pastikan nama penanggung jawab dan NIP ditulis sesuai dokumen resmi sekolah.</p>
        </div>
        <form action="{{ route('school-profile.update', $school) }}" method="POST" class="school-form">
            @csrf
            @method('PUT')
            @include('school-profile._form')
            <div class="form-actions">
                <a href="{{ route('school-profile.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
