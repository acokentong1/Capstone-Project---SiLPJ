<div class="row g-3 mt-4">
    <div class="col-md-6">
        <div class="card p-3">
            <h5>Pengeluaran Bulanan</h5>
            <canvas id="chartBulanan"></canvas>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h5>Komposisi Barang dan Jasa</h5>
            <canvas id="chartKategori"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('chartBulanan'), {
    type: 'bar',
    data: {
        labels: @json($labelsBulan ?? []),
        datasets: [{
            label: 'Total Belanja',
            data: @json($dataBulan ?? [])
        }]
    }
});

new Chart(document.getElementById('chartKategori'), {
    type: 'doughnut',
    data: {
        labels: ['Barang','Jasa'],
        datasets: [{
            data: [
                {{ $summary['barang'] ?? 0 }},
                {{ $summary['jasa'] ?? 0 }}
            ]
        }]
    }
});
</script>