<?php

namespace App\Http\Controllers;

use App\Exports\RekapExport;
use App\Support\RekapQuery;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RekapExportController extends Controller
{
    public function excel(Request $request)
    {
        return Excel::download(
            new RekapExport(
                (int) $request->user()->school_profile_id,
                RekapQuery::filters($request),
                $request->user()->schoolProfile?->nama_sekolah ?? '-',
            ),
            'rekap-keuangan-silpj.xlsx',
        );
    }
}
