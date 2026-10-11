<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\NotaPesanan;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\LpjWorkflow;
use Illuminate\Support\Facades\DB;

class NotaPesananController extends Controller
{
    public function index(Request $request)
    {
        $query = NotaPesanan::with([
            'belanja.kwitansi',
            'belanja.bapb',
        ])->whereHas(
            'belanja',
            fn($q) => $q->where(
                'school_profile_id',
                auth()->user()->school_profile_id ?? 0,
            ),
        );

        if ($request->filled('q')) {
            $search = trim((string) $request->q);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_nota', 'like', "%{$search}%")->orWhereHas(
                    'belanja',
                    function ($belanjaQuery) use ($search) {
                        $belanjaQuery
                            ->where('uraian', 'like', "%{$search}%")
                            ->orWhere('nomor_bukti', 'like', "%{$search}%");
                    },
                );
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_nota', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_nota', '<=', $request->tanggal_sampai);
        }

        $notas = $query
            ->orderByDesc('tanggal_nota')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totalNota = NotaPesanan::whereHas(
            'belanja',
            fn($q) => $q->where(
                'school_profile_id',
                auth()->user()->school_profile_id ?? 0,
            ),
        )->count();
        $totalNilai = Belanja::where(
            'school_profile_id',
            auth()->user()->school_profile_id ?? 0,
        )
            ->whereHas('notaPesanan')
            ->sum('total');
        $notaLengkap = Belanja::where(
            'school_profile_id',
            auth()->user()->school_profile_id ?? 0,
        )
            ->whereHas('notaPesanan')
            ->whereHas('kwitansi')
            ->whereHas('bapb')
            ->count();

        return view(
            'nota-pesanan.index',
            compact('notas', 'totalNota', 'totalNilai', 'notaLengkap'),
        );
    }

    public function create($id)
    {
        $belanja = Belanja::where(
            'school_profile_id',
            auth()->user()->school_profile_id ?? 0,
        )->findOrFail($id);

        LpjWorkflow::assertEditable($belanja);

        $notaAda = NotaPesanan::where('belanja_id', $belanja->id)
            ->whereHas(
                'belanja',
                fn($q) => $q->where(
                    'school_profile_id',
                    auth()->user()->school_profile_id ?? 0,
                ),
            )
            ->first();

        if ($notaAda) {
            return redirect()->route('nota-pesanan.show', $notaAda->id);
        }

        $school = auth()->user()->schoolProfile;

        if (!$school) {
            return redirect()
                ->route('school-profile.create')
                ->with(
                    'error',
                    'Lengkapi profil sekolah sebelum membuat Nota Pesanan.',
                );
        }

        $nomorNota = $this->nextNomorNota();

        return view(
            'nota-pesanan.create',
            compact('belanja', 'school', 'nomorNota'),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'belanja_id' => ['required', 'integer'],
        ]);

        return DB::transaction(function () use ($validated) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();

            $belanja = Belanja::where(
                'school_profile_id',
                auth()->user()->school_profile_id ?? 0,
            )
                ->lockForUpdate()
                ->findOrFail($validated['belanja_id']);
            LpjWorkflow::assertEditable($belanja);

            if (!auth()->user()->schoolProfile()->exists()) {
                return redirect()
                    ->route('school-profile.create')
                    ->with(
                        'error',
                        'Lengkapi profil sekolah sebelum membuat Nota Pesanan.',
                    );
            }

            $notaAda = NotaPesanan::whereHas(
                'belanja',
                fn($q) => $q->where(
                    'school_profile_id',
                    auth()->user()->school_profile_id ?? 0,
                ),
            )
                ->where('belanja_id', $belanja->id)
                ->first();

            if ($notaAda) {
                return redirect()->route('nota-pesanan.show', $notaAda->id);
            }

            $nota = NotaPesanan::create([
                'user_id' => auth()->id(),
                'belanja_id' => $belanja->id,
                'nomor_nota' => $this->nextNomorNota(),
                'tanggal_nota' => $belanja->tanggal,
                'status' => 'Selesai',
            ]);

            return redirect()
                ->route('nota-pesanan.show', $nota->id)
                ->with('success', 'Nota Pesanan berhasil disimpan.');
        });
    }

    public function show($id)
    {
        $nota = NotaPesanan::with([
            'belanja.notaPesanan',
            'belanja.kwitansi',
            'belanja.bapb',
        ])
            ->whereHas(
                'belanja',
                fn($q) => $q->where(
                    'school_profile_id',
                    auth()->user()->school_profile_id ?? 0,
                ),
            )
            ->findOrFail($id);

        $school = auth()->user()->schoolProfile;

        return view('nota-pesanan.show', compact('nota', 'school'));
    }

    private function nextNomorNota(): string
    {
        $urutan = NotaPesanan::where('user_id', auth()->id())->count() + 1;

        do {
            $nomorNota =
                'NP-' . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
            $sudahAda = NotaPesanan::where('user_id', auth()->id())
                ->where('nomor_nota', $nomorNota)
                ->exists();
            $urutan++;
        } while ($sudahAda);

        return $nomorNota;
    }
}
