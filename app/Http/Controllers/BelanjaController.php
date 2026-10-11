<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\Anggaran;
use App\Models\NotaPesanan;
use App\Models\Kwitansi;
use App\Models\Bapb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolProfile;
use App\Support\Terbilang;
use App\Services\LpjWorkflow;

class BelanjaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'kategori' => ['nullable', 'in:Barang,Jasa'],
            'status' => ['nullable', 'in:lengkap,proses,belum'],
            'tahun' => ['nullable', 'integer', 'between:1900,9999'],
            'tahun_anggaran' => ['nullable', 'integer', 'between:1900,9999'],
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $query = Belanja::where(
            'school_profile_id',
            $user->school_profile_id ?? 0,
        );
        if ($request->filled('q')) {
            $query->where(
                fn($q) => $q
                    ->where('nomor_bukti', 'like', '%' . $request->q . '%')
                    ->orWhere('uraian', 'like', '%' . $request->q . '%'),
            );
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if (
            $year =
                $request->input('tahun') ?: $request->input('tahun_anggaran')
        ) {
            $query->where('tahun_anggaran', $year);
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        if ($request->status === 'lengkap') {
            $query->has('notaPesanan')->has('kwitansi')->has('bapb');
        } elseif ($request->status === 'belum') {
            $query
                ->doesntHave('notaPesanan')
                ->doesntHave('kwitansi')
                ->doesntHave('bapb');
        } elseif ($request->status === 'proses') {
            $query
                ->where(
                    fn($q) => $q
                        ->has('notaPesanan')
                        ->orHas('kwitansi')
                        ->orHas('bapb'),
                )
                ->where(
                    fn($q) => $q
                        ->doesntHave('notaPesanan')
                        ->orDoesntHave('kwitansi')
                        ->orDoesntHave('bapb'),
                );
        }

        $belanjas = $query
            ->with(['notaPesanan', 'kwitansi', 'bapb'])
            ->latest('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // total transaksi seluruh data
        $totalTransaksi = (clone $query)->count();

        // total nilai belanja seluruh data
        $totalBelanja = (clone $query)->sum('total');

        // total untuk tampilan filter
        $filteredTotal = $totalBelanja;

        $lpjLengkap = (clone $query)
            ->has('notaPesanan')
            ->has('kwitansi')
            ->has('bapb')
            ->count();
        $lpjBelumLengkap = $totalTransaksi - $lpjLengkap;
        $jumlahNota = (clone $query)->has('notaPesanan')->count();
        $jumlahKwitansi = (clone $query)->has('kwitansi')->count();
        $jumlahBapb = (clone $query)->has('bapb')->count();

        // daftar tahun anggaran untuk filter
        $tahunAnggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id ?? 0,
        )
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->unique()
            ->values();

        $filters = [
            'q' => request('q', ''),
            'kategori' => request('kategori', ''),
            'tahun' => request('tahun', ''),
            'status' => request('status', ''),
            'tanggal_mulai' => request('tanggal_mulai', ''),
            'tanggal_selesai' => request('tanggal_selesai', ''),
        ];

        $hasFilters =
            !empty(request('q')) ||
            !empty(request('kategori')) ||
            !empty(request('tahun')) ||
            !empty(request('tahun_anggaran')) ||
            !empty(request('status')) ||
            !empty(request('tanggal_mulai')) ||
            !empty(request('tanggal_selesai'));

        $tahunAnggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id ?? 0,
        )
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->unique()
            ->values();

        return view(
            'belanja.index',
            compact(
                'belanjas',
                'totalTransaksi',
                'totalBelanja',
                'filteredTotal',
                'lpjLengkap',
                'lpjBelumLengkap',
                'jumlahNota',
                'jumlahKwitansi',
                'jumlahBapb',
                'tahunAnggaran',
                'filters',
                'hasFilters',
            ),
        );
    }

    public function create()
    {
        $user = Auth::user();

        $tahunAnggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id ?? 0,
        )
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->unique()
            ->values();

        return view('belanja.create', compact('tahunAnggaran'));
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            SchoolProfile::whereKey(Auth::user()->school_profile_id)
                ->lockForUpdate()
                ->firstOrFail();
            $validated = $request->validate([
                'tahun_anggaran' => 'required|integer|between:1900,9999',

                'tanggal' => 'required|date',

                'nomor_bukti' => 'required|string|max:255',

                'uraian' => 'required|string|max:255',

                'kategori' => ['required', Rule::in(['Barang', 'Jasa'])],

                'jumlah' => 'required|integer|min:1|max:1000000',

                'harga_satuan' => 'required|integer|min:0|max:1000000000',
            ]);

            $user = Auth::user();

            // cek sisa anggaran tahun berjalan

            $anggaran = Anggaran::where(
                'school_profile_id',
                $user->school_profile_id ?? 0,
            )
                ->where('tahun', $validated['tahun_anggaran'])
                ->first();

            if (!$anggaran) {
                return back()
                    ->withInput()
                    ->with('error', 'Tahun anggaran belum tersedia.');
            }

            $totalTerpakai = Belanja::where(
                'school_profile_id',
                $user->school_profile_id ?? 0,
            )
                ->where('tahun_anggaran', $validated['tahun_anggaran'])
                ->sum('total');

            $totalBaru = $validated['jumlah'] * $validated['harga_satuan'];
            if ($totalBaru > 2147483647) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'harga_satuan' =>
                        'Total transaksi melebihi batas kolom database (Rp 2.147.483.647).',
                ]);
            }

            $sisaAnggaran = $anggaran->jumlah - $totalTerpakai;

            if ($totalBaru > $sisaAnggaran) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Belanja melebihi sisa anggaran. Sisa tersedia Rp ' .
                            number_format($sisaAnggaran, 0, ',', '.'),
                    );
            }

            Belanja::create([
                'user_id' => $user->id,

                'school_profile_id' => $user->school_profile_id ?? 0,

                'tanggal' => $validated['tanggal'],

                'tahun_anggaran' => $validated['tahun_anggaran'],

                'nomor_bukti' => $validated['nomor_bukti'],

                'uraian' => $validated['uraian'],

                'kategori' => $validated['kategori'],

                'jumlah' => $validated['jumlah'],

                'harga_satuan' => $validated['harga_satuan'],

                'total' => $validated['jumlah'] * $validated['harga_satuan'],
            ]);

            return redirect()
                ->route('belanja.index')
                ->with('success', 'Data belanja berhasil disimpan');
        });
    }

    public function edit($id)
    {
        $user = Auth::user();

        $belanja = Belanja::where(
            'school_profile_id',
            $user->school_profile_id ?? 0,
        )->findOrFail($id);

        LpjWorkflow::assertEditable($belanja);

        $tahunAnggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id ?? 0,
        )
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->unique()
            ->values();

        return view('belanja.edit', compact('belanja', 'tahunAnggaran'));
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            SchoolProfile::whereKey(Auth::user()->school_profile_id)
                ->lockForUpdate()
                ->firstOrFail();
            $user = Auth::user();

            $validated = $request->validate([
                'tahun_anggaran' => 'required|integer|between:1900,9999',

                'tanggal' => 'required|date',

                'nomor_bukti' => 'required|string|max:255',

                'uraian' => 'required|string|max:255',
                'reason' => 'required|string|min:5|max:2000',

                'kategori' => ['required', Rule::in(['Barang', 'Jasa'])],

                'jumlah' => 'required|integer|min:1|max:1000000',

                'harga_satuan' => 'required|integer|min:0|max:1000000000',
            ]);

            $belanja = Belanja::where(
                'school_profile_id',
                $user->school_profile_id ?? 0,
            )
                ->lockForUpdate()
                ->findOrFail($id);
            LpjWorkflow::assertEditable($belanja);
            $before = $belanja->only([
                'tanggal',
                'tahun_anggaran',
                'nomor_bukti',
                'uraian',
                'kategori',
                'jumlah',
                'harga_satuan',
                'total',
            ]);

            $anggaran = Anggaran::where(
                'school_profile_id',
                $user->school_profile_id ?? 0,
            )
                ->where('tahun', $validated['tahun_anggaran'])
                ->first();

            if (!$anggaran) {
                return back()
                    ->withInput()
                    ->with('error', 'Tahun anggaran belum tersedia.');
            }

            // total belanja lain (tidak menghitung data yang sedang diedit)

            $totalTerpakai = Belanja::where(
                'school_profile_id',
                $user->school_profile_id ?? 0,
            )
                ->where('tahun_anggaran', $validated['tahun_anggaran'])
                ->where('id', '!=', $id)
                ->sum('total');

            $totalBaru = $validated['jumlah'] * $validated['harga_satuan'];
            if ($totalBaru > 2147483647) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'harga_satuan' =>
                        'Total transaksi melebihi batas kolom database (Rp 2.147.483.647).',
                ]);
            }

            $sisaAnggaran = $anggaran->jumlah - $totalTerpakai;

            if ($totalBaru > $sisaAnggaran) {
                return back()
                    ->withInput()
                    ->with('error', 'Perubahan melebihi sisa anggaran.');
            }

            $belanja->update([
                'tanggal' => $validated['tanggal'],

                'tahun_anggaran' => $validated['tahun_anggaran'],

                'nomor_bukti' => $validated['nomor_bukti'],

                'uraian' => $validated['uraian'],

                'kategori' => $validated['kategori'],

                'jumlah' => $validated['jumlah'],

                'harga_satuan' => $validated['harga_satuan'],

                'total' => $validated['jumlah'] * $validated['harga_satuan'],
            ]);

            // Kwitansi harus tetap sesuai nilai transaksi yang baru.
            if ($kwitansi = $belanja->kwitansi) {
                $kwitansi->update([
                    'jumlah_uang' => $belanja->total,
                    'terbilang' => Terbilang::rupiah((int) $belanja->total),
                ]);
            }

            if ($nota = $belanja->notaPesanan) {
                $nota->update(['tanggal_nota' => $belanja->tanggal]);
            }
            LpjWorkflow::record(
                $belanja,
                'ubah_belanja',
                $validated['reason'],
                [
                    'before' => $before,
                    'after' => $belanja->only(array_keys($before)),
                ],
            );
            return redirect()
                ->route('belanja.index')
                ->with('success', 'Data belanja berhasil diperbarui');
        });
    }

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        return DB::transaction(function () use ($request, $id) {
            $belanja = Belanja::where(
                'school_profile_id',
                $request->user()->school_profile_id ?? 0,
            )
                ->lockForUpdate()
                ->findOrFail($id);
            LpjWorkflow::assertEditable($belanja);
            if (
                $belanja->notaPesanan()->exists() ||
                $belanja->kwitansi()->exists() ||
                $belanja->bapb()->exists()
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'lpj' =>
                        'Transaksi yang memiliki dokumen LPJ tidak dapat dihapus.',
                ]);
            }
            LpjWorkflow::record($belanja, 'hapus_belanja', $request->reason, [
                'before' => $belanja->only([
                    'nomor_bukti',
                    'uraian',
                    'total',
                    'tanggal',
                    'tahun_anggaran',
                ]),
            ]);
            $belanja->delete();
            return redirect()
                ->route('belanja.index')
                ->with('success', 'Data belanja berhasil dihapus.');
        });
    }
}
