<?php

namespace App\Http\Controllers;

use App\Support\RekapQuery;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapPdfController extends Controller
{
    public function pdf(Request $request)
    {
        $filters = RekapQuery::filters($request);
        $items = RekapQuery::query(
            (int) $request->user()->school_profile_id,
            $filters,
        )
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $total = $items->sum('total');
        $tahunAnggaran = $filters['tahun'];
        $school = $request->user()->schoolProfile;
        return Pdf::loadView(
            'pdf.rekap-keuangan',
            compact('items', 'total', 'tahunAnggaran', 'school'),
        )
            ->setPaper('A4', 'portrait')
            ->download('LPJ-SILPJ-' . $tahunAnggaran . '.pdf');
    }
}
