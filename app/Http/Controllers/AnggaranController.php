<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggaran;
use Illuminate\Support\Facades\Auth;

class AnggaranController extends Controller
{
    public function index()
    {
        $anggarans = Anggaran::where(
            'school_profile_id',
            Auth::user()->school_profile_id,
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
            'tahun' => 'required',
            'jumlah' => 'required',
        ]);

        Anggaran::create([
            'user_id' => Auth::id(),

            'school_profile_id' => Auth::user()->school_profile_id,

            'tahun' => $request->tahun,

            'jumlah' => $request->jumlah,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Anggaran berhasil ditambahkan');
    }
}
