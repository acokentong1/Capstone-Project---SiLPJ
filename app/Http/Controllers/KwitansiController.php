<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\Kwitansi;
use App\Models\SchoolProfile;
use App\Support\Terbilang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KwitansiController extends Controller
{
    public function create($id)
    {
        $belanja = Belanja::where('school_profile_id', auth()->user()->school_profile_id)
            ->findOrFail($id);

        $kwitansiAda = Kwitansi::where('belanja_id', $belanja->id)->first();

        if ($kwitansiAda) {
            return redirect()->route('kwitansi.show', $kwitansiAda->id);
        }

        $school = SchoolProfile::where('user_id', auth()->id())->first();

        if (!$school) {
            return redirect()
                ->route('school-profile.create')
                ->with('error', 'Lengkapi profil sekolah sebelum membuat Kwitansi.');
        }

        $nomorKwitansi = $this->generateNomorKwitansi($belanja);
        $terbilang = Terbilang::rupiah((int) $belanja->total);

        return view('kwitansi.create', compact('belanja', 'nomorKwitansi', 'terbilang', 'school'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'belanja_id' => ['required', 'integer'],
            'penerima' => ['required', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($validated) {
            $belanja = Belanja::where('school_profile_id', auth()->user()->school_profile_id)
                ->lockForUpdate()
                ->findOrFail($validated['belanja_id']);

            if (!SchoolProfile::where('user_id', auth()->id())->exists()) {
                return redirect()
                    ->route('school-profile.create')
                    ->with('error', 'Lengkapi profil sekolah sebelum membuat Kwitansi.');
            }

            $kwitansiAda = Kwitansi::where('belanja_id', $belanja->id)->first();

            if ($kwitansiAda) {
                return redirect()->route('kwitansi.show', $kwitansiAda->id);
            }

            $kwitansi = Kwitansi::create([
                'belanja_id' => $belanja->id,
                'nomor_kwitansi' => $this->generateNomorKwitansi($belanja),
                'tanggal_kwitansi' => now()->toDateString(),
                'penerima' => $validated['penerima'],
                'jumlah_uang' => $belanja->total,
                'terbilang' => Terbilang::rupiah((int) $belanja->total),
            ]);

            return redirect()
                ->route('kwitansi.show', $kwitansi->id)
                ->with('success', 'Kwitansi berhasil dibuat.');
        });
    }

    public function index(Request $request)
    {
        $query = Kwitansi::with(['belanja.notaPesanan', 'belanja.bapb'])
            ->whereHas('belanja', function ($query) {
                $query->where('user_id', auth()->id());
            });

        if ($request->filled('q')) {
            $search = trim((string) $request->q);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kwitansi', 'like', "%{$search}%")
                    ->orWhere('penerima', 'like', "%{$search}%")
                    ->orWhereHas('belanja', function ($belanjaQuery) use ($search) {
                        $belanjaQuery->where('uraian', 'like', "%{$search}%")
                            ->orWhere('nomor_bukti', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_kwitansi', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_kwitansi', '<=', $request->tanggal_sampai);
        }

        $kwitansi = $query
            ->orderByDesc('tanggal_kwitansi')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $base = Kwitansi::whereHas('belanja', function ($query) {
            $query->where('user_id', auth()->id());
        });

        $totalKwitansi = (clone $base)->count();
        $totalNilai = (clone $base)->sum('jumlah_uang');
        $kwitansiLengkap = Belanja::where('school_profile_id', auth()->user()->school_profile_id)
            ->whereHas('notaPesanan')
            ->whereHas('kwitansi')
            ->whereHas('bapb')
            ->count();

        return view('kwitansi.index', compact('kwitansi', 'totalKwitansi', 'totalNilai', 'kwitansiLengkap'));
    }

    public function show($id)
    {
        $kwitansi = Kwitansi::with([
            'belanja.notaPesanan',
            'belanja.kwitansi',
            'belanja.bapb',
        ])
            ->whereHas('belanja', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        $school = SchoolProfile::where('user_id', auth()->id())->first();

        return view('kwitansi.show', compact('kwitansi', 'school'));
    }

    private function generateNomorKwitansi(Belanja $belanja): string
    {
        return 'KW-' . str_pad((string) $belanja->id, 3, '0', STR_PAD_LEFT);
    }
}
