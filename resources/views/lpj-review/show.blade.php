@extends('lpj-review.layout')
@section('title', 'Detail pemeriksaan LPJ')
@section('content')
@php
$canEdit = auth()->user()->isBendahara() && $item->isEditable();
$canReview = auth()->user()->isKepalaSekolah();
$canApprove = $canReview && (int)$item->submitted_by !== (int)auth()->id() && (int)$item->user_id !== (int)auth()->id();
@endphp
<a href="{{ route('lpj-review.index') }}">← Semua pemeriksaan</a>
<div class="hero">
<h1 style="margin-top:14px">{{ $item->nomor_bukti ?: 'Transaksi #'.$item->id }}</h1>
<p>{{ $item->uraian }}</p>
<span class="status {{ $item->review_status }}">{{ $item->reviewLabel() }}</span>
</div>
<div class="split">
<div>
<section class="card">
<h2>Rincian transaksi</h2>
<dl>
<dt>Tanggal</dt>
<dd>{{ $item->tanggal }}</dd>
<dt>Tahun anggaran</dt>
<dd>{{ $item->tahun_anggaran }}</dd>
<dt>Kategori</dt>
<dd>{{ $item->kategori }}</dd>
<dt>Perhitungan</dt>
<dd>{{ $item->jumlah }} × Rp {{ number_format($item->harga_satuan,0,',','.') }}</dd>
<dt>Total</dt>
<dd>
<strong>Rp {{ number_format($item->total,0,',','.') }}</strong>
</dd>
</dl>
@if($canEdit)<a class="btn secondary" href="{{ route('belanja.edit',$item->id) }}">Edit transaksi</a>
@else
<p class="muted">Transaksi yang diajukan atau disetujui terkunci. Perubahan memerlukan pengembalian untuk revisi.</p>
@endif
<h2 style="margin-top:24px">Dokumen LPJ</h2>
<div class="actions">
@foreach(['notaPesanan'=>['Nota Pesanan','nota-pesanan'], 'kwitansi'=>['Kwitansi','kwitansi'], 'bapb'=>['BAPB','bapb']] as $relation=>$document)
@if($item->$relation)<a class="btn secondary" href="{{ route($document[1].'.show',$item->$relation->id) }}">{{ $document[0] }} ✓</a>
@elseif($canEdit)<a class="btn" href="{{ route($document[1].'.create',$item->id) }}">Buat {{ $document[0] }}</a>
@else
<span class="status">{{ $document[0] }} belum tersedia</span>
@endif
@endforeach
</div>
</section>
<section class="card">
<h2>Bukti transaksi</h2>
@forelse($attachments as $file)<div class="file">
<a href="{{ route('lpj-review.download',$file->id) }}">{{ $file->filename }}</a>
<br>
<small>{{ number_format($file->size/1024,1) }} KB · {{ $file->created_at->format('d-m-Y H:i') }}</small>
@if($canEdit)<details>
<summary>Hapus lampiran</summary>
<form class="inline-form" method="POST" action="{{ route('lpj-review.remove',[$item->id,$file->id]) }}">
@csrf 
@method('DELETE')<label for="reason-{{ $file->id }}">Alasan penghapusan</label>
<input id="reason-{{ $file->id }}" name="reason" required minlength="5" maxlength="2000">
<button class="danger" style="margin-top:10px">Hapus lampiran ini</button>
</form>
</details>
@endif
</div>
@empty
<p class="muted">Belum ada bukti. Lampirkan minimal satu berkas sebelum mengajukan.</p>
@endforelse
@if($canEdit)<form class="inline-form" action="{{ route('lpj-review.upload',$item->id) }}" method="POST" enctype="multipart/form-data">
@csrf
<label for="bukti">Tambah bukti</label>
<input id="bukti" type="file" name="bukti" accept=".pdf,.jpg,.jpeg,.png" required>
<p class="muted">PDF, JPG, atau PNG. Maksimal 2 MB per berkas dan 5 berkas per transaksi.</p>
<button>Unggah bukti</button>
</form>
@endif
</section>
</div>
<div>
<section class="card">
<h2>Tindakan pemeriksaan</h2>
@if($canEdit)<p>Pastikan ketiga dokumen dan bukti transaksi sudah benar. Setelah diajukan, data dikunci.</p>
<form method="POST" action="{{ route('lpj-review.transition',$item->id) }}">
@csrf
<input type="hidden" name="action" value="ajukan">
<button>Ajukan pemeriksaan</button>
</form>
@elseif($item->review_status==='diajukan')<p>Menunggu pemeriksa. Persetujuan dilakukan akun berbeda dari pembuat dan pengaju.</p>
@endif
@if($canApprove && $item->review_status==='diajukan')<form class="inline-form" method="POST" action="{{ route('lpj-review.transition',$item->id) }}">
@csrf
<input type="hidden" name="action" value="setujui">
<button>Setujui LPJ</button>
</form>
@endif
@if($canReview && in_array($item->review_status,['diajukan','disetujui']))<form class="inline-form" method="POST" action="{{ route('lpj-review.transition',$item->id) }}">
@csrf
<input type="hidden" name="action" value="{{ $item->review_status==='disetujui'?'buka':'revisi' }}">
<label for="reason">Catatan untuk bendahara</label>
<textarea id="reason" name="reason" required minlength="5" maxlength="2000" rows="4" placeholder="Jelaskan bagian yang perlu diperbaiki.">{{ old('reason') }}</textarea>
<button class="danger" style="margin-top:12px">{{ $item->review_status==='disetujui'?'Buka kembali untuk revisi':'Kembalikan untuk revisi' }}</button>
</form>
@endif
@if($item->review_status==='disetujui')<p class="ok">LPJ telah disetujui. Data dan bukti terkunci.</p>
@endif
</section>
<section class="card">
<h2>Riwayat perubahan</h2>
@forelse($events as $event)<div class="event">
<strong>{{ $event->actor_name }}</strong>
<p>{{ str_replace('_',' ',$event->action) }} 
@if($event->from_status) · {{ $event->from_status }} → {{ $event->to_status }}
@endif
</p>
<small>{{ $event->created_at->format('d-m-Y H:i') }}</small>
@if($event->reason)<p>{{ $event->reason }}</p>
@endif 
@if($event->details)<details>
<summary>Lihat rincian</summary>
<pre>{{ json_encode($event->details,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
</details>
@endif
</div>
@empty
<p class="muted">Belum ada perubahan tercatat.</p>
@endforelse
</section>
</div>
</div>
@endsection
