<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->year ?? now()->year;
        $month = $request->month;
        $kategori = $request->kategori;

        $query = Belanja::where('user_id', Auth::id())
            ->whereYear('tanggal', $year);

        if ($month) {
            $query->whereMonth('tanggal', $month);
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        $items = $query->latest('tanggal')->get();

        $summary = [
            'total' => $items->sum('total'),
            'jumlah' => $items->count(),
            'barang' => $items->where('kategori', 'Barang')->sum('total'),
            'jasa' => $items->where('kategori', 'Jasa')->sum('total'),
        ];

        // Grafik tetap mengikuti filter tahun
        $monthlyQuery = Belanja::where('user_id', Auth::id())
            ->whereYear('tanggal', $year);

        if ($kategori) {
            $monthlyQuery->where('kategori', $kategori);
        }

        if ($month) {
            $monthlyQuery->whereMonth('tanggal', $month);
        }

        $monthly = $monthlyQuery
            ->selectRaw('MONTH(tanggal) as bulan, SUM(total) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $namaBulan = [
            1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',
            5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',
            9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
        ];

        $labelsBulan = [];
        $dataBulan = [];

        foreach ($monthly as $row) {
            $labelsBulan[] = $namaBulan[$row->bulan];
            $dataBulan[] = $row->total;
        }

        return view('rekap.index', compact(
            'items',
            'summary',
            'labelsBulan',
            'dataBulan',
            'year',
            'month',
            'kategori'
        ));
    }
}
