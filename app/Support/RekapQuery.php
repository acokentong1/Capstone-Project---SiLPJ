<?php

namespace App\Support;

use App\Models\Belanja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RekapQuery
{
    public static function filters(Request $request): array
    {
        $filters = [
            'tahun' =>
                $request->input('tahun') ?:
                $request->input('tahun_anggaran') ?:
                $request->input('year') ?:
                now()->year,
            'bulan' => $request->input('bulan') ?: $request->input('month'),
            'kategori' => $request->input('kategori'),
        ];
        return validator($filters, [
            'tahun' => ['required', 'integer', 'between:1900,9999'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'kategori' => ['nullable', 'in:Barang,Jasa'],
        ])->validate();
    }

    public static function query(int $schoolId, array $filters): Builder
    {
        return Belanja::where('school_profile_id', $schoolId)
            ->where('tahun_anggaran', $filters['tahun'])
            ->when(
                $filters['bulan'] ?? null,
                fn($q, $month) => $q->whereMonth('tanggal', $month),
            )
            ->when(
                $filters['kategori'] ?? null,
                fn($q, $category) => $q->where('kategori', $category),
            );
    }
}
