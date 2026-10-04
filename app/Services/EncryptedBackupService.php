<?php

namespace App\Services;

use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use Throwable;

class EncryptedBackupService
{
    private const FORMAT = 'SILPJ_ENCRYPTED_BACKUP';
    private const FORMAT_VERSION = 2;
    private const SUPPORTED_VERSIONS = [1, 2];

    /**
     * Tabel inti SILPJ yang aman untuk dibawa ke backup aplikasi.
     * Cache, sessions, jobs, migrations dan token reset sengaja tidak disertakan.
     */
    private const TABLES = [
        'users',
        'school_profiles',
        'belanjas',
        'nota_pesanans',
        'kwitansis',
        'bapbs',
        'activity_logs',
    ];

    private const DELETE_ORDER = [
        'activity_logs',
        'bapbs',
        'kwitansis',
        'nota_pesanans',
        'school_profiles',
        'belanjas',
        'users',
    ];

    private const REQUIRED_COLUMNS = [
        'users' => ['id', 'name', 'email', 'password'],
        'school_profiles' => ['id', 'user_id', 'nama_sekolah'],
        'belanjas' => ['id', 'user_id', 'tanggal', 'uraian', 'jumlah', 'harga_satuan', 'total'],
        'nota_pesanans' => ['id', 'user_id', 'belanja_id', 'nomor_nota', 'tanggal_nota'],
        'kwitansis' => ['id', 'belanja_id', 'nomor_kwitansi', 'tanggal_kwitansi', 'penerima', 'jumlah_uang'],
        'bapbs' => ['id', 'user_id', 'belanja_id', 'nomor_bapb', 'tanggal_bapb'],
        'activity_logs' => ['id', 'user_id', 'action', 'description'],
    ];

    public function createEncryptedPayload(): array
    {
        $payload = $this->createPayloadArray();
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $encrypted = Crypt::encryptString($json);

        return [
            'contents' => $encrypted,
            'counts' => $payload['counts'],
            'bytes' => strlen($encrypted),
            'created_at' => $payload['created_at'],
        ];
    }

    public function createPayloadArray(): array
    {
        $tables = [];
        $counts = [];
        $schema = [];

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)
                ->orderBy('id')
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();

            $tables[$table] = $rows;
            $counts[$table] = count($rows);
            $schema[$table] = Schema::getColumnListing($table);
        }

        if ($tables === []) {
            throw new RuntimeException('Tidak ada tabel SILPJ yang dapat dibackup.');
        }

        return [
            'format' => self::FORMAT,
            'format_version' => self::FORMAT_VERSION,
            'created_at' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'app_url' => config('app.url'),
            'database_driver' => DB::connection()->getDriverName(),
            'schema' => $schema,
            'counts' => $counts,
            'tables' => $tables,
        ];
    }

    public function tableCounts(): array
    {
        $counts = [];

        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table)) {
                $counts[$table] = DB::table($table)->count();
            }
        }

        return $counts;
    }

    public function inspectEncryptedContents(string $contents, ?string $currentAdminEmail = null): array
    {
        try {
            $json = Crypt::decryptString(trim($contents));
        } catch (DecryptException) {
            throw new RuntimeException('File backup tidak dapat didekripsi. Pastikan file berasal dari instalasi SILPJ dengan APP_KEY yang sama.');
        }

        try {
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('Isi backup rusak atau bukan format backup SILPJ yang valid.');
        }

        if (! is_array($payload)) {
            throw new RuntimeException('Struktur backup SILPJ tidak valid.');
        }

        $this->validatePayload($payload);

        $counts = [];
        foreach (self::TABLES as $table) {
            $counts[$table] = count($payload['tables'][$table] ?? []);
        }

        $users = $payload['tables']['users'] ?? [];
        $currentAdminFound = $currentAdminEmail === null;
        $activeAdmins = 0;

        foreach ($users as $user) {
            $role = $user['role'] ?? 'user';
            $isActive = array_key_exists('is_active', $user) ? (bool) $user['is_active'] : true;

            if ($role === 'admin' && $isActive) {
                $activeAdmins++;
            }

            if ($currentAdminEmail !== null && strcasecmp((string) ($user['email'] ?? ''), $currentAdminEmail) === 0) {
                $currentAdminFound = true;
            }
        }

        $warnings = [];
        if (($payload['format_version'] ?? 1) < self::FORMAT_VERSION) {
            $warnings[] = 'Backup menggunakan format lama. SILPJ akan melakukan normalisasi kolom yang aman saat restore.';
        }
        if ($activeAdmins === 0) {
            $warnings[] = 'Backup tidak memiliki admin aktif. Saat restore, akun admin yang menjalankan proses akan tetap dipertahankan sebagai admin aktif.';
        }
        if ($currentAdminEmail !== null && ! $currentAdminFound) {
            $warnings[] = 'Email admin yang sedang login tidak ditemukan di backup. Restore tidak dapat dijalankan untuk mencegah kehilangan akses.';
        }

        return [
            'payload' => $payload,
            'preview' => [
                'format_version' => (int) ($payload['format_version'] ?? 1),
                'created_at' => $payload['created_at'] ?? null,
                'timezone' => $payload['timezone'] ?? null,
                'database_driver' => $payload['database_driver'] ?? null,
                'counts' => $counts,
                'total_records' => array_sum($counts),
                'active_admins' => $activeAdmins,
                'current_admin_found' => $currentAdminFound,
                'warnings' => $warnings,
            ],
        ];
    }

    public function storeSafetyBackup(): array
    {
        $backup = $this->createEncryptedPayload();
        $filename = 'SILPJ-pre-restore-' . now()->format('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.silpjbackup';
        $path = 'silpj/safety-backups/' . $filename;

        if (! Storage::disk('local')->put($path, $backup['contents'])) {
            throw new RuntimeException('Backup pengaman sebelum restore tidak dapat disimpan. Restore dibatalkan.');
        }

        return [
            'filename' => $filename,
            'path' => $path,
            'counts' => $backup['counts'],
            'bytes' => $backup['bytes'],
        ];
    }

    public function listSafetyBackups(int $limit = 10): array
    {
        $disk = Storage::disk('local');
        $files = collect($disk->files('silpj/safety-backups'))
            ->filter(fn (string $path) => str_ends_with($path, '.silpjbackup'))
            ->map(function (string $path) use ($disk): array {
                return [
                    'path' => $path,
                    'filename' => basename($path),
                    'bytes' => $disk->size($path),
                    'modified_at' => $disk->lastModified($path),
                ];
            })
            ->sortByDesc('modified_at')
            ->take($limit)
            ->values()
            ->all();

        return $files;
    }

    public function restorePayload(array $payload, User $actor): array
    {
        $this->validatePayload($payload);

        $actorEmail = (string) $actor->email;
        $actorPassword = (string) $actor->getAuthPassword();
        $actorRowFound = false;

        foreach ($payload['tables']['users'] as &$userRow) {
            if (strcasecmp((string) ($userRow['email'] ?? ''), $actorEmail) === 0) {
                $actorRowFound = true;
                $userRow['password'] = $actorPassword;
                $userRow['role'] = 'admin';
                $userRow['is_active'] = 1;
                $userRow['remember_token'] = null;
                break;
            }
        }
        unset($userRow);

        if (! $actorRowFound) {
            throw new RuntimeException('Restore dibatalkan karena akun admin yang sedang digunakan tidak ada di dalam backup.');
        }

        $counts = [];

        DB::transaction(function () use ($payload, &$counts): void {
            foreach (self::DELETE_ORDER as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }

            foreach (self::TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $rows = $this->normalizeRowsForCurrentSchema($table, $payload['tables'][$table] ?? []);
                $counts[$table] = count($rows);

                foreach (array_chunk($rows, 250) as $chunk) {
                    if ($chunk !== []) {
                        DB::table($table)->insert($chunk);
                    }
                }
            }

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->delete();
            }

            $this->syncPostgresSequences();
        }, 3);

        $restoredActor = User::query()->where('email', $actorEmail)->first();
        if ($restoredActor) {
            ActivityLogger::record(
                (int) $restoredActor->id,
                'restore',
                'Memulihkan database SILPJ dari backup terenkripsi',
                null,
                ['record_counts' => $counts]
            );
        }

        return ['counts' => $counts, 'restored_actor' => $restoredActor];
    }

    private function validatePayload(array $payload): void
    {
        if (($payload['format'] ?? null) !== self::FORMAT) {
            throw new RuntimeException('File bukan backup SILPJ yang dikenali.');
        }

        $version = (int) ($payload['format_version'] ?? 1);
        if (! in_array($version, self::SUPPORTED_VERSIONS, true)) {
            throw new RuntimeException("Versi format backup {$version} belum didukung oleh aplikasi ini.");
        }

        if (! isset($payload['tables']) || ! is_array($payload['tables'])) {
            throw new RuntimeException('Backup tidak memiliki struktur tabel yang valid.');
        }

        foreach (self::TABLES as $table) {
            if (! array_key_exists($table, $payload['tables']) || ! is_array($payload['tables'][$table])) {
                throw new RuntimeException("Backup tidak lengkap. Tabel {$table} tidak ditemukan.");
            }

            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Database aplikasi saat ini belum memiliki tabel {$table}. Jalankan migration terbaru sebelum restore.");
            }

            $currentColumns = Schema::getColumnListing($table);
            $currentColumnMap = array_fill_keys($currentColumns, true);

            foreach ($payload['tables'][$table] as $index => $row) {
                if (! is_array($row)) {
                    throw new RuntimeException("Baris ke-" . ($index + 1) . " pada tabel {$table} tidak valid.");
                }

                foreach (self::REQUIRED_COLUMNS[$table] as $required) {
                    if (! array_key_exists($required, $row)) {
                        throw new RuntimeException("Kolom wajib {$table}.{$required} tidak ditemukan pada backup.");
                    }
                }

                foreach (array_keys($row) as $column) {
                    if (! isset($currentColumnMap[$column])) {
                        throw new RuntimeException("Backup menggunakan kolom {$table}.{$column} yang belum tersedia pada aplikasi ini. Perbarui aplikasi sebelum restore.");
                    }
                }
            }
        }

        if ($version >= 2 && isset($payload['schema']) && is_array($payload['schema'])) {
            foreach ($payload['schema'] as $table => $columns) {
                if (! in_array($table, self::TABLES, true) || ! is_array($columns)) {
                    continue;
                }

                $currentColumns = Schema::getColumnListing($table);
                $unknown = array_diff($columns, $currentColumns);
                if ($unknown !== []) {
                    throw new RuntimeException('Schema backup lebih baru daripada aplikasi ini pada tabel ' . $table . '.');
                }
            }
        }

        $this->validateRelations($payload['tables']);
    }

    private function validateRelations(array $tables): void
    {
        $userIds = $this->uniqueIds($tables['users'], 'users');
        $belanjaIds = $this->uniqueIds($tables['belanjas'], 'belanjas');
        $this->uniqueIds($tables['school_profiles'], 'school_profiles');
        $this->uniqueIds($tables['nota_pesanans'], 'nota_pesanans');
        $this->uniqueIds($tables['kwitansis'], 'kwitansis');
        $this->uniqueIds($tables['bapbs'], 'bapbs');
        $this->uniqueIds($tables['activity_logs'], 'activity_logs');

        $emails = [];
        foreach ($tables['users'] as $row) {
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '') {
                throw new RuntimeException('Backup memiliki akun tanpa email.');
            }
            if (isset($emails[$email])) {
                throw new RuntimeException("Backup memiliki email akun ganda: {$email}.");
            }
            $emails[$email] = true;
        }

        $schoolOwners = [];
        foreach ($tables['school_profiles'] as $row) {
            $userId = (string) $row['user_id'];
            $this->assertExists($userIds, $userId, 'Profil sekolah mengacu ke user yang tidak ada.');
            if (isset($schoolOwners[$userId])) {
                throw new RuntimeException('Backup memiliki lebih dari satu profil sekolah untuk user yang sama.');
            }
            $schoolOwners[$userId] = true;
        }

        $belanjaOwner = [];
        foreach ($tables['belanjas'] as $row) {
            $userId = (string) $row['user_id'];
            $this->assertExists($userIds, $userId, 'Data belanja mengacu ke user yang tidak ada.');
            $belanjaOwner[(string) $row['id']] = $userId;
        }

        $notaBelanja = [];
        foreach ($tables['nota_pesanans'] as $row) {
            $belanjaId = (string) $row['belanja_id'];
            $userId = (string) $row['user_id'];
            $this->assertExists($belanjaIds, $belanjaId, 'Nota Pesanan mengacu ke transaksi yang tidak ada.');
            $this->assertExists($userIds, $userId, 'Nota Pesanan mengacu ke user yang tidak ada.');
            if (($belanjaOwner[$belanjaId] ?? null) !== $userId) {
                throw new RuntimeException('Pemilik Nota Pesanan tidak konsisten dengan pemilik transaksi.');
            }
            if (isset($notaBelanja[$belanjaId])) {
                throw new RuntimeException('Backup memiliki lebih dari satu Nota Pesanan untuk transaksi yang sama.');
            }
            $notaBelanja[$belanjaId] = true;
        }

        $kwitansiBelanja = [];
        foreach ($tables['kwitansis'] as $row) {
            $belanjaId = (string) $row['belanja_id'];
            $this->assertExists($belanjaIds, $belanjaId, 'Kwitansi mengacu ke transaksi yang tidak ada.');
            if (isset($kwitansiBelanja[$belanjaId])) {
                throw new RuntimeException('Backup memiliki lebih dari satu Kwitansi untuk transaksi yang sama.');
            }
            $kwitansiBelanja[$belanjaId] = true;
        }

        $bapbBelanja = [];
        foreach ($tables['bapbs'] as $row) {
            $belanjaId = (string) $row['belanja_id'];
            $userId = (string) $row['user_id'];
            $this->assertExists($belanjaIds, $belanjaId, 'BAPB mengacu ke transaksi yang tidak ada.');
            $this->assertExists($userIds, $userId, 'BAPB mengacu ke user yang tidak ada.');
            if (($belanjaOwner[$belanjaId] ?? null) !== $userId) {
                throw new RuntimeException('Pemilik BAPB tidak konsisten dengan pemilik transaksi.');
            }
            if (isset($bapbBelanja[$belanjaId])) {
                throw new RuntimeException('Backup memiliki lebih dari satu BAPB untuk transaksi yang sama.');
            }
            $bapbBelanja[$belanjaId] = true;
        }

        foreach ($tables['activity_logs'] as $row) {
            $this->assertExists($userIds, (string) $row['user_id'], 'Activity Log mengacu ke user yang tidak ada.');
        }
    }

    private function uniqueIds(array $rows, string $table): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (string) ($row['id'] ?? '');
            if ($id === '') {
                throw new RuntimeException("Backup memiliki baris {$table} tanpa ID.");
            }
            if (isset($ids[$id])) {
                throw new RuntimeException("Backup memiliki ID ganda pada tabel {$table}.");
            }
            $ids[$id] = true;
        }

        return $ids;
    }

    private function assertExists(array $ids, string $id, string $message): void
    {
        if (! isset($ids[$id])) {
            throw new RuntimeException($message);
        }
    }

    private function normalizeRowsForCurrentSchema(string $table, array $rows): array
    {
        $columns = array_fill_keys(Schema::getColumnListing($table), true);

        return array_map(function (array $row) use ($table, $columns): array {
            if ($table === 'users') {
                $row['role'] = $row['role'] ?? 'user';
                $row['is_active'] = array_key_exists('is_active', $row) ? (int) ((bool) $row['is_active']) : 1;
            }

            return array_intersect_key($row, $columns);
        }, $rows);
    }

    private function syncPostgresSequences(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::TABLES as $table) {
            if (! Schema::hasColumn($table, 'id')) {
                continue;
            }

            DB::statement(
                "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM {$table}"
            );
        }
    }
}
