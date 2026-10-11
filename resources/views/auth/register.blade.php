<x-guest-layout>
    <a href="{{ url('/') }}" class="auth-back">← Kembali ke halaman awal</a>
    <h2 class="auth-title">Buat akun SILPJ</h2>
    <p class="auth-subtitle">Buat satu akun untuk mengelola profil sekolah dan seluruh dokumen pertanggungjawaban.</p>

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="name">Nama Pengguna</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama lengkap">
            @foreach($errors->get('name') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <div class="auth-field">
            <label for="email">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@sekolah.sch.id">
            @foreach($errors->get('email') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <div class="auth-field">
            <label for="password">Kata Sandi</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
            <div class="auth-help">Gunakan kata sandi yang kuat dan tidak mudah ditebak.</div>
            @foreach($errors->get('password') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <div class="auth-field">
            <label for="password_confirmation">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi">
            @foreach($errors->get('password_confirmation') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <button type="submit" class="auth-button">Daftar dan Mulai</button>
    </form>

    <div class="auth-footer">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk</a></div>
</x-guest-layout>
