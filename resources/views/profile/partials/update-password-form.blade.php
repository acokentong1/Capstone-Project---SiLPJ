@if(session('status') === 'password-updated')
    <div class="alert alert-success">Kata sandi berhasil diperbarui.</div>
@endif

<div class="security-note">
    Minimal 8 karakter. Gunakan kombinasi yang tidak mudah ditebak dan hindari memakai kata sandi yang sama dengan akun lain.
</div>

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    @method('PUT')

    <div class="form-stack">
        <div class="field">
            <label for="update_password_current_password">Kata Sandi Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password">
            @foreach($errors->updatePassword->get('current_password') as $message)<div class="field-error">{{ $message }}</div>@endforeach
        </div>
        <div class="field">
            <label for="update_password_password">Kata Sandi Baru</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password">
            @foreach($errors->updatePassword->get('password') as $message)<div class="field-error">{{ $message }}</div>@endforeach
        </div>
        <div class="field">
            <label for="update_password_password_confirmation">Konfirmasi Kata Sandi Baru</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
            @foreach($errors->updatePassword->get('password_confirmation') as $message)<div class="field-error">{{ $message }}</div>@endforeach
        </div>
    </div>

    <div class="card-actions">
        <button type="submit" class="btn btn-primary">Perbarui Kata Sandi</button>
    </div>
</form>
