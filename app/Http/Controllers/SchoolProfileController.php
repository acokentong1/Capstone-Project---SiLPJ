<?php

namespace App\Http\Controllers;

use App\Models\SchoolProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolProfileController extends Controller
{
    public function index()
    {
        $school = SchoolProfile::where('user_id', Auth::id())->first();

        if (!$school) {
            return redirect()->route('school-profile.create');
        }

        return view('school-profile.index', compact('school'));
    }

    public function create()
    {
        $school = SchoolProfile::where('user_id', Auth::id())->first();

        if ($school) {
            return redirect()->route('school-profile.edit', $school);
        }

        return view('school-profile.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateSchoolProfile($request);

        $school = SchoolProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return redirect()
            ->route('school-profile.index')
            ->with('success', 'Profil sekolah berhasil disimpan.');
    }

    public function edit(SchoolProfile $schoolProfile)
    {
        $this->ensureOwnedByAuthenticatedUser($schoolProfile);

        return view('school-profile.edit', [
            'school' => $schoolProfile,
        ]);
    }

    public function update(Request $request, SchoolProfile $schoolProfile)
    {
        $this->ensureOwnedByAuthenticatedUser($schoolProfile);

        $schoolProfile->update($this->validateSchoolProfile($request));

        return redirect()
            ->route('school-profile.index')
            ->with('success', 'Profil sekolah berhasil diperbarui.');
    }

    private function validateSchoolProfile(Request $request): array
    {
        return $request->validate([
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'kepala_sekolah' => ['nullable', 'string', 'max:255'],
            'nip_kepala' => ['nullable', 'string', 'max:50'],
            'bendahara' => ['nullable', 'string', 'max:255'],
            'nip_bendahara' => ['nullable', 'string', 'max:50'],
        ]);
    }

    private function ensureOwnedByAuthenticatedUser(SchoolProfile $schoolProfile): void
    {
        abort_if((int) $schoolProfile->user_id !== (int) Auth::id(), 403);
    }
}
