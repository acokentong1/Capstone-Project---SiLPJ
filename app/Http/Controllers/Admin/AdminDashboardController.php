<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Bapb;
use App\Models\Belanja;
use App\Models\Kwitansi;
use App\Models\NotaPesanan;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalSchools = SchoolProfile::count();
        $totalTransactions = Belanja::count();
        $totalValue = (int) Belanja::sum('total');

        $completeLpj = Belanja::query()
            ->whereHas('notaPesanan')
            ->whereHas('kwitansi')
            ->whereHas('bapb')
            ->count();

        $completionRate = $totalTransactions > 0
            ? (int) round(($completeLpj / $totalTransactions) * 100)
            : 0;

        $documentCounts = [
            'nota' => NotaPesanan::count(),
            'kwitansi' => Kwitansi::count(),
            'bapb' => Bapb::count(),
        ];

        $missingCounts = [
            'nota' => max(0, $totalTransactions - $documentCounts['nota']),
            'kwitansi' => max(0, $totalTransactions - $documentCounts['kwitansi']),
            'bapb' => max(0, $totalTransactions - $documentCounts['bapb']),
        ];

        $schoolMonitoring = User::query()
            ->with('schoolProfile')
            ->withCount([
                'belanjas',
                'belanjas as lpj_lengkap_count' => fn ($query) => $query
                    ->whereHas('notaPesanan')
                    ->whereHas('kwitansi')
                    ->whereHas('bapb'),
            ])
            ->withSum('belanjas', 'total')
            ->where('is_active', true)
            ->get()
            ->map(function (User $user): User {
                $user->setAttribute('lpj_belum_lengkap_count', max(0, $user->belanjas_count - $user->lpj_lengkap_count));
                $user->setAttribute(
                    'lpj_progress',
                    $user->belanjas_count > 0
                        ? (int) round(($user->lpj_lengkap_count / $user->belanjas_count) * 100)
                        : 0
                );

                return $user;
            })
            ->sortByDesc(fn (User $user) => ($user->lpj_belum_lengkap_count * 1000000) + $user->belanjas_count)
            ->take(8)
            ->values();

        $recentActivities = ActivityLog::query()
            ->with('user:id,name,email')
            ->latest('created_at')
            ->limit(8)
            ->get();

        $recentUsers = User::query()
            ->with('schoolProfile')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $activityLast7Days = ActivityLog::where('created_at', '>=', now()->subDays(7))->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'totalSchools',
            'totalTransactions',
            'totalValue',
            'completeLpj',
            'completionRate',
            'documentCounts',
            'missingCounts',
            'schoolMonitoring',
            'recentActivities',
            'recentUsers',
            'activityLast7Days'
        ));
    }
}
