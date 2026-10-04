<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'role' => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $query = User::query()
            ->with('schoolProfile')
            ->withCount(['belanjas', 'activityLogs'])
            ->orderByDesc('created_at');

        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q): void {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereHas('schoolProfile', fn ($school) => $school->where('nama_sekolah', 'like', "%{$q}%"));
            });
        }

        if (in_array($filters['role'], ['admin', 'user'], true)) {
            $query->where('role', $filters['role']);
        }

        if (in_array($filters['status'], ['active', 'inactive'], true)) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        $users = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'filters', 'stats'));
    }

    public function show(User $user): View
    {
        $user->load('schoolProfile');

        $totalBelanja = $user->belanjas()->sum('total');
        $jumlahBelanja = $user->belanjas()->count();
        $lpjLengkap = $user->belanjas()
            ->whereHas('notaPesanan')
            ->whereHas('kwitansi')
            ->whereHas('bapb')
            ->count();

        $lastLogin = $user->activityLogs()
            ->where('action', 'login')
            ->latest('created_at')
            ->first();

        $recentActivities = $user->activityLogs()
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.users.show', compact(
            'user',
            'totalBelanja',
            'jumlahBelanja',
            'lpjLengkap',
            'lastLogin',
            'recentActivities'
        ));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        $actor = $request->user();
        $newRole = $validated['role'];

        if ($actor->is($user) && $newRole !== 'admin') {
            return back()->with('error', 'Anda tidak dapat menurunkan role admin milik akun sendiri.');
        }

        if ($user->isAdmin() && $newRole !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Role admin terakhir tidak dapat diturunkan.');
            }
        }

        if ($user->role === $newRole) {
            return back()->with('success', 'Role akun tidak berubah.');
        }

        $oldRole = $user->role;

        DB::transaction(function () use ($user, $newRole, $actor, $oldRole): void {
            User::withoutEvents(function () use ($user, $newRole): void {
                $user->forceFill(['role' => $newRole])->save();
            });

            ActivityLogger::record(
                (int) $actor->id,
                'admin',
                "Mengubah role akun {$user->email} dari {$oldRole} menjadi {$newRole}",
                $user,
                [
                    'target_user_id' => $user->id,
                    'old_role' => $oldRole,
                    'new_role' => $newRole,
                ]
            );
        });

        return back()->with('success', 'Role akun berhasil diperbarui.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $actor = $request->user();
        $newStatus = (bool) $validated['is_active'];

        if ($actor->is($user) && ! $newStatus) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.');
        }

        if ($user->isAdmin() && ! $newStatus) {
            $activeAdminCount = User::where('role', 'admin')->where('is_active', true)->count();
            if ($activeAdminCount <= 1) {
                return back()->with('error', 'Admin aktif terakhir tidak dapat dinonaktifkan.');
            }
        }

        if ($user->isActive() === $newStatus) {
            return back()->with('success', 'Status akun tidak berubah.');
        }

        DB::transaction(function () use ($user, $newStatus, $actor): void {
            User::withoutEvents(function () use ($user, $newStatus): void {
                $user->forceFill(['is_active' => $newStatus])->save();
            });

            if (! $newStatus) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            ActivityLogger::record(
                (int) $actor->id,
                'admin',
                ($newStatus ? 'Mengaktifkan' : 'Menonaktifkan') . " akun {$user->email}",
                $user,
                [
                    'target_user_id' => $user->id,
                    'is_active' => $newStatus,
                ]
            );
        });

        return back()->with('success', $newStatus ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.');
    }
}
