<?php
namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\Belanja;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tahun' => ['nullable', 'integer', 'between:1900,9999'],
        ]);
        $school = $request->user()->schoolProfile;
        $schoolId = $school?->id ?? 0;
        $anggarans = Anggaran::where('school_profile_id', $schoolId)
            ->orderByDesc('tahun')
            ->get();
        $tahunTersedia = $anggarans
            ->pluck('tahun')
            ->merge(
                Belanja::where('school_profile_id', $schoolId)
                    ->whereNotNull('tahun_anggaran')
                    ->distinct()
                    ->pluck('tahun_anggaran'),
            )
            ->unique()
            ->sortDesc()
            ->values();
        $tahunDipilih = $request->filled('tahun')
            ? (int) $request->tahun
            : (int) ($tahunTersedia->contains(now()->year)
                ? now()->year
                : $tahunTersedia->first() ?? now()->year);
        $tahunAnggaran = $tahunDipilih;
        $anggaranAktif = $anggarans->firstWhere('tahun', $tahunDipilih);
        $belanjas = Belanja::where('school_profile_id', $schoolId)
            ->where('tahun_anggaran', $tahunDipilih)
            ->with(['notaPesanan', 'kwitansi', 'bapb'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
        $totalBelanja = $belanjas->sum('total');
        $jumlahTransaksi = $belanjas->count();
        $lengkap = $belanjas->filter(
            fn($item) => $item->notaPesanan && $item->kwitansi && $item->bapb,
        );
        $jumlahLengkap = $lengkap->count();
        $jumlahBelumLengkap = $jumlahTransaksi - $jumlahLengkap;
        $transaksiTerbaru = $belanjas->take(5);
        $jumlahNota = $belanjas
            ->filter(fn($item) => $item->notaPesanan)
            ->count();
        $jumlahKwitansi = $belanjas
            ->filter(fn($item) => $item->kwitansi)
            ->count();
        $jumlahBapb = $belanjas->filter(fn($item) => $item->bapb)->count();
        $persentaseLengkap = $jumlahTransaksi
            ? round(($jumlahLengkap / $jumlahTransaksi) * 100)
            : 0;
        $paguAnggaran = $anggaranAktif?->jumlah ?? 0;
        $realisasiAnggaran = $totalBelanja;
        $sisaAnggaran = $paguAnggaran - $realisasiAnggaran;
        $serapanAnggaran =
            $paguAnggaran > 0
                ? round(($realisasiAnggaran / $paguAnggaran) * 100)
                : 0;
        $attentionSummary = [
            'belum_lengkap' => $jumlahBelumLengkap,
            'nilai_belum_lengkap' => $totalBelanja - $lengkap->sum('total'),
            'nota_kurang' => $jumlahTransaksi - $jumlahNota,
            'kwitansi_kurang' => $jumlahTransaksi - $jumlahKwitansi,
            'bapb_kurang' => $jumlahTransaksi - $jumlahBapb,
        ];
        return view(
            'dashboard',
            compact(
                'school',
                'belanjas',
                'totalBelanja',
                'jumlahTransaksi',
                'jumlahLengkap',
                'jumlahBelumLengkap',
                'transaksiTerbaru',
                'persentaseLengkap',
                'jumlahNota',
                'jumlahKwitansi',
                'jumlahBapb',
                'anggarans',
                'anggaranAktif',
                'tahunDipilih',
                'tahunAnggaran',
                'tahunTersedia',
                'paguAnggaran',
                'realisasiAnggaran',
                'sisaAnggaran',
                'serapanAnggaran',
                'attentionSummary',
            ),
        );
    }
}
