<?php

namespace App\Http\Controllers;

use App\Models\Bapb;
use App\Models\Belanja;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BapbController extends Controller
{
    public function index(Request $request)
    {
        $query = Bapb::with(['belanja.notaPesanan', 'belanja.kwitansi'])
            ->where('user_id', auth()->id());

        if ($request->filled('q')) {
            $search = trim((string) $request->q);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_bapb', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('belanja', function ($belanjaQuery) use ($search) {
                        $belanjaQuery->where('uraian', 'like', "%{$search}%")
                            ->orWhere('nomor_bukti', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('hasil')) {
            $request->validate([
                'hasil' => [Rule::in(['Baik dan sesuai', 'Baik, terdapat catatan', 'Tidak sesuai'])],
            ]);
            $query->where('hasil_pemeriksaan', $request->hasil);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_bapb', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_bapb', '<=', $request->tanggal_sampai);
        }

        $bapbs = $query
            ->orderByDesc('tanggal_bapb')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $base = Bapb::where('user_id', auth()->id());
        $totalBapb = (clone $base)->count();
        $sesuai = (clone $base)->where('hasil_pemeriksaan', 'Baik dan sesuai')->count();
        $denganCatatan = (clone $base)->where('hasil_pemeriksaan', 'Baik, terdapat catatan')->count();
        $tidakSesuai = (clone $base)->where('hasil_pemeriksaan', 'Tidak sesuai')->count();

        return view('bapb.index', compact('bapbs', 'totalBapb', 'sesuai', 'denganCatatan', 'tidakSesuai'));
    }

    public function create($id)
    {
        $belanja = Belanja::where('user_id', auth()->id())
            ->findOrFail($id);

        $bapbAda = Bapb::where('belanja_id', $belanja->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($bapbAda) {
            return redirect()->route('bapb.show', $bapbAda->id);
        }

        $school = SchoolProfile::where('user_id', auth()->id())->first();

        if (!$school) {
            return redirect()
                ->route('school-profile.create')
                ->with('error', 'Lengkapi profil sekolah sebelum membuat BAPB.');
        }

        $nomorBapb = $this->nextNomorBapb();

        return view('bapb.create', compact('belanja', 'nomorBapb', 'school'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'belanja_id' => ['required', 'integer'],
            'tanggal_bapb' => ['required', 'date'],
            'hasil_pemeriksaan' => ['required', Rule::in(['Baik dan sesuai', 'Baik, terdapat catatan', 'Tidak sesuai'])],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($validated) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();

            $belanja = Belanja::where('user_id', auth()->id())
                ->lockForUpdate()
                ->findOrFail($validated['belanja_id']);

            if (!SchoolProfile::where('user_id', auth()->id())->exists()) {
                return redirect()
                    ->route('school-profile.create')
                    ->with('error', 'Lengkapi profil sekolah sebelum membuat BAPB.');
            }

            $bapbAda = Bapb::where('user_id', auth()->id())
                ->where('belanja_id', $belanja->id)
                ->first();

            if ($bapbAda) {
                return redirect()->route('bapb.show', $bapbAda->id);
            }

            $bapb = Bapb::create([
                'user_id' => auth()->id(),
                'belanja_id' => $belanja->id,
                'nomor_bapb' => $this->nextNomorBapb(),
                'tanggal_bapb' => $validated['tanggal_bapb'],
                'hasil_pemeriksaan' => $validated['hasil_pemeriksaan'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            return redirect()
                ->route('bapb.show', $bapb->id)
                ->with('success', 'BAPB berhasil dibuat.');
        });
    }

    public function show($id)
    {
        $bapb = Bapb::with([
            'belanja.notaPesanan',
            'belanja.kwitansi',
            'belanja.bapb',
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $school = SchoolProfile::where('user_id', auth()->id())->first();

        return view('bapb.show', compact('bapb', 'school'));
    }

    private function nextNomorBapb(): string
    {
        $urutan = Bapb::where('user_id', auth()->id())->count() + 1;

        do {
            $nomorBapb = 'BAPB-' . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
            $sudahAda = Bapb::where('user_id', auth()->id())
                ->where('nomor_bapb', $nomorBapb)
                ->exists();
            $urutan++;
        } while ($sudahAda);

        return $nomorBapb;
    }
}
