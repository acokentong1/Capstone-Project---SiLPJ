<x-guest-layout>
    <h2 class="auth-title">Buat kata sandi baru</h2>
    <p class="auth-subtitle">Masukkan email akun dan kata sandi baru untuk menyelesaikan proses pemulihan.</p>

    <form method="POST" action="{{ route('password.store') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="auth-field">
            <label for="email">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @foreach($errors->get('email') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <div class="auth-field">
            <label for="password">Kata Sandi Baru</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
            @foreach($errors->get('password') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <div class="auth-field">
            <label for="password_confirmation">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi">
            @foreach($errors->get('password_confirmation') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <button type="submit" class="auth-button">Simpan Kata Sandi Baru</button>
    </form>
</x-guest-layout>
