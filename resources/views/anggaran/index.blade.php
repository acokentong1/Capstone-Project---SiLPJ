<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Anggaran - SILPJ</title>
<style>
body{margin:0;background:#f5f7fb;font-family:Arial;color:#172033}
.wrap{width:min(980px,92%);margin:35px auto}
.head{background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;padding:24px;border-radius:12px}
.card{background:#fff;margin-top:22px;border-radius:14px;padding:22px;box-shadow:0 4px 15px #ddd}
.btn{padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:bold}
.primary{background:#2563eb;color:#fff}
table{width:100%;border-collapse:collapse;margin-top:20px}
th,td{padding:14px;border-bottom:1px solid #eee;text-align:left}
.badge{background:#eff6ff;color:#2563eb;padding:5px 10px;border-radius:20px}
</style>
</head>
<body>
@include('partials.app-navigation')

<div class="wrap">
<div class="head">
<h1>Riwayat Anggaran</h1>
<p>Daftar tahun anggaran yang sudah dimasukkan ke sistem.</p>
</div>

<div class="card">
@if(!in_array(auth()->user()->role, ['kepala_sekolah']))
    <a class="btn primary" href="{{route('anggaran.create')}}">+ Tambah Anggaran</a>
@endif

<table>
<tr>
<th>Tahun</th>
<th>Jumlah Anggaran</th>
<th>Status</th>
</tr>

@forelse($anggarans as $a)
<tr>
<td><span class="badge">{{$a->tahun}}</span></td>
<td>Rp {{number_format($a->jumlah,0,',','.')}}</td>
<td>Aktif</td>
</tr>
@empty
<tr><td colspan="3">Belum ada anggaran.</td></tr>
@endforelse

</table>
</div>
</div>
</body>
</html>
