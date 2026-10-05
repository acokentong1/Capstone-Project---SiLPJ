<?php

namespace App\Http\Controllers;

use App\Services\LpjAttentionService;
use Illuminate\Http\Request;

class AttentionController extends Controller
{
    public function index(Request $request, LpjAttentionService $attentionService)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'kategori' => ['nullable', 'in:Barang,Jasa'],
            'dokumen' => ['nullable', 'in:nota,kwitansi,bapb'],
            'umur' => ['nullable', 'in:baru,7_13,14_plus'],
        ]);

        $userId = (int) auth()->id();
        $query = $attentionService
            ->incompleteQuery($userId)
            ->with(['notaPesanan', 'kwitansi', 'bapb']);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($subQuery) use ($keyword): void {
                $subQuery
                    ->where('uraian', 'like', "%{$keyword}%")
                    ->orWhere('nomor_bukti', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->string('kategori')->toString());
        }

        if ($request->filled('dokumen')) {
            match ($request->string('dokumen')->toString()) {
                'nota' => $query->whereDoesntHave('notaPesanan'),
                'kwitansi' => $query->whereDoesntHave('kwitansi'),
                'bapb' => $query->whereDoesntHave('bapb'),
                default => null,
            };
        }

        if ($request->filled('umur')) {
            $today = now()->startOfDay();
            $sevenDaysAgo = $today->copy()->subDays(7)->toDateString();
            $fourteenDaysAgo = $today->copy()->subDays(14)->toDateString();

            match ($request->string('umur')->toString()) {
                'baru' => $query->whereDate('tanggal', '>', $sevenDaysAgo),
                '7_13' => $query
                    ->whereDate('tanggal', '<=', $sevenDaysAgo)
                    ->whereDate('tanggal', '>', $fourteenDaysAgo),
                '14_plus' => $query->whereDate('tanggal', '<=', $fourteenDaysAgo),
                default => null,
            };
        }

        $items = $query
            ->orderBy('tanggal')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $summary = $attentionService->summary($userId);

        return view('attention.index', compact('items', 'summary'));
    }
}
