<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolProfile;
use Illuminate\Validation\ValidationException;

class AnggaranController extends Controller
{
    public function index()
    {
        $anggarans = Anggaran::where(
            'school_profile_id',
            Auth::user()->school_profile_id ?? 0,
        )->get();

        return view('anggaran.index', compact('anggarans'));
    }

    public function create()
    {
        return view('anggaran.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|between:1900,9999',
            'jumlah' => 'required|integer|min:0|max:999999999999',
        ]);

        DB::transaction(function () use ($request) {
            SchoolProfile::whereKey(Auth::user()->school_profile_id)
                ->lockForUpdate()
                ->firstOrFail();
            if (
                Anggaran::where(
                    'school_profile_id',
                    Auth::user()->school_profile_id,
                )
                    ->where('tahun', $request->tahun)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'tahun' => 'Anggaran untuk tahun ini sudah tersedia.',
                ]);
            }
            Anggaran::create([
                'user_id' => Auth::id(),

                'school_profile_id' => Auth::user()->school_profile_id ?? 0,

                'tahun' => $request->tahun,

                'jumlah' => $request->jumlah,
            ]);
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Anggaran berhasil ditambahkan');
    }
}
