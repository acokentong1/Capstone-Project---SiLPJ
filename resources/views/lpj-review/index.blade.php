@extends ('lpj-review.layout')
@section ('title', 'Pemeriksaan LPJ')
@section ('content')
    <div class="hero">
        <h1>Pemeriksaan LPJ</h1>
        <p class="muted">Siapkan bukti, ajukan untuk diperiksa, dan pantau persetujuan setiap transaksi.</p>
    </div>
    <div class="grid">
        @foreach ([
                'draf' => 'Draf',
                'diajukan' => 'Menunggu pemeriksaan',
                'revisi' => 'Perlu revisi',
                'disetujui' => 'Disetujui'
            ]
            as $key => $label)
            <a
                class="metric"
                style="text-decoration: none; color: inherit"
                href="{{ route('lpj-review.index', ['status'=>$key]) }}"
                >{{ $label }}<strong>{{
                    $counts[$key] ??
                        0
                }}</strong>
            </a>
        @endforeach
    </div>
    <section class="card">
        <form
            class="filters"
            method="GET"
            action="{{ route('lpj-review.index') }}"
        >
            <div>
                <label for="q">Cari transaksi</label>
                <input
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Nomor bukti atau uraian"
                />
            </div>
            <div>
                <label for="status">Status pemeriksaan</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach ([
                            'draf' => 'Draf',
                            'diajukan' => 'Diajukan',
                            'revisi' => 'Perlu revisi',
                            'disetujui' => 'Disetujui'
                        ]
                        as $key => $label)
                        <option
                            value="{{ $key }}"
                            @selected (request('status') === $key)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button>Tampilkan</button>
            <a class="btn secondary" href="{{ route('lpj-review.index') }}"
                >Reset</a
            >
        </form>
    </section>
    <section class="card scroll">
        <table>
            <thead>
                <tr>
                    <th>Transaksi</th>
                    <th>Tahun</th>
                    <th>Total</th>
                    <th>Pemeriksaan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->nomor_bukti }}</strong>
                            <br />{{ $item->uraian }}<br />
                            <small
                                >{{ $item->attachments_count }} lampiran</small
                            >
                        </td>
                        <td>{{ $item->tahun_anggaran }}</td>
                        <td>
                            Rp {{
                                number_format(
                                    $item->total,
                                    0,
                                    ',',
                                    '.',
                                )
                            }}
                        </td>
                        <td>
                            <span
                                class="status {{ $item->review_status }}"
                                >{{ $item->reviewLabel() }}</span
                            >
                        </td>
                        <td>
                            <a
                                class="btn secondary"
                                href="{{ route('lpj-review.show',$item->id) }}"
                                >Buka detail</a
                            >
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Belum ada transaksi yang sesuai. Pastikan akun Anda
                            sudah terhubung ke sekolah.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $items->links() }}</div>
    </section>
    <section class="card">
        <h2>Aktivitas pemeriksaan terbaru</h2>
        @forelse ($events as $event)
            <div class="event">
                <strong>{{ $event->actor_name }}</strong> · {{
                    str_replace(
                        '_',
                        ' ',
                        $event->action,
                    )
                }}<br />
                <small
                    >{{
                        $event->created_at->format(
                            'd-m-Y H:i',
                        )
                    }}
                    @if ($event->belanja_id)
                        ·
                        <a
                            href="{{ route('lpj-review.show',$event->belanja_id) }}"
                            >Transaksi #{{ $event->belanja_id }}</a
                        >
                    @else
                        · Transaksi telah dihapus
                    @endif
                </small>
                @if ($event->reason)
                    <p>{{ $event->reason }}</p>
                @endif
                @if ($event->details)
                    <details>
                        <summary>Rincian perubahan</summary>
                        <pre>{{
                            json_encode(
                                $event->details,
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
                            )
                        }}</pre>
                    </details>
                @endif
            </div>
        @empty
            <p class="muted">Belum ada aktivitas pemeriksaan.</p>
        @endforelse
    </section>
@endsection
