<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EncryptedBackupService;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController extends Controller
{
    private const PENDING_TTL_MINUTES = 30;

    public function index(EncryptedBackupService $backupService): View
    {
        $this->cleanupExpiredPendingFiles();

        $counts = $backupService->tableCounts();
        $safetyBackups = $backupService->listSafetyBackups();

        return view('admin.backups.index', compact('counts', 'safetyBackups'));
    }

    public function download(Request $request, EncryptedBackupService $backupService): Response
    {
        $backup = $backupService->createEncryptedPayload();
        $filename = 'SILPJ-backup-' . now()->format('Ymd-His') . '.silpjbackup';

        ActivityLogger::record(
            (int) $request->user()->id,
            'backup',
            'Mengunduh backup terenkripsi database SILPJ',
            null,
            [
                'filename' => $filename,
                'bytes' => $backup['bytes'],
                'record_counts' => $backup['counts'],
            ]
        );

        return response($backup['contents'], 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function previewRestore(Request $request, EncryptedBackupService $backupService): RedirectResponse
    {
        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'max:51200'],
        ], [
            'backup_file.required' => 'Pilih file backup SILPJ terlebih dahulu.',
            'backup_file.file' => 'File backup tidak valid.',
            'backup_file.max' => 'Ukuran file backup maksimal 50 MB.',
        ]);

        $uploaded = $validated['backup_file'];
        if (! str_ends_with(strtolower($uploaded->getClientOriginalName()), '.silpjbackup')) {
            return back()->with('error', 'File harus menggunakan ekstensi .silpjbackup.')->withInput();
        }

        $contents = file_get_contents($uploaded->getRealPath());
        if ($contents === false || trim($contents) === '') {
            return back()->with('error', 'File backup kosong atau tidak dapat dibaca.');
        }

        try {
            $inspection = $backupService->inspectEncryptedContents($contents, (string) $request->user()->email);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $token = Str::random(48);
        $path = 'silpj/restore-pending/' . $token . '.silpjbackup';

        if (! Storage::disk('local')->put($path, $contents)) {
            return back()->with('error', 'File preview restore tidak dapat disimpan sementara.');
        }

        Cache::put($this->cacheKey($token), [
            'user_id' => (int) $request->user()->id,
            'path' => $path,
            'original_name' => $uploaded->getClientOriginalName(),
            'preview' => $inspection['preview'],
            'created_at' => now()->toIso8601String(),
        ], now()->addMinutes(self::PENDING_TTL_MINUTES));

        ActivityLogger::record(
            (int) $request->user()->id,
            'restore_preview',
            'Memvalidasi file backup untuk preview restore',
            null,
            [
                'filename' => $uploaded->getClientOriginalName(),
                'record_counts' => $inspection['preview']['counts'],
            ]
        );

        return redirect()->route('admin.backups.restore.show', $token);
    }

    public function showRestore(Request $request, string $token): View|RedirectResponse
    {
        $pending = $this->pendingRestore($request, $token);
        if (! $pending) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Preview restore sudah kedaluwarsa atau tidak ditemukan. Unggah ulang file backup.');
        }

        return view('admin.backups.restore-preview', [
            'token' => $token,
            'pending' => $pending,
        ]);
    }

    public function executeRestore(
        Request $request,
        string $token,
        EncryptedBackupService $backupService
    ): RedirectResponse {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:RESTORE SILPJ'],
            'understand_logout' => ['accepted'],
        ], [
            'current_password.current_password' => 'Kata sandi admin tidak sesuai.',
            'confirmation.in' => 'Ketik persis RESTORE SILPJ untuk melanjutkan.',
            'understand_logout.accepted' => 'Konfirmasi bahwa seluruh sesi akan dikeluarkan setelah restore.',
        ]);

        $pending = $this->pendingRestore($request, $token);
        if (! $pending) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Preview restore sudah kedaluwarsa. Unggah ulang file backup.');
        }

        $contents = Storage::disk('local')->get($pending['path']);
        if (! is_string($contents) || trim($contents) === '') {
            return redirect()->route('admin.backups.index')
                ->with('error', 'File restore sementara tidak dapat dibaca. Unggah ulang backup.');
        }

        $actor = $request->user();
        $safetyBackup = null;

        try {
            $inspection = $backupService->inspectEncryptedContents($contents, (string) $actor->email);

            if (! ($inspection['preview']['current_admin_found'] ?? false)) {
                throw new RuntimeException('Restore dibatalkan karena akun admin yang sedang digunakan tidak ada di dalam backup.');
            }

            $safetyBackup = $backupService->storeSafetyBackup();

            Log::warning('SILPJ database restore dimulai.', [
                'admin_email' => $actor->email,
                'backup_file' => $pending['original_name'],
                'safety_backup' => $safetyBackup['filename'],
                'backup_created_at' => $inspection['preview']['created_at'] ?? null,
            ]);

            $backupService->restorePayload($inspection['payload'], $actor);
        } catch (Throwable $exception) {
            Log::error('SILPJ database restore gagal.', [
                'admin_email' => $actor?->email,
                'backup_file' => $pending['original_name'] ?? null,
                'safety_backup' => $safetyBackup['filename'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            $message = 'Restore gagal dan perubahan database dibatalkan: ' . $exception->getMessage();
            if ($safetyBackup) {
                $message .= ' Backup pengaman tersimpan sebagai ' . $safetyBackup['filename'] . '.';
            }

            return back()->with('error', $message);
        }

        Storage::disk('local')->delete($pending['path']);
        Cache::forget($this->cacheKey($token));

        Log::warning('SILPJ database restore selesai.', [
            'admin_email' => $actor->email,
            'backup_file' => $pending['original_name'],
            'safety_backup' => $safetyBackup['filename'] ?? null,
        ]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(
            'status',
            'Restore database berhasil. Demi keamanan semua sesi telah dikeluarkan. Silakan login kembali. Password admin yang menjalankan restore tetap menggunakan password sebelum restore.'
        );
    }

    public function cancelRestore(Request $request, string $token): RedirectResponse
    {
        $pending = $this->pendingRestore($request, $token);

        if ($pending) {
            Storage::disk('local')->delete($pending['path']);
            Cache::forget($this->cacheKey($token));
        }

        return redirect()->route('admin.backups.index')->with('success', 'Preview restore dibatalkan. Database tidak berubah.');
    }

    public function downloadSafetyBackup(
        Request $request,
        string $filename
    ): StreamedResponse|RedirectResponse {
        if (basename($filename) !== $filename || ! preg_match('/^[A-Za-z0-9._-]+\.silpjbackup$/', $filename)) {
            abort(404);
        }

        $path = 'silpj/safety-backups/' . $filename;
        if (! Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.backups.index')->with('error', 'Backup pengaman tidak ditemukan.');
        }

        ActivityLogger::record(
            (int) $request->user()->id,
            'backup',
            'Mengunduh backup pengaman sebelum restore',
            null,
            ['filename' => $filename]
        );

        return Storage::disk('local')->download($path, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    private function pendingRestore(Request $request, string $token): ?array
    {
        if (! preg_match('/^[A-Za-z0-9]{48}$/', $token)) {
            return null;
        }

        $pending = Cache::get($this->cacheKey($token));
        if (! is_array($pending) || (int) ($pending['user_id'] ?? 0) !== (int) $request->user()->id) {
            return null;
        }

        if (! isset($pending['path']) || ! Storage::disk('local')->exists($pending['path'])) {
            Cache::forget($this->cacheKey($token));
            return null;
        }

        return $pending;
    }

    private function cacheKey(string $token): string
    {
        return 'silpj_restore_preview_' . $token;
    }

    private function cleanupExpiredPendingFiles(): void
    {
        $disk = Storage::disk('local');
        $threshold = now()->subHours(2)->timestamp;

        foreach ($disk->files('silpj/restore-pending') as $path) {
            try {
                if ($disk->lastModified($path) < $threshold) {
                    $disk->delete($path);
                }
            } catch (Throwable) {
                // Cleanup bersifat best-effort dan tidak boleh mengganggu halaman backup.
            }
        }
    }
}
