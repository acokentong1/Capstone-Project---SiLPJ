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
    public function create(Request $request): View
    {
        $schools = \App\Models\SchoolProfile::orderBy('nama_sekolah')->get();
        $selectedSchool = $request->user()->school_profile_id;
        if (!$selectedSchool && $schools->count() === 1) {
            $selectedSchool = $schools->first()->id;
        }
        return view('admin.users.create', compact('schools', 'selectedSchool'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,bendahara,kepala_sekolah,user'],
            'school_profile_id' => ['required', 'integer', 'exists:school_profiles,id'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);
        $user = DB::transaction(function () use ($request, $data) {
            $user = new User();
            $user->forceFill([
                'name' => $data['name'], 'email' => $data['email'],
                'password' => $data['password'], 'role' => $data['role'],
                'school_profile_id' => $data['school_profile_id'], 'is_active' => true,
            ])->save();
            ActivityLogger::record($request->user()->id, 'admin', 'Membuat akun '.$user->email, $user, [
                'role' => $user->role, 'school_profile_id' => $user->school_profile_id,
            ]);
            return $user;
        });
        return redirect()->route('admin.users.show', $user)->with('success', 'Akun berhasil dibuat dan terhubung ke sekolah. Sampaikan kata sandi secara pribadi kepada pemilik akun.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
        ]);
        DB::transaction(function () use ($request, $user, $data) {
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($target->email !== $data['email']) {
                $target->email_verified_at = null;
            }
            $target->fill($data)->save();
            ActivityLogger::record($request->user()->id, 'admin', 'Mengubah identitas akun '.$target->email, $target);
        });
        return redirect()->route('admin.users.show', $user)->with('success', 'Identitas akun berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);
        DB::transaction(function () use ($request, $user, $data) {
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $target->forceFill([
                'password' => $data['password'],
                'remember_token' => \Illuminate\Support\Str::random(60),
            ])->save();
            DB::table('sessions')->where('user_id', $target->id)->delete();
            DB::table('password_reset_tokens')->where('email', $target->email)->delete();
            ActivityLogger::record($request->user()->id, 'admin', 'Mereset kata sandi akun '.$target->email, $target);
        });
        if ($request->user()->is($user)) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('status', 'Kata sandi diperbarui. Masuk kembali dengan kata sandi baru.');
        }
        return back()->with('success', 'Kata sandi diperbarui. Sampaikan kata sandi baru secara pribadi kepada pemilik akun.');
    }

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
                    ->orWhereHas(
                        'schoolProfile',
                        fn($school) => $school->where(
                            'nama_sekolah',
                            'like',
                            "%{$q}%",
                        ),
                    );
            });
        }

        if (
            in_array(
                $filters['role'],
                ['admin', 'bendahara', 'kepala_sekolah', 'user'],
                true,
            )
        ) {
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
        $schools = \App\Models\SchoolProfile::orderBy('nama_sekolah')->get();

        $totalBelanja = $user->belanjas()->sum('total');
        $jumlahBelanja = $user->belanjas()->count();
        $lpjLengkap = $user
            ->belanjas()
            ->whereHas('notaPesanan')
            ->whereHas('kwitansi')
            ->whereHas('bapb')
            ->count();

        $lastLogin = $user
            ->activityLogs()
            ->where('action', 'login')
            ->latest('created_at')
            ->first();

        $recentActivities = $user
            ->activityLogs()
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view(
            'admin.users.show',
            compact(
                'user',
                'totalBelanja',
                'jumlahBelanja',
                'lpjLengkap',
                'lastLogin',
                'schools',
                'recentActivities',
            ),
        );
    }

    public function updateSchool(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'school_profile_id' => [
                'required',
                'integer',
                'exists:school_profiles,id',
            ],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        DB::transaction(function () use ($request, $user, $data) {
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (
                (int) $target->school_profile_id ===
                (int) $data['school_profile_id']
            ) {
                return;
            }
            // A reassignment never silently moves financial records to another school.
            if (
                $target->belanjas()->exists() ||
                \App\Models\Anggaran::where('user_id', $target->id)->exists() ||
                \App\Models\NotaPesanan::where(
                    'user_id',
                    $target->id,
                )->exists() ||
                \App\Models\Bapb::where('user_id', $target->id)->exists() ||
                \App\Models\LpjEvent::where('actor_id', $target->id)->exists()
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'school_profile_id' =>
                        'Akun sudah memiliki riwayat keuangan. Gunakan akun baru untuk penugasan sekolah lain; data lama tetap dipertahankan.',
                ]);
            }
            $before = $target->school_profile_id;
            $target->school_profile_id = $data['school_profile_id'];
            $target->save();
            ActivityLogger::record(
                (int) $request->user()->id,
                'admin',
                'Mengubah penugasan sekolah akun ' . $target->email,
                $target,
                [
                    'before' => $before,
                    'after' => $target->school_profile_id,
                    'reason' => $data['reason'],
                ],
            );
        });
        return back()->with(
            'success',
            'Penugasan sekolah berhasil diperbarui.',
        );
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,bendahara,kepala_sekolah,user'],
        ]);

        $actor = $request->user();
        $newRole = $validated['role'];

        if ($actor->is($user) && $newRole !== 'admin') {
            return back()->with(
                'error',
                'Anda tidak dapat menurunkan role admin milik akun sendiri.',
            );
        }

        if ($user->isAdmin() && $newRole !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with(
                    'error',
                    'Role admin terakhir tidak dapat diturunkan.',
                );
            }
        }

        if ($user->role === $newRole) {
            return back()->with('success', 'Role akun tidak berubah.');
        }

        $oldRole = $user->role;

        DB::transaction(function () use (
            $user,
            $newRole,
            $actor,
            $oldRole,
        ): void {
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
                ],
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

        if ($actor->is($user) && !$newStatus) {
            return back()->with(
                'error',
                'Anda tidak dapat menonaktifkan akun yang sedang digunakan.',
            );
        }

        if ($user->isAdmin() && !$newStatus) {
            $activeAdminCount = User::where('role', 'admin')
                ->where('is_active', true)
                ->count();
            if ($activeAdminCount <= 1) {
                return back()->with(
                    'error',
                    'Admin aktif terakhir tidak dapat dinonaktifkan.',
                );
            }
        }

        if ($user->isActive() === $newStatus) {
            return back()->with('success', 'Status akun tidak berubah.');
        }

        DB::transaction(function () use ($user, $newStatus, $actor): void {
            User::withoutEvents(function () use ($user, $newStatus): void {
                $user->forceFill(['is_active' => $newStatus])->save();
            });

            if (!$newStatus) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            ActivityLogger::record(
                (int) $actor->id,
                'admin',
                ($newStatus ? 'Mengaktifkan' : 'Menonaktifkan') .
                    " akun {$user->email}",
                $user,
                [
                    'target_user_id' => $user->id,
                    'is_active' => $newStatus,
                ],
            );
        });

        return back()->with(
            'success',
            $newStatus
                ? 'Akun berhasil diaktifkan.'
                : 'Akun berhasil dinonaktifkan.',
        );
    }
}
