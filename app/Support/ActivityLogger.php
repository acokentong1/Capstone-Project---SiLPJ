<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Bapb;
use App\Models\Belanja;
use App\Models\Kwitansi;
use App\Models\NotaPesanan;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ActivityLogger
{
    private static ?bool $tableReady = null;

    public static function record(
        int $userId,
        string $action,
        string $description,
        ?Model $subject = null,
        array $metadata = []
    ): void {
        if (! self::tableReady()) {
            return;
        }

        $request = app()->bound('request') ? request() : null;

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }

    public static function recordModel(Model $model, string $event): void
    {
        // Setelah akun terhapus, foreign key activity_logs tidak lagi menerima user_id tersebut.
        if ($model instanceof User && $event === 'deleted') {
            return;
        }

        $userId = self::resolveUserId($model);

        if (! $userId) {
            return;
        }

        [$action, $description] = self::describeModelEvent($model, $event);
        $metadata = [];

        if ($event === 'updated') {
            $excluded = ['updated_at', 'password', 'remember_token', 'email_verified_at'];
            $changes = array_values(array_diff(array_keys($model->getChanges()), $excluded));

            if ($changes !== []) {
                $metadata['changed_fields'] = $changes;
            }
        }

        self::record($userId, $action, $description, $model, $metadata);
    }

    private static function resolveUserId(Model $model): ?int
    {
        if ($model instanceof User) {
            return (int) $model->getKey();
        }

        if (isset($model->user_id)) {
            return (int) $model->user_id;
        }

        if ($model instanceof Kwitansi) {
            $userId = $model->relationLoaded('belanja')
                ? $model->belanja?->user_id
                : Belanja::query()->whereKey($model->belanja_id)->value('user_id');

            return $userId ? (int) $userId : null;
        }

        return null;
    }

    private static function describeModelEvent(Model $model, string $event): array
    {
        $verb = match ($event) {
            'created' => 'Menambahkan',
            'updated' => 'Memperbarui',
            'deleted' => 'Menghapus',
            default => ucfirst($event),
        };

        if ($model instanceof Belanja) {
            return [$event === 'created' ? 'create' : ($event === 'updated' ? 'update' : 'delete'),
                "{$verb} transaksi belanja: {$model->uraian}"];
        }

        if ($model instanceof NotaPesanan) {
            return [$event === 'created' ? 'create' : ($event === 'updated' ? 'update' : 'delete'),
                "{$verb} Nota Pesanan {$model->nomor_nota}"];
        }

        if ($model instanceof Kwitansi) {
            return [$event === 'created' ? 'create' : ($event === 'updated' ? 'update' : 'delete'),
                "{$verb} Kwitansi {$model->nomor_kwitansi}"];
        }

        if ($model instanceof Bapb) {
            return [$event === 'created' ? 'create' : ($event === 'updated' ? 'update' : 'delete'),
                "{$verb} BAPB {$model->nomor_bapb}"];
        }

        if ($model instanceof SchoolProfile) {
            $name = $model->nama_sekolah ?: 'sekolah';
            return [$event === 'created' ? 'create' : ($event === 'updated' ? 'update' : 'delete'),
                "{$verb} profil sekolah: {$name}"];
        }

        if ($model instanceof User) {
            $description = match ($event) {
                'created' => 'Membuat akun SILPJ',
                'updated' => 'Memperbarui profil akun',
                'deleted' => 'Menghapus akun SILPJ',
                default => "{$verb} akun SILPJ",
            };

            return [$event === 'created' ? 'create' : ($event === 'updated' ? 'update' : 'delete'), $description];
        }

        return [$event, "{$verb} data " . class_basename($model)];
    }

    private static function tableReady(): bool
    {
        if (self::$tableReady !== null) {
            return self::$tableReady;
        }

        try {
            return self::$tableReady = Schema::hasTable('activity_logs');
        } catch (\Throwable) {
            return self::$tableReady = false;
        }
    }
}
