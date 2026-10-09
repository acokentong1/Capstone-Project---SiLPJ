<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan LPJ SILPJ</title>
<style>
body{font-family:Arial;font-size:12px}
.header{text-align:center}
table{width:100%;border-collapse:collapse;margin-top:20px}
th{background:#2563eb;color:white;padding:8px}
td{padding:8px;border:1px solid #ddd}
.ttd{margin-top:60px}
</style>
</head>

<body>

<div class="header">
<h2>LAPORAN PERTANGGUNGJAWABAN SILPJ</h2>
<p>SDN 007 SABANG SUBIK</p>
<p>
TAHUN ANGGARAN {{ $tahunAnggaran ?? '-' }}
</p>
<hr>
</div>

<table>
<thead>
<tr>
<th>No</th>
<th>Tanggal</th>
<th>No Bukti</th>
<th>Uraian</th>
<th>Kategori</th>
<th>Total</th>
</tr>
</thead>

<tbody>
@foreach($items as $key=>$item)
<tr>
<td>{{ $key+1 }}</td>
<td>{{ $item->tanggal }}</td>
<td>{{ $item->nomor_bukti }}</td>
<td>{{ $item->uraian }}</td>
<td>{{ $item->kategori }}</td>
<td>Rp {{ number_format($item->total,0,',','.') }}</td>
</tr>
@endforeach
</tbody>
</table>

<h3>
Total Realisasi:
Rp {{ number_format($total,0,',','.') }}
</h3>

<table class="ttd">
<tr>
<td style="text-align:center;border:none">
Mengetahui<br>
Kepala Sekolah<br><br><br>
(....................)
</td>

<td style="text-align:center;border:none">
Bendahara<br><br><br>
(....................)
</td>
</tr>
</table>

</body>
</html>
