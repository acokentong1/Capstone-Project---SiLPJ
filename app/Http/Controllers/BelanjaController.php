<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BelanjaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $kategori = in_array($request->query('kategori'), ['Barang', 'Jasa'], true)
            ? $request->query('kategori')
            : '';
        $status = in_array($request->query('status'), ['lengkap', 'proses', 'belum'], true)
            ? $request->query('status')
            : '';
        $tanggalMulai = $this->normalizeDate($request->query('tanggal_mulai'));
        $tanggalSelesai = $this->normalizeDate($request->query('tanggal_selesai'));

        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai > $tanggalSelesai) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        $baseQuery = Belanja::query()
            ->where('user_id', Auth::id());

        $totalTransaksi = (clone $baseQuery)->count();
        $totalBelanja = (clone $baseQuery)->sum('total');
        $lpjLengkap = (clone $baseQuery)
            ->whereHas('notaPesanan')
            ->whereHas('kwitansi')
            ->whereHas('bapb')
            ->count();
        $lpjBelumLengkap = max(0, $totalTransaksi - $lpjLengkap);

        $query = clone $baseQuery;

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nomor_bukti', 'like', "%{$search}%")
                    ->orWhere('uraian', 'like', "%{$search}%");
            });
        }

        if ($kategori !== '') {
            $query->where('kategori', $kategori);
        }

        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal', '<=', $tanggalSelesai);
        }

        if ($status === 'lengkap') {
            $query->whereHas('notaPesanan')
                ->whereHas('kwitansi')
                ->whereHas('bapb');
        } elseif ($status === 'proses') {
            $query->where(function ($builder) {
                $builder->whereHas('notaPesanan')
                    ->orWhereHas('kwitansi')
                    ->orWhereHas('bapb');
            })->where(function ($builder) {
                $builder->whereDoesntHave('notaPesanan')
                    ->orWhereDoesntHave('kwitansi')
                    ->orWhereDoesntHave('bapb');
            });
        } elseif ($status === 'belum') {
            $query->whereDoesntHave('notaPesanan')
                ->whereDoesntHave('kwitansi')
                ->whereDoesntHave('bapb');
        }

        $filteredTotal = (clone $query)->sum('total');

        $belanjas = $query
            ->with(['notaPesanan', 'kwitansi', 'bapb'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $filters = [
            'q' => $search,
            'kategori' => $kategori,
            'status' => $status,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
        ];

        $hasFilters = collect($filters)->contains(fn ($value) => filled($value));

        return view('belanja.index', compact(
            'belanjas',
            'totalTransaksi',
            'totalBelanja',
            'lpjLengkap',
            'lpjBelumLengkap',
            'filteredTotal',
            'filters',
            'hasFilters'
        ));
    }

    public function create()
    {
        return view('belanja.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_bukti' => ['required', 'string', 'max:255'],
            'uraian' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(['Barang', 'Jasa'])],
            'jumlah' => ['required', 'integer', 'min:1'],
            'harga_satuan' => ['required', 'integer', 'min:0'],
        ]);

        $total = $validated['jumlah'] * $validated['harga_satuan'];

        Belanja::create([
            'user_id' => Auth::id(),
            'tanggal' => $validated['tanggal'],
            'nomor_bukti' => trim($validated['nomor_bukti']),
            'uraian' => trim($validated['uraian']),
            'kategori' => $validated['kategori'],
            'jumlah' => $validated['jumlah'],
            'harga_satuan' => $validated['harga_satuan'],
            'total' => $total,
        ]);

        return redirect()
            ->route('belanja.index')
            ->with('success', 'Data belanja berhasil disimpan. Dokumen LPJ sekarang dapat dilengkapi.');
    }

    public function edit(Belanja $belanja)
    {
        $this->ensureOwnedByAuthenticatedUser($belanja);
        $belanja->loadMissing(['notaPesanan', 'kwitansi', 'bapb']);

        return view('belanja.edit', compact('belanja'));
    }

    public function update(Request $request, Belanja $belanja)
    {
        $this->ensureOwnedByAuthenticatedUser($belanja);

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_bukti' => ['required', 'string', 'max:255'],
            'uraian' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(['Barang', 'Jasa'])],
            'jumlah' => ['required', 'integer', 'min:1'],
            'harga_satuan' => ['required', 'integer', 'min:0'],
        ]);

        $validated['nomor_bukti'] = trim($validated['nomor_bukti']);
        $validated['uraian'] = trim($validated['uraian']);
        $validated['total'] = $validated['jumlah'] * $validated['harga_satuan'];

        $belanja->update($validated);

        return redirect()
            ->route('belanja.index')
            ->with('success', 'Data belanja berhasil diperbarui.');
    }

    public function destroy(Belanja $belanja)
    {
        $this->ensureOwnedByAuthenticatedUser($belanja);

        $sudahDipakai = $belanja->notaPesanan()->exists()
            || $belanja->kwitansi()->exists()
            || $belanja->bapb()->exists();

        if ($sudahDipakai) {
            return back()->with(
                'error',
                'Data belanja tidak dapat dihapus karena sudah memiliki dokumen LPJ.'
            );
        }

        $belanja->delete();

        return redirect()
            ->route('belanja.index')
            ->with('success', 'Data belanja berhasil dihapus.');
    }

    private function ensureOwnedByAuthenticatedUser(Belanja $belanja): void
    {
        abort_if((int) $belanja->user_id !== (int) auth()->id(), 403);
    }

    private function normalizeDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value
            ? $value
            : null;
    }
}
