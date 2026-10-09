<section class="budget-section">

<div class="budget-header">
    <div>
        <h2 class="budget-title">Ringkasan Anggaran</h2>
        <p class="budget-subtitle">Informasi pagu dan penggunaan anggaran tahun berjalan</p>
    </div>

    <a href="{{ route('anggaran.create') }}" class="add-budget-btn">
        + Tambah Anggaran
    </a>
</div>

    <form method="GET" action="{{ route('dashboard') }}" class="year-filter">
        <label>Tahun Anggaran</label>
        <select name="tahun" onchange="this.form.submit()">
            @foreach(\App\Models\Anggaran::where('user_id',auth()->id())->orderByDesc('tahun')->pluck('tahun') as $tahun)
                <option value="{{ $tahun }}" {{ ($tahunAnggaran ?? date('Y')) == $tahun ? 'selected' : '' }}>
                    {{ $tahun }}
                </option>
            @endforeach
        </select>
    </form>

@if(($paguAnggaran ?? 0) == 0)
<div class="empty-budget">
    <strong>Belum ada data anggaran</strong>
    <p>Silakan tambahkan pagu anggaran terlebih dahulu agar ringkasan dapat dihitung.</p>
    <a href="{{ route('anggaran.create') }}" class="add-budget-btn">
        Tambah Anggaran
    </a>
</div>
@else

<div class="budget-grid">
    <div class="budget-card">
        <div class="budget-label">Total Pagu Anggaran</div>
        <div class="budget-value">Rp {{ number_format($paguAnggaran,0,',','.') }}</div>
        <div class="budget-desc">Total dana tersedia</div>
    </div>

    <div class="budget-card">
        <div class="budget-label">Realisasi Belanja</div>
        <div class="budget-value">Rp {{ number_format($realisasiAnggaran,0,',','.') }}</div>
        <div class="budget-desc">Dana yang telah digunakan</div>
    </div>

    <div class="budget-card">
        <div class="budget-label">Sisa Anggaran</div>
        <div class="budget-value">Rp {{ number_format($sisaAnggaran,0,',','.') }}</div>
        <div class="budget-desc">Dana yang belum digunakan</div>
    </div>

    <div class="budget-card">
        <div class="budget-label">Serapan Anggaran</div>
        <div class="budget-value">{{ $serapanAnggaran }}%</div>
        <div class="progress">
            <div class="progress-bar" style="width:{{ $serapanAnggaran }}%"></div>
        </div>
        <div class="budget-desc">Persentase penggunaan</div>
    </div>
</div>
@endif

</section>

<style>
.budget-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.budget-title{font-size:22px;font-weight:800;color:#172033;margin:0}
.budget-subtitle{color:#64748b;font-size:13px}
.year-filter{display:flex;align-items:center;gap:10px;margin-bottom:15px}.year-filter label{font-weight:700;color:#475569}.year-filter select{border:1px solid #d1d5db;padding:8px 12px;border-radius:8px;background:white}.add-budget-btn{background:#2563eb;color:white;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:700;font-size:14px}
.budget-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.budget-card,.empty-budget{background:white;border:1px solid #e5e7eb;border-radius:14px;padding:20px;box-shadow:0 3px 10px rgba(0,0,0,.04)}
.budget-label{font-weight:700;color:#64748b;font-size:14px}
.budget-value{font-size:26px;font-weight:800;margin:12px 0;color:#172033}
.budget-desc{font-size:13px;color:#64748b}
.progress{height:8px;background:#e5e7eb;border-radius:10px;overflow:hidden}
.progress-bar{height:100%;background:#2563eb}
.empty-budget{text-align:center}
@media(max-width:900px){.budget-grid{grid-template-columns:1fr}.budget-header{display:block}}
</style>
