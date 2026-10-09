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

class BelanjaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Belanja::where('school_profile_id', $user->school_profile_id);

        // filter pencarian
        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where(
                    'nomor_bukti',
                    'like',
                    '%' . $request->q . '%',
                )->orWhere('uraian', 'like', '%' . $request->q . '%');
            });
        }

        // filter kategori
        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        // FILTER TAHUN ANGGARAN
        if ($request->tahun_anggaran) {
            $query->where('tahun_anggaran', $request->tahun_anggaran);
        }

        // filter tanggal
        if ($request->tanggal_mulai) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->tanggal_selesai) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        $query = Belanja::where('school_profile_id', $user->school_profile_id);

        if (request('q')) {
            $query->where(function ($q) {
                $q->where(
                    'nomor_bukti',
                    'like',
                    '%' . request('q') . '%',
                )->orWhere('uraian', 'like', '%' . request('q') . '%');
            });
        }

        if (request('kategori')) {
            $query->where('kategori', request('kategori'));
        }

        if (request('tahun')) {
            $query->where('tahun_anggaran', request('tahun'));
        }

        $belanjas = $query->latest()->paginate(10)->withQueryString();

        // total transaksi seluruh data
        $totalTransaksi = (clone $query)->count();

        // total nilai belanja seluruh data
        $totalBelanja = (clone $query)->sum('total');

        // total untuk tampilan filter
        $filteredTotal = $totalBelanja;

        // hitung LPJ
        $lpjLengkap = 0;
        $lpjBelumLengkap = 0;

        foreach ($belanjas as $belanja) {
            $lengkap =
                \App\Models\NotaPesanan::where(
                    'belanja_id',
                    $belanja->id,
                )->exists() &&
                \App\Models\Kwitansi::where(
                    'belanja_id',
                    $belanja->id,
                )->exists() &&
                \App\Models\Bapb::where('belanja_id', $belanja->id)->exists();

            if ($lengkap) {
                $lpjLengkap++;
            } else {
                $lpjBelumLengkap++;
            }
        }

        $jumlahNota = \App\Models\NotaPesanan::whereIn(
            'belanja_id',
            $belanjas->getCollection()->pluck('id'),
        )->count();

        $jumlahKwitansi = \App\Models\Kwitansi::whereIn(
            'belanja_id',
            $belanjas->getCollection()->pluck('id'),
        )->count();

        $jumlahBapb = \App\Models\Bapb::whereIn(
            'belanja_id',
            $belanjas->getCollection()->pluck('id'),
        )->count();

        // daftar tahun anggaran untuk filter
        $tahunAnggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id,
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
            !empty(request('tahun_anggaran')) ||
            !empty(request('status')) ||
            !empty(request('tanggal_mulai')) ||
            !empty(request('tanggal_selesai'));

        $tahunAnggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id,
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
            $user->school_profile_id,
        )
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->unique()
            ->values();

        return view('belanja.create', compact('tahunAnggaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_anggaran' => 'required',

            'tanggal' => 'required|date',

            'nomor_bukti' => 'required',

            'uraian' => 'required',

            'kategori' => ['required', Rule::in(['Barang', 'Jasa'])],

            'jumlah' => 'required|integer|min:1',

            'harga_satuan' => 'required|integer|min:0',
        ]);

        $user = Auth::user();

        // cek sisa anggaran tahun berjalan

        $anggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id,
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
            $user->school_profile_id,
        )
            ->where('tahun_anggaran', $validated['tahun_anggaran'])
            ->sum('total');

        $totalBaru = $validated['jumlah'] * $validated['harga_satuan'];

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

            'school_profile_id' => $user->school_profile_id,

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
    }

    public function edit($id)
    {
        $user = Auth::user();

        $belanja = Belanja::where(
            'school_profile_id',
            $user->school_profile_id,
        )->findOrFail($id);

        $tahunAnggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id,
        )
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->unique()
            ->values();

        return view('belanja.edit', compact('belanja', 'tahunAnggaran'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'tahun_anggaran' => 'required',

            'tanggal' => 'required|date',

            'nomor_bukti' => 'required',

            'uraian' => 'required',

            'kategori' => ['required', Rule::in(['Barang', 'Jasa'])],

            'jumlah' => 'required|integer|min:1',

            'harga_satuan' => 'required|integer|min:0',
        ]);

        $belanja = Belanja::where(
            'school_profile_id',
            $user->school_profile_id,
        )->findOrFail($id);

        $anggaran = Anggaran::where(
            'school_profile_id',
            $user->school_profile_id,
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
            $user->school_profile_id,
        )
            ->where('tahun_anggaran', $validated['tahun_anggaran'])
            ->where('id', '!=', $id)
            ->sum('total');

        $totalBaru = $validated['jumlah'] * $validated['harga_satuan'];

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

        return redirect()
            ->route('belanja.index')
            ->with('success', 'Data belanja berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $belanja = Belanja::where(
            'school_profile_id',
            $user->school_profile_id,
        )->findOrFail($id);

        $belanja->delete();

        return redirect()
            ->route('belanja.index')
            ->with('success', 'Data belanja berhasil dihapus');
    }
}
