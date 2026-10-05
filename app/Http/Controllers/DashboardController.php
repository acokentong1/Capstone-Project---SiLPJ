<?php

namespace App\Http\Controllers;

use App\Models\Bapb;
use App\Models\Belanja;
use App\Models\Kwitansi;
use App\Models\NotaPesanan;
use App\Models\SchoolProfile;
use App\Services\LpjAttentionService;

class DashboardController extends Controller
{
    public function index(LpjAttentionService $attentionService)
    {
        $userId = auth()->id();

        $school = SchoolProfile::where('user_id', $userId)->first();

        $jumlahTransaksi = Belanja::where('user_id', $userId)->count();
        $totalBelanja = (int) Belanja::where('user_id', $userId)->sum('total');

        $jumlahNota = NotaPesanan::where('user_id', $userId)->count();
        $jumlahKwitansi = Kwitansi::whereHas('belanja', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();
        $jumlahBapb = Bapb::where('user_id', $userId)->count();

        $jumlahLengkap = Belanja::where('user_id', $userId)
            ->whereHas('notaPesanan')
            ->whereHas('kwitansi')
            ->whereHas('bapb')
            ->count();

        $jumlahBelumLengkap = max(0, $jumlahTransaksi - $jumlahLengkap);
        $persentaseLengkap = $jumlahTransaksi > 0
            ? (int) round(($jumlahLengkap / $jumlahTransaksi) * 100)
            : 0;

        $transaksiTerbaru = Belanja::with(['notaPesanan', 'kwitansi', 'bapb'])
            ->where('user_id', $userId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $attentionSummary = $attentionService->summary((int) $userId);

        return view('dashboard', compact(
            'school',
            'jumlahTransaksi',
            'totalBelanja',
            'jumlahNota',
            'jumlahKwitansi',
            'jumlahBapb',
            'jumlahLengkap',
            'jumlahBelumLengkap',
            'persentaseLengkap',
            'transaksiTerbaru',
            'attentionSummary'
        ));
    }
}
