@if(session('status') === 'profile-updated')
    <div class="alert alert-success">Informasi akun berhasil diperbarui.</div>
@endif

<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PATCH')

    <div class="form-stack">
        <div class="field">
            <label for="name">Nama Pengguna</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @foreach($errors->get('name') as $message)<div class="field-error">{{ $message }}</div>@endforeach
        </div>

        <div class="field">
            <label for="email">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <div class="field-help">Email digunakan sebagai identitas login akun.</div>
            @foreach($errors->get('email') as $message)<div class="field-error">{{ $message }}</div>@endforeach
        </div>
    </div>

    <div class="account-meta">
        <div class="meta-box">
            <span>Akun dibuat</span>
            <strong>{{ optional($user->created_at)->translatedFormat('d F Y') ?? '-' }}</strong>
        </div>
        <div class="meta-box">
            <span>Terakhir diperbarui</span>
            <strong>{{ optional($user->updated_at)->translatedFormat('d F Y, H:i') ?? '-' }}</strong>
        </div>
    </div>

    <div class="card-actions">
        <button type="submit" class="btn btn-primary">Simpan Informasi Akun</button>
    </div>
</form>
