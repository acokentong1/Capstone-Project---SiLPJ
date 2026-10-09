<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapExport;

class RekapExportController extends Controller
{
    public function excel(Request $request)
    {
        return Excel::download(
            new RekapExport(
                Auth::id(),
                $request->year,
                $request->month,
                $request->kategori,
                $request->tahun_anggaran
            ),
            'rekap-keuangan-silpj.xlsx'
        );
    }
}