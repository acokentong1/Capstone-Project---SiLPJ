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
use App\Http\Controllers\LpjReviewController;

Route::middleware('guest')->group(function () {
    Route::get('/pengaturan-awal', [\App\Http\Controllers\SetupController::class, 'create'])->name('setup.create');
    Route::post('/pengaturan-awal', [\App\Http\Controllers\SetupController::class, 'store'])->middleware('throttle:6,1')->name('setup.store');
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::delete('/dokumen/{kind}/{id}', [\App\Http\Controllers\DocumentDeletionController::class, 'destroy'])
        ->whereIn('kind', ['kwitansi', 'nota-pesanan', 'bapb'])
        ->whereNumber('id')->middleware('role:admin,bendahara')->name('documents.destroy');

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

    Route::get('/belanja', [BelanjaController::class, 'index'])->name(
        'belanja.index',
    );
    Route::resource('belanja', BelanjaController::class)
        ->except(['show', 'index'])
        ->middleware('role:admin,bendahara');

    Route::get('/nota-pesanan', [NotaPesananController::class, 'index'])->name(
        'nota-pesanan.index',
    );
    Route::get('/nota-pesanan/create/{id}', [
        NotaPesananController::class,
        'create',
    ])
        ->name('nota-pesanan.create')
        ->middleware('role:admin,bendahara');
    Route::post('/nota-pesanan', [NotaPesananController::class, 'store'])
        ->name('nota-pesanan.store')
        ->middleware('role:admin,bendahara');
    Route::get('/nota-pesanan/{id}', [
        NotaPesananController::class,
        'show',
    ])->name('nota-pesanan.show');

    Route::get('/kwitansi', [KwitansiController::class, 'index'])->name(
        'kwitansi.index',
    );
    Route::get('/kwitansi/create/{id}', [KwitansiController::class, 'create'])
        ->name('kwitansi.create')
        ->middleware('role:admin,bendahara');
    Route::post('/kwitansi', [KwitansiController::class, 'store'])
        ->name('kwitansi.store')
        ->middleware('role:admin,bendahara');
    Route::get('/kwitansi/{id}', [KwitansiController::class, 'show'])->name(
        'kwitansi.show',
    );

    Route::get('/bapb', [BapbController::class, 'index'])->name('bapb.index');
    Route::get('/bapb/create/{id}', [BapbController::class, 'create'])
        ->name('bapb.create')
        ->middleware('role:admin,bendahara');
    Route::post('/bapb', [BapbController::class, 'store'])
        ->name('bapb.store')
        ->middleware('role:admin,bendahara');
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

    Route::get('/school-profile', [
        SchoolProfileController::class,
        'index',
    ])->name('school-profile.index');
    Route::resource('school-profile', SchoolProfileController::class)
        ->only(['create', 'store', 'edit', 'update'])
        ->middleware('role:admin,bendahara');

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
            Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
            Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
            Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
            Route::put('/users/{user}/password', [UserManagementController::class, 'resetPassword'])->middleware('throttle:6,1')->name('users.password');
            Route::get('/users/{user}', [
                UserManagementController::class,
                'show',
            ])->name('users.show');
            Route::patch('/users/{user}/school', [
                UserManagementController::class,
                'updateSchool',
            ])->name('users.school');
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
    Route::get('/rekap-keuangan', [RekapController::class, 'index'])->name(
        'rekap.index',
    );
    Route::get('/rekap-keuangan/export', [
        RekapExportController::class,
        'excel',
    ])->name('rekap.export');
    Route::get('/rekap-keuangan/export-excel', [
        RekapExportController::class,
        'excel',
    ])->name('rekap.export.excel');
    Route::get('/rekap-keuangan/export-pdf', [
        RekapPdfController::class,
        'pdf',
    ])->name('rekap.export.pdf');

    Route::get('/anggaran', [AnggaranController::class, 'index'])->name(
        'anggaran.index',
    );
    Route::resource('anggaran', AnggaranController::class)
        ->only(['create', 'store'])
        ->middleware('role:admin,bendahara');

    Route::get('/pemeriksaan-lpj', [LpjReviewController::class, 'index'])->name(
        'lpj-review.index',
    );
    Route::get('/pemeriksaan-lpj/{id}', [LpjReviewController::class, 'show'])
        ->whereNumber('id')
        ->name('lpj-review.show');
    Route::post('/pemeriksaan-lpj/{id}/status', [
        LpjReviewController::class,
        'transition',
    ])
        ->whereNumber('id')
        ->middleware('role:admin,bendahara,kepala_sekolah')
        ->name('lpj-review.transition');
    Route::post('/pemeriksaan-lpj/{id}/lampiran', [
        LpjReviewController::class,
        'upload',
    ])
        ->whereNumber('id')
        ->middleware('role:admin,bendahara')
        ->name('lpj-review.upload');
    Route::get('/lampiran-lpj/{attachment}', [
        LpjReviewController::class,
        'download',
    ])
        ->whereNumber('attachment')
        ->name('lpj-review.download');
    Route::delete('/pemeriksaan-lpj/{id}/lampiran/{attachment}', [
        LpjReviewController::class,
        'remove',
    ])
        ->whereNumber(['id', 'attachment'])
        ->middleware('role:admin,bendahara')
        ->name('lpj-review.remove');

    Route::get('/laporan-lpj/export-excel', [
        LaporanLpjController::class,
        'exportExcel',
    ])->name('laporan-lpj.excel');
});

require __DIR__ . '/auth.php';
