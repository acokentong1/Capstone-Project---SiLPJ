<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapPdfController extends Controller
{
    public function pdf(Request $request)
    {
        $query = Belanja::query()
            ->where('user_id', auth()->id());

        if ($request->tahun) {
            $query->where('tahun_anggaran', $request->tahun);
        }

        if ($request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        $items = $query
            ->orderBy('tanggal', 'asc')
            ->get();

        $total = $items->sum('total');

        $tahunAnggaran = $request->tahun;

        return Pdf::loadView('pdf.rekap-keuangan', compact(
            'items',
            'total',
            'tahunAnggaran'
        ))
        ->setPaper('A4','portrait')
        ->download('LPJ-SILPJ-'.$tahunAnggaran.'.pdf');
    }
}
