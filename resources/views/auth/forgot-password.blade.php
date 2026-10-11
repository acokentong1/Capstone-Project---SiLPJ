<x-guest-layout>
    <a href="{{ route('login') }}" class="auth-back">← Kembali ke halaman masuk</a>
    <h2 class="auth-title">Lupa kata sandi</h2>
    <p class="auth-subtitle">Masukkan email akun. Jika layanan email sudah dikonfigurasi, sistem akan mengirim tautan untuk membuat kata sandi baru.</p>

    @if(session('status'))<div class="auth-alert success">{{ session('status') }}</div>@endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="email">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Email akun SILPJ">
            @foreach($errors->get('email') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <button type="submit" class="auth-button">Kirim Tautan Reset</button>
    </form>
</x-guest-layout>
