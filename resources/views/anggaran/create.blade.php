<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Anggaran - SILPJ</title>
<style>
:root{--primary:#1d4ed8;--primary-dark:#1e40af;--text:#172033;--muted:#667085;--border:#e4e7ec;--background:#f5f7fb}
*{box-sizing:border-box}body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--background);color:var(--text)}button,input,a{font:inherit}
.header{background:linear-gradient(135deg,#1d4ed8,#2563eb);color:white;padding:22px 30px}.header-inner,.container{width:min(980px,100%);margin:auto}.header h1{margin:0;font-size:24px}.header p{margin:6px 0 0;font-size:13px;opacity:.9}
.container{padding:28px 22px 42px}.breadcrumb{margin-bottom:14px;color:var(--muted);font-size:12px}.breadcrumb a{color:var(--primary);text-decoration:none;font-weight:700}
.card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 3px 14px rgba(16,24,40,.05);overflow:hidden}.card-head{padding:20px 22px;border-bottom:1px solid var(--border)}.card-head h2{margin:0 0 6px;font-size:20px}.card-head p{margin:0;color:var(--muted);font-size:13px}
form{padding:22px}.field{margin-bottom:18px}.field label{display:block;margin-bottom:7px;font-size:13px;font-weight:800;color:#344054}.field input{width:100%;height:42px;border:1px solid #d0d5dd;border-radius:8px;padding:10px 11px}.money{display:flex;border:1px solid #d0d5dd;border-radius:8px;overflow:hidden}.money span{padding:11px;background:#f8fafc;font-weight:bold}.money input{border:0;border-radius:0}.actions{display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--border);padding-top:18px;margin-top:20px}.btn{display:inline-flex;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:800;border:1px solid transparent}.primary{background:#2563eb;color:white}.secondary{background:white;color:#344054;border-color:#d0d5dd}
</style>
</head>
<body>
@include('partials.app-navigation')
<header class="header"><div class="header-inner"><h1>Tambah Anggaran</h1><p>Masukkan pagu anggaran tahun berjalan untuk perhitungan ringkasan keuangan.</p></div></header>
<main class="container">
<div class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> / Tambah Anggaran</div>
<section class="card">
<div class="card-head"><h2>Informasi Anggaran</h2><p>Pastikan jumlah anggaran sesuai dengan dokumen sumber.</p></div>
<form action="{{ route('anggaran.store') }}" method="POST">
@csrf
@if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
<div class="field"><label>Tahun Anggaran</label><input type="number" name="tahun" value="{{ date('Y') }}" required></div>
<div class="field"><label>Jumlah Anggaran</label><div class="money"><span>Rp</span><input type="number" name="jumlah" placeholder="Masukkan jumlah anggaran" required></div></div>
<div class="actions"><a href="{{ route('dashboard') }}" class="btn secondary">Kembali</a><button class="btn primary">Simpan Anggaran</button></div>
</form>
</section>
</main>
</body>
</html>
