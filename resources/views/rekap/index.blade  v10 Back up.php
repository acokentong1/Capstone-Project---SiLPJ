<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekap Keuangan SILPJ</title>

<style>
@include('documents._app-styles')

.summary-grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:16px;
margin:25px auto;
width:min(1380px,100%);
}

.panel{
background:#fff;
border:1px solid var(--border);
border-radius:12px;
padding:18px;
box-shadow:0 3px 12px rgba(16,24,40,.04);
}

.panel h3{
margin:0 0 8px;
font-size:14px;
}

.value{
font-size:26px;
font-weight:800;
color:#172033;
}

.chart-grid{
display:grid;
grid-template-columns:1.5fr 1fr;
gap:16px;
width:min(1380px,100%);
margin:0 auto 18px;
}

.chart-box{
height:360px;
}

.table-wrap{
width:min(1380px,100%);
margin:0 auto 30px;
}

.table-silpj{
width:100%;
border-collapse:collapse;
background:white;
border-radius:12px;
overflow:hidden;
}

.table-silpj th{
background:#2563eb;
color:white;
padding:12px;
text-align:left;
}

.table-silpj td{
padding:12px;
border-bottom:1px solid #e4e7ec;
}

.badge{
background:#eff6ff;
color:#1d4ed8;
padding:5px 10px;
border-radius:999px;
font-size:12px;
font-weight:700;
}

@media(max-width:900px){
.summary-grid,.chart-grid{
grid-template-columns:1fr;
}
}
</style>

</head>

<body>

@include('partials.app-navigation')

<div class="header">
<div class="header-inner">
<h1>Rekap Keuangan SILPJ</h1>
<p>Ringkasan transaksi, komposisi belanja, dan laporan penggunaan anggaran.</p>
</div>
</div>

<div class="summary-grid">

<div class="panel">
<h3>Total Belanja</h3>
<div class="value">Rp {{ number_format($summary['total'],0,',','.') }}</div>
</div>

<div class="panel">
<h3>Jumlah Transaksi</h3>
<div class="value">{{ $summary['jumlah'] }}</div>
</div>

<div class="panel">
<h3>Belanja Barang</h3>
<div class="value">Rp {{ number_format($summary['barang'],0,',','.') }}</div>
</div>

<div class="panel">
<h3>Belanja Jasa</h3>
<div class="value">Rp {{ number_format($summary['jasa'],0,',','.') }}</div>
</div>

</div>


<div class="chart-grid">

<div class="panel">
<h3>Pengeluaran Bulanan</h3>
<div class="chart-box">
<canvas id="chartBulanan"></canvas>
</div>
</div>

<div class="panel">
<h3>Komposisi Belanja</h3>
<div class="chart-box">
<canvas id="chartKategori"></canvas>
</div>
</div>

</div>


<div class="table-wrap">

<div class="panel">

<h3>Detail Transaksi</h3>

<table class="table-silpj">
<thead>
<tr>
<th>No</th>
<th>Tanggal</th>
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
<td>{{ $item->uraian }}</td>
<td><span class="badge">{{ $item->kategori }}</span></td>
<td>Rp {{ number_format($item->total,0,',','.') }}</td>
</tr>
@endforeach
</tbody>

</table>

</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

new Chart(document.getElementById('chartBulanan'),{
type:'bar',
data:{
labels:@json($labelsBulan),
datasets:[{
label:'Total Belanja',
data:@json($dataBulan)
}]
},
options:{
responsive:true,
maintainAspectRatio:false
}
});


new Chart(document.getElementById('chartKategori'),{
type:'doughnut',
data:{
labels:['Barang','Jasa'],
datasets:[{
data:[
{{$summary['barang']}},
{{$summary['jasa']}}
]
}]
},
options:{
responsive:true,
maintainAspectRatio:false
}
});

</script>

</body>
</html>