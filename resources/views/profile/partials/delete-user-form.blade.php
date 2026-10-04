@if($errors->userDeletion->isNotEmpty())
    <div class="alert alert-error">Kata sandi tidak sesuai. Akun belum dihapus.</div>
@endif

<details class="danger-details" @if($errors->userDeletion->isNotEmpty()) open @endif>
    <summary>Tampilkan opsi penghapusan akun</summary>
    <div class="danger-panel">
        <p>
            Setelah akun dihapus, data akun dan sumber daya yang terkait tidak dapat dipulihkan melalui aplikasi. Pastikan data yang masih diperlukan sudah disimpan terlebih dahulu.
        </p>
        <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Hapus akun SILPJ secara permanen? Tindakan ini tidak dapat dibatalkan.');">
            @csrf
            @method('DELETE')
            <div class="field">
                <label for="delete_account_password">Konfirmasi Kata Sandi</label>
                <input id="delete_account_password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi akun">
                @foreach($errors->userDeletion->get('password') as $message)<div class="field-error">{{ $message }}</div>@endforeach
            </div>
            <div class="card-actions">
                <button type="submit" class="btn btn-danger">Hapus Akun Permanen</button>
            </div>
        </form>
    </div>
</details>
