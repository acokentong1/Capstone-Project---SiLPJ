@extends('accounts.layout')
@section('title', 'Kelola Akun')
@section('content')
<p>{{ $user->name }} · {{ $user->email }}</p>
<a href="{{ route('admin.users.show', $user) }}">← Kembali ke detail akun</a>
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="card">
    @csrf
    @method('PATCH')
    <h2>Identitas akun</h2>
    <div class="grid">
        <div><label for="name">Nama lengkap</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255"></div>
        <div><label for="email">Email untuk login</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255"></div>
    </div>
    <div class="actions"><button type="submit">Simpan Identitas</button></div>
</form>
<form method="POST" action="{{ route('admin.users.password', $user) }}" class="card">
    @csrf
    @method('PUT')
    <h2>Reset kata sandi</h2>
    <p>Gunakan saat pemilik akun lupa kata sandi. Sampaikan kata sandi baru secara pribadi.</p>
    <div class="grid">
        <div class="wide"><label for="current_password">Kata sandi Anda sebagai admin (konfirmasi)</label><input id="current_password" type="password" name="current_password" required autocomplete="current-password"></div>
        @include('accounts.password-fields')
    </div>
    <div class="actions"><button type="submit">Reset Kata Sandi</button></div>
</form>
@endsection
