<x-guest-layout>
    <h2 class="auth-title">Verifikasi email</h2>
    <p class="auth-subtitle">Periksa kotak masuk email Anda dan buka tautan verifikasi yang dikirim oleh sistem.</p>

    @if(session('status') === 'verification-link-sent')
        <div class="auth-alert success">Tautan verifikasi baru sudah dikirim ke email yang digunakan saat pendaftaran.</div>
    @else
        <div class="auth-alert info">Belum menerima email? Anda dapat meminta sistem mengirim ulang tautan verifikasi.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="auth-form">
        @csrf
        <button type="submit" class="auth-button">Kirim Ulang Email Verifikasi</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:10px">
        @csrf
        <button type="submit" class="auth-secondary-button">Keluar dari Akun</button>
    </form>
</x-guest-layout>
