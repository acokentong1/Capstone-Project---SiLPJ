<?php
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\RekapPdfController;
use App\Http\Controllers\RekapExportController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AttentionController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BapbController;
use App\Http\Controllers\BelanjaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KwitansiController;
use App\Http\Controllers\LaporanLpjController;
use App\Http\Controllers\NotaPesananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name(
        'dashboard',
    );

    Route::get('/profile', [ProfileController::class, 'edit'])->name(
        'profile.edit',
    );
    Route::patch('/profile', [ProfileController::class, 'update'])->name(
        'profile.update',
    );
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name(
        'profile.destroy',
    );

    Route::resource('belanja', BelanjaController::class)->except(['show'])
        ->middleware('role:admin,bendahara');

    Route::get('/nota-pesanan', [NotaPesananController::class, 'index'])->name(
        'nota-pesanan.index',
    );
    Route::get('/nota-pesanan/create/{id}', [
        NotaPesananController::class,
        'create',
    ])->name('nota-pesanan.create');
    Route::post('/nota-pesanan', [NotaPesananController::class, 'store'])->name(
        'nota-pesanan.store',
    );
    Route::get('/nota-pesanan/{id}', [
        NotaPesananController::class,
        'show',
    ])->name('nota-pesanan.show');

    Route::get('/kwitansi', [KwitansiController::class, 'index'])->name(
        'kwitansi.index',
    );
    Route::get('/kwitansi/create/{id}', [
        KwitansiController::class,
        'create',
    ])->name('kwitansi.create');
    Route::post('/kwitansi', [KwitansiController::class, 'store'])->name(
        'kwitansi.store',
    );
    Route::get('/kwitansi/{id}', [KwitansiController::class, 'show'])->name(
        'kwitansi.show',
    );

    Route::get('/bapb', [BapbController::class, 'index'])->name('bapb.index');
    Route::get('/bapb/create/{id}', [BapbController::class, 'create'])->name(
        'bapb.create',
    );
    Route::post('/bapb', [BapbController::class, 'store'])->name('bapb.store');
    Route::get('/bapb/{id}', [BapbController::class, 'show'])->name(
        'bapb.show',
    );

    Route::get('/laporan-lpj', [LaporanLpjController::class, 'index'])->name(
        'laporan-lpj.index',
    );
    Route::get('/laporan-lpj/export-csv', [
        LaporanLpjController::class,
        'exportCsv',
    ])->name('laporan-lpj.export-csv');

    Route::resource('school-profile', SchoolProfileController::class)->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
    ]);

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name(
        'activity-logs.index',
    );

    Route::get('/perhatian', [AttentionController::class, 'index'])->name(
        'attention.index',
    );

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])->name(
                'dashboard',
            );
            Route::get('/monitoring', [
                MonitoringController::class,
                'index',
            ])->name('monitoring.index');

            Route::get('/users', [
                UserManagementController::class,
                'index',
            ])->name('users.index');
            Route::get('/users/{user}', [
                UserManagementController::class,
                'show',
            ])->name('users.show');
            Route::patch('/users/{user}/role', [
                UserManagementController::class,
                'updateRole',
            ])->name('users.role');
            Route::patch('/users/{user}/status', [
                UserManagementController::class,
                'updateStatus',
            ])->name('users.status');

            Route::get('/backups', [BackupController::class, 'index'])->name(
                'backups.index',
            );

            Route::get('/backups/download', [
                BackupController::class,
                'download',
            ])
                ->middleware('password.confirm')
                ->name('backups.download');

            Route::post('/backups/restore/preview', [
                BackupController::class,
                'previewRestore',
            ])
                ->middleware('throttle:5,1')
                ->name('backups.restore.preview');
            Route::get('/backups/restore/{token}', [
                BackupController::class,
                'showRestore',
            ])->name('backups.restore.show');
            Route::post('/backups/restore/{token}', [
                BackupController::class,
                'executeRestore',
            ])
                ->middleware('throttle:3,1')
                ->name('backups.restore.execute');
            Route::delete('/backups/restore/{token}', [
                BackupController::class,
                'cancelRestore',
            ])->name('backups.restore.cancel');

            Route::get('/backups/safety/{filename}', [
                BackupController::class,
                'downloadSafetyBackup',
            ])
                ->middleware('password.confirm')
                ->where('filename', '[A-Za-z0-9._-]+')
                ->name('backups.safety.download');

            Route::get('/rekap-keuangan', [
                RekapController::class,
                'index',
            ])->name('rekap.index');
        });
    Route::middleware(['auth', 'active'])->group(function () {
        Route::get('/rekap-keuangan', [RekapController::class, 'index'])->name(
            'rekap.index',
        );
        Route::get('/rekap-keuangan', [RekapController::class, 'index'])
            ->middleware(['auth', 'active'])
            ->name('rekap.index');
        Route::get('/rekap-keuangan/export', [
            RekapController::class,
            'export',
        ])->name('rekap.export');
        Route::get('/rekap-keuangan', [RekapController::class, 'index'])
            ->middleware(['auth', 'active'])
            ->name('rekap.index');

        Route::get('/rekap-keuangan/export-excel', [
            RekapExportController::class,
            'excel',
        ])->name('rekap.export.excel');

        Route::get('/rekap-keuangan/export-pdf', [
            RekapPdfController::class,
            'pdf',
        ]);

        Route::resource('anggaran', AnggaranController::class)->except([
            'show',
        ])->middleware('role:admin,bendahara');

        Route::get('/anggaran/create', [
            AnggaranController::class,
            'create',
        ])->middleware('role:admin,bendahara')->name('anggaran.create');
        Route::post('/anggaran', [AnggaranController::class, 'store'])
            ->middleware('role:admin,bendahara')
            ->name('anggaran.store');
    });
});

require __DIR__ . '/auth.php';
