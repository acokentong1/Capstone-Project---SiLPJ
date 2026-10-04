<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Belanja - SILPJ</title>
    <style>
        :root {
            --primary:#1d4ed8; --primary-dark:#1e40af; --text:#172033; --muted:#667085;
            --border:#e4e7ec; --surface:#fff; --background:#f5f7fb; --danger:#b91c1c;
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family:Arial,Helvetica,sans-serif; background:var(--background); color:var(--text); }
        button,input,select,textarea,a { font:inherit; }
        .header { background:linear-gradient(135deg,#1d4ed8,#2563eb); color:#fff; padding:22px 30px; }
        .header-inner,.container { width:min(980px,100%); margin:0 auto; }
        .header h1 { margin:0; font-size:24px; }
        .header p { margin:6px 0 0; opacity:.9; font-size:13px; }
        .container { padding:28px 22px 42px; }
        .breadcrumb { margin-bottom:14px; color:var(--muted); font-size:12px; }
        .breadcrumb a { color:var(--primary); text-decoration:none; font-weight:700; }
        .card { background:#fff; border:1px solid var(--border); border-radius:14px; box-shadow:0 3px 14px rgba(16,24,40,.05); overflow:hidden; }
        .card-head { padding:20px 22px; border-bottom:1px solid var(--border); }
        .card-head h2 { margin:0 0 6px; font-size:20px; }
        .card-head p { margin:0; color:var(--muted); font-size:13px; line-height:1.55; }
        form { padding:22px; }
        .alert { margin-bottom:18px; padding:13px 15px; border-radius:9px; font-size:13px; line-height:1.5; }
        .alert-error { border:1px solid #fecaca; background:#fef2f2; color:#991b1b; }
        .alert-warning { border:1px solid #fde68a; background:#fffbeb; color:#92400e; }
        .alert strong { display:block; margin-bottom:4px; }
        .alert ul { margin:5px 0 0; padding-left:19px; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:17px 18px; }
        .field.full { grid-column:1/-1; }
        .field label { display:block; margin-bottom:7px; font-size:13px; font-weight:800; color:#344054; }
        .field label span { color:#dc2626; }
        .field input,.field select,.field textarea { width:100%; border:1px solid #d0d5dd; border-radius:8px; padding:10px 11px; background:#fff; color:var(--text); outline:none; }
        .field input,.field select { height:42px; }
        .field textarea { resize:vertical; min-height:92px; line-height:1.5; }
        .field input:focus,.field select:focus,.field textarea:focus { border-color:#84adff; box-shadow:0 0 0 3px rgba(37,99,235,.09); }
        .field-help { margin-top:6px; color:var(--muted); font-size:11px; line-height:1.45; }
        .field-error { margin-top:6px; color:var(--danger); font-size:11px; font-weight:700; }
        .money-input { display:flex; border:1px solid #d0d5dd; border-radius:8px; overflow:hidden; background:#fff; }
        .money-input:focus-within { border-color:#84adff; box-shadow:0 0 0 3px rgba(37,99,235,.09); }
        .money-input span { display:flex; align-items:center; padding:0 11px; background:#f8fafc; color:#475467; font-size:13px; font-weight:700; border-right:1px solid #e4e7ec; }
        .money-input input { border:0; border-radius:0; box-shadow:none!important; }
        .total-preview { height:42px; display:flex; align-items:center; padding:0 12px; border-radius:8px; background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; font-weight:800; }
        .form-actions { display:flex; justify-content:flex-end; gap:9px; margin-top:24px; padding-top:18px; border-top:1px solid var(--border); }
        .btn { display:inline-flex; align-items:center; justify-content:center; min-height:40px; padding:9px 15px; border-radius:8px; border:1px solid transparent; text-decoration:none; cursor:pointer; font-size:13px; font-weight:800; }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-primary:hover { background:var(--primary-dark); }
        .btn-secondary { background:#fff; color:#344054; border-color:#d0d5dd; }
        .btn-secondary:hover { background:#f9fafb; }
        @media(max-width:650px){ .header{padding:19px 17px}.container{padding:20px 14px 32px}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.card-head,form{padding:17px}.form-actions{flex-direction:column-reverse}.btn{width:100%} }
    </style>
</head>
<body>
@include('partials.app-navigation')
    <header class="header">
        <div class="header-inner">
            <h1>Edit Data Belanja</h1>
            <p>Periksa kembali transaksi sebelum menyimpan perubahan.</p>
        </div>
    </header>

    <main class="container">
        <div class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> / <a href="{{ route('belanja.index') }}">Data Belanja</a> / Edit</div>

        <section class="card">
            <div class="card-head">
                <h2>{{ $belanja->nomor_bukti }} · {{ $belanja->uraian }}</h2>
                <p>Perubahan jumlah atau harga satuan akan menghitung ulang total secara otomatis.</p>
            </div>

            <form action="{{ route('belanja.update', $belanja->id) }}" method="POST" id="editBelanjaForm">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-error">
                        <strong>Data belum dapat disimpan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $docCount = ($belanja->notaPesanan ? 1 : 0)
                        + ($belanja->kwitansi ? 1 : 0)
                        + ($belanja->bapb ? 1 : 0);
                @endphp

                @if($docCount > 0)
                    <div class="alert alert-warning">
                        <strong>Transaksi ini sudah memiliki {{ $docCount }} dokumen LPJ.</strong>
                        Perubahan uraian, jumlah, harga, atau tanggal dapat ikut memengaruhi informasi yang ditampilkan pada dokumen terkait. Pastikan perubahan memang diperlukan.
                    </div>
                @endif

                @include('belanja._form', ['belanja' => $belanja])
            </form>
        </section>
    </main>

    @if($docCount > 0)
        <script>
            document.getElementById('editBelanjaForm')?.addEventListener('submit', function (event) {
                if (!confirm('Transaksi ini sudah memiliki dokumen LPJ. Simpan perubahan pada data belanja?')) {
                    event.preventDefault();
                }
            });
        </script>
    @endif
</body>
</html>
