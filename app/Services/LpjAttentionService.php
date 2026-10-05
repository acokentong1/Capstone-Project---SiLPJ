<?php

namespace App\Services;

use App\Models\Belanja;
use Illuminate\Database\Eloquent\Builder;

class LpjAttentionService
{
    public function incompleteQuery(int $userId): Builder
    {
        return Belanja::query()
            ->where('user_id', $userId)
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
            'age_7_plus' => (clone $base)->whereDate('tanggal', '<=', $sevenDaysAgo)->count(),
            'age_14_plus' => (clone $base)->whereDate('tanggal', '<=', $fourteenDaysAgo)->count(),
            'missing_nota' => Belanja::where('user_id', $userId)->whereDoesntHave('notaPesanan')->count(),
            'missing_kwitansi' => Belanja::where('user_id', $userId)->whereDoesntHave('kwitansi')->count(),
            'missing_bapb' => Belanja::where('user_id', $userId)->whereDoesntHave('bapb')->count(),
        ];
    }
}
