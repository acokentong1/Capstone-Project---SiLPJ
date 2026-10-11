<?php

namespace App\Services;

use App\Models\Belanja;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LpjAttentionService
{
    public function incompleteQuery(int $userId): Builder
    {
        return Belanja::query()
            ->where(
                'school_profile_id',
                User::find($userId)?->school_profile_id ?? 0,
            )
            ->where(function (Builder $query): void {
                $query
                    ->whereDoesntHave('notaPesanan')
                    ->orWhereDoesntHave('kwitansi')
                    ->orWhereDoesntHave('bapb');
            });
    }

    public function notificationCount(int $userId): int
    {
        return (int) $this->incompleteQuery($userId)->count();
    }

    public function summary(int $userId): array
    {
        $today = now()->startOfDay();
        $sevenDaysAgo = $today->copy()->subDays(7)->toDateString();
        $fourteenDaysAgo = $today->copy()->subDays(14)->toDateString();

        $base = $this->incompleteQuery($userId);

        return [
            'total' => (clone $base)->count(),
            'age_7_plus' => (clone $base)
                ->whereDate('tanggal', '<=', $sevenDaysAgo)
                ->count(),
            'age_14_plus' => (clone $base)
                ->whereDate('tanggal', '<=', $fourteenDaysAgo)
                ->count(),
            'missing_nota' => Belanja::where(
                'school_profile_id',
                User::find($userId)?->school_profile_id ?? 0,
            )
                ->whereDoesntHave('notaPesanan')
                ->count(),
            'missing_kwitansi' => Belanja::where(
                'school_profile_id',
                User::find($userId)?->school_profile_id ?? 0,
            )
                ->whereDoesntHave('kwitansi')
                ->count(),
            'missing_bapb' => Belanja::where(
                'school_profile_id',
                User::find($userId)?->school_profile_id ?? 0,
            )
                ->whereDoesntHave('bapb')
                ->count(),
        ];
    }
}
