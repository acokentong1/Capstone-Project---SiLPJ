<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
            'progress' => (string) $request->query('progress', ''),
        ];

        $query = User::query()
            ->with('schoolProfile')
            ->withCount([
                'belanjas',
                'belanjas as lpj_lengkap_count' => fn ($belanja) => $belanja
                    ->whereHas('notaPesanan')
                    ->whereHas('kwitansi')
                    ->whereHas('bapb'),
                'belanjas as nota_count' => fn ($belanja) => $belanja->whereHas('notaPesanan'),
                'belanjas as kwitansi_count' => fn ($belanja) => $belanja->whereHas('kwitansi'),
                'belanjas as bapb_count' => fn ($belanja) => $belanja->whereHas('bapb'),
            ])
            ->withSum('belanjas', 'total')
            ->orderBy('name');

        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q): void {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereHas('schoolProfile', function ($school) use ($q): void {
                        $school->where('nama_sekolah', 'like', "%{$q}%")
                            ->orWhere('npsn', 'like', "%{$q}%");
                    });
            });
        }

        if (in_array($filters['status'], ['active', 'inactive'], true)) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        if ($filters['progress'] === 'empty') {
            $query->doesntHave('belanjas');
        } elseif ($filters['progress'] === 'complete') {
            $query->whereHas('belanjas')
                ->whereDoesntHave('belanjas', function ($belanja): void {
                    $belanja->where(function ($missing): void {
                        $missing->whereDoesntHave('notaPesanan')
                            ->orWhereDoesntHave('kwitansi')
                            ->orWhereDoesntHave('bapb');
                    });
                });
        } elseif ($filters['progress'] === 'incomplete') {
            $query->whereHas('belanjas', function ($belanja): void {
                $belanja->where(function ($missing): void {
                    $missing->whereDoesntHave('notaPesanan')
                        ->orWhereDoesntHave('kwitansi')
                        ->orWhereDoesntHave('bapb');
                });
            });
        }

        $users = $query->paginate(12)->withQueryString();

        $users->getCollection()->transform(function (User $user): User {
            $user->setAttribute('lpj_belum_lengkap_count', max(0, $user->belanjas_count - $user->lpj_lengkap_count));
            $user->setAttribute(
                'lpj_progress',
                $user->belanjas_count > 0
                    ? (int) round(($user->lpj_lengkap_count / $user->belanjas_count) * 100)
                    : 0
            );

            return $user;
        });

        return view('admin.monitoring.index', compact('users', 'filters'));
    }
}
