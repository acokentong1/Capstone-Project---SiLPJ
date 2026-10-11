<x-guest-layout>
    <h2 class="auth-title">Konfirmasi keamanan</h2>
    <p class="auth-subtitle">Halaman ini memerlukan konfirmasi kata sandi sebelum Anda dapat melanjutkan.</p>
    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="password">Kata Sandi</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" autofocus>
            @foreach($errors->get('password') as $message)<div class="auth-error">{{ $message }}</div>@endforeach
        </div>
        <button type="submit" class="auth-button">Konfirmasi</button>
    </form>
</x-guest-layout>
