<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Support\RekapQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $filters = RekapQuery::filters($request);
        $schoolId = (int) $request->user()->school_profile_id;
        $year = $filters['tahun'];
        $month = $filters['bulan'];
        $kategori = $filters['kategori'];
        $items = RekapQuery::query($schoolId, $filters)
            ->latest('tanggal')
            ->orderByDesc('id')
            ->get();
        $summary = [
            'total' => $items->sum('total'),
            'jumlah' => $items->count(),
            'barang' => $items->where('kategori', 'Barang')->sum('total'),
            'jasa' => $items->where('kategori', 'Jasa')->sum('total'),
        ];
        $namaBulan = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];
        $monthly = $items
            ->groupBy(fn($item) => Carbon::parse($item->tanggal)->month)
            ->sortKeys();
        $labelsBulan = $monthly
            ->keys()
            ->map(fn($month) => $namaBulan[$month])
            ->values()
            ->all();
        $dataBulan = $monthly
            ->map(fn($rows) => $rows->sum('total'))
            ->values()
            ->all();
        $tahunList = Belanja::where('school_profile_id', $schoolId)
            ->whereNotNull('tahun_anggaran')
            ->distinct()
            ->pluck('tahun_anggaran')
            ->push($year)
            ->unique()
            ->sortDesc()
            ->values();
        return view(
            'rekap.index',
            compact(
                'items',
                'summary',
                'labelsBulan',
                'dataBulan',
                'year',
                'month',
                'kategori',
                'tahunList',
                'filters',
            ),
        );
    }
}
