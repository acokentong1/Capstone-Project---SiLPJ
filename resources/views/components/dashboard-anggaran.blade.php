<div class="summary-grid">

<div class="panel">
<h3>Total Pagu Anggaran</h3>
<div class="value">
Rp {{ number_format($paguAnggaran ?? 0,0,',','.') }}
</div>
<p>Anggaran tersedia tahun berjalan</p>
</div>


<div class="panel">
<h3>Realisasi Belanja</h3>
<div class="value">
Rp {{ number_format($realisasiAnggaran ?? 0,0,',','.') }}
</div>
<p>Total transaksi yang digunakan</p>
</div>


<div class="panel">
<h3>Sisa Anggaran</h3>
<div class="value">
Rp {{ number_format(($paguAnggaran ?? 0)-($realisasiAnggaran ?? 0),0,',','.') }}
</div>
<p>Sisa dana tersedia</p>
</div>


<div class="panel">
<h3>Serapan Anggaran</h3>
<div class="value">
{{ ($paguAnggaran ?? 0) > 0 ? number_format((($realisasiAnggaran ?? 0)/$paguAnggaran)*100,2) : 0 }}%
</div>
<p>Persentase penggunaan</p>
</div>

</div>