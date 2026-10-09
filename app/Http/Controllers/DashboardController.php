<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Belanja;
use App\Models\Anggaran;
use App\Models\NotaPesanan;
use App\Models\Kwitansi;
use App\Models\Bapb;
use App\Models\SchoolProfile;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // tahun yang dipilih
        $tahunDipilih = $request->tahun ?? now()->year;

        /*
        |--------------------------------------------------------------------------
        | Ambil data sekolah
        |--------------------------------------------------------------------------
        */

        $school = null;

        if ($user) {
            // ambil berdasarkan relasi user
            $school = SchoolProfile::where('user_id', $user->id)->first();

            // fallback berdasarkan school_profile_id
            if (!$school && $user->school_profile_id) {
                $school = SchoolProfile::find($user->school_profile_id);
            }

            // fallback terakhir
            if (!$school) {
                $school = SchoolProfile::first();
            }
        }

        $schoolId = $school ? $school->id : null;

        /*
        |--------------------------------------------------------------------------
        | Data Belanja
        |--------------------------------------------------------------------------
        */

        $belanjas = Belanja::where('school_profile_id', $schoolId)
            ->latest()
            ->get();

        $totalBelanja = $belanjas->sum('total');

        $jumlahTransaksi = $belanjas->count();

        /*
        |--------------------------------------------------------------------------
        | Status LPJ
        |--------------------------------------------------------------------------
        */

        $jumlahLengkap = 0;
        $jumlahBelumLengkap = 0;

        foreach ($belanjas as $belanja) {
            $lengkap =
                NotaPesanan::where('belanja_id', $belanja->id)->exists() &&
                Kwitansi::where('belanja_id', $belanja->id)->exists() &&
                Bapb::where('belanja_id', $belanja->id)->exists();

            if ($lengkap) {
                $jumlahLengkap++;
            } else {
                $jumlahBelumLengkap++;
            }
        }

        $transaksiTerbaru = $belanjas->take(5);

        $jumlahNota = NotaPesanan::whereIn(
            'belanja_id',
            $belanjas->pluck('id'),
        )->count();

        $jumlahKwitansi = Kwitansi::whereIn(
            'belanja_id',
            $belanjas->pluck('id'),
        )->count();

        $jumlahBapb = Bapb::whereIn(
            'belanja_id',
            $belanjas->pluck('id'),
        )->count();

        $persentaseLengkap =
            $jumlahTransaksi > 0
                ? round(($jumlahLengkap / $jumlahTransaksi) * 100)
                : 0;

        /*
        |--------------------------------------------------------------------------
        | Data Anggaran
        |--------------------------------------------------------------------------
        */

        $anggarans = Anggaran::where('school_profile_id', $schoolId)
            ->orderBy('tahun', 'desc')
            ->get();

        $anggaranAktif = Anggaran::where('school_profile_id', $schoolId)
            ->where('tahun', $tahunDipilih)
            ->first();

        $tahunTersedia = $anggarans->pluck('tahun')->unique()->values();

        $paguAnggaran = $anggaranAktif->jumlah ?? 0;

        $realisasiAnggaran = Belanja::where('school_profile_id', $schoolId)
            ->where('tahun_anggaran', $tahunDipilih)
            ->sum('total');

        $sisaAnggaran = $paguAnggaran - $realisasiAnggaran;

        $serapanAnggaran =
            $paguAnggaran > 0
                ? round(($realisasiAnggaran / $paguAnggaran) * 100)
                : 0;

        $tahunAnggaran = $tahunDipilih;

        $attentionSummary = [];

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
