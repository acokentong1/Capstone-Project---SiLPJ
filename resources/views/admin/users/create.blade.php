@extends('accounts.layout')
@section('title', 'Tambah Akun')
@section('content')
<p>Buat akun dan tetapkan peran serta sekolah dalam satu langkah.</p>
@if($schools->isEmpty())
    <div class="notice">Belum ada sekolah. <a href="{{ route('school-profile.create') }}">Lengkapi profil sekolah terlebih dahulu.</a></div>
@else
    <form method="POST" action="{{ route('admin.users.store') }}" class="card">
        @csrf
        <div class="grid">
            <div>
                <label for="name">Nama lengkap</label>
                <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autofocus>
            </div>
            <div>
                <label for="email">Email untuk login</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="off">
            </div>
            <div>
                <label for="role">Peran</label>
                <select id="role" name="role" required>
                    @foreach(['bendahara'=>'Bendahara', 'kepala_sekolah'=>'Kepala Sekolah', 'admin'=>'Admin', 'user'=>'Pembaca'] as $value=>$label)
                        <option value="{{ $value }}" @selected(old('role', 'bendahara') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="school_profile_id">Sekolah</label>
                <select id="school_profile_id" name="school_profile_id" required>
                    <option value="">Pilih sekolah</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" @selected((string) old('school_profile_id', $selectedSchool) === (string) $school->id)>{{ $school->nama_sekolah }} — {{ $school->npsn }}</option>
                    @endforeach
                </select>
            </div>
            @include('accounts.password-fields')
        </div>
        <p class="hint">Admin mengelola akun. Bendahara menyiapkan LPJ. Kepala sekolah memeriksa LPJ. Pembaca hanya melihat data. Gunakan akun berbeda untuk bendahara dan pemeriksa.</p>
        <div class="actions"><button type="submit">Buat Akun</button><a href="{{ route('admin.users.index') }}">Kembali</a></div>
    </form>
@endif
@endsection
