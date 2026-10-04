<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use DateTime;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);

        $query = ActivityLog::query()
            ->where('user_id', $request->user()->id);

        if ($filters['q'] !== '') {
            $search = $filters['q'];
            $query->where('description', 'like', "%{$search}%");
        }

        if ($filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }

        if ($filters['tanggal_mulai']) {
            $query->whereDate('created_at', '>=', $filters['tanggal_mulai']);
        }

        if ($filters['tanggal_selesai']) {
            $query->whereDate('created_at', '<=', $filters['tanggal_selesai']);
        }

        $statsBase = clone $query;

        $logs = $query
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $statsBase)->count(),
            'hariIni' => (clone $statsBase)->whereDate('created_at', now()->toDateString())->count(),
            'perubahanData' => (clone $statsBase)->whereIn('action', ['create', 'update', 'delete'])->count(),
            'login' => (clone $statsBase)->where('action', 'login')->count(),
        ];

        return view('activity-logs.index', compact('logs', 'filters', 'stats'));
    }

    private function resolveFilters(Request $request): array
    {
        $q = trim((string) $request->query('q', ''));
        $allowedActions = ['login', 'login_failed', 'logout', 'create', 'update', 'delete', 'export'];
        $action = in_array($request->query('action'), $allowedActions, true)
            ? (string) $request->query('action')
            : '';

        $tanggalMulai = $this->normalizeDate($request->query('tanggal_mulai'));
        $tanggalSelesai = $this->normalizeDate($request->query('tanggal_selesai'));

        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai > $tanggalSelesai) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        return [
            'q' => $q,
            'action' => $action,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
        ];
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value
            ? $value
            : null;
    }
}
