<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Belanja - SILPJ</title>
<style>
.alert {margin-bottom:18px;padding:14px 16px;border-radius:10px;font-size:14px;line-height:1.5}
.alert-warning {border:1px solid #fde68a;background:#fffbeb;color:#92400e}
.alert strong {display:block;margin-bottom:5px}
.alert ul {margin:5px 0 0;padding-left:18px}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;color:#172033}
.header{background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;padding:22px 30px}
.header-inner,.container{width:min(980px,100%);margin:auto}
.container{padding:28px 22px}
.card{background:#fff;border:1px solid #e4e7ec;border-radius:14px;box-shadow:0 3px 14px rgba(16,24,40,.05);overflow:hidden}
.card-head{padding:20px 22px;border-bottom:1px solid #e4e7ec}
.card-head h2{margin:0}
form{padding:22px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:17px}
.field.full{grid-column:1/-1}
.field label{display:block;font-weight:700;margin-bottom:7px}
input,select,textarea{width:100%;padding:10px;border:1px solid #d0d5dd;border-radius:8px;box-sizing:border-box}
.btn{padding:10px 16px;border-radius:8px;border:0;font-weight:700;text-decoration:none}
.btn-primary{background:#1d4ed8;color:white}
.btn-secondary{border:1px solid #d0d5dd;color:#344054}
.form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}
@media(max-width:650px){.form-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

@include('partials.app-navigation')

<header class="header">
<div class="header-inner">
<h1>Tambah Data Belanja</h1>
<p>Masukkan transaksi dengan tahun anggaran yang sesuai.</p>
</div>
</header>

<main class="container">
<div class="card">
<div class="card-head">
<h2>Informasi Transaksi</h2>
</div>

<form action="{{ route('belanja.store') }}" method="POST">
@csrf

@if(session('error'))
<div class="alert alert-warning">
    <strong>⚠ Perhatian</strong>
    {{ session('error') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-warning">
    <strong>⚠ Perhatian</strong>
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="form-grid">

<div class="field full">
<label>Tahun Anggaran *</label>
<select name="tahun_anggaran" required>
<option value="">Pilih Tahun Anggaran</option>
@foreach(($tahunAnggaran ?? []) as $tahun)
<option value="{{ $tahun }}">{{ $tahun }}</option>
@endforeach
</select>
</div>

</div>

@include('belanja._form')

</form>
</div>
</main>

</body>
</html>
