<?php

namespace Tests\Feature;

use App\Models\Belanja;
use App\Models\User;
use App\Services\EncryptedBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDashboardRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_and_monitoring_are_admin_only(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.monitoring.index'))->assertForbidden();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin', 'is_active' => true])->saveQuietly();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.monitoring.index'))->assertOk();
    }

    public function test_restore_requires_current_admin_to_exist_in_backup(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('Password-123!'),
        ]);
        $admin->forceFill(['role' => 'admin', 'is_active' => true])->saveQuietly();

        $backup = app(EncryptedBackupService::class)->createEncryptedPayload();

        User::withoutEvents(function (): void {
            User::where('email', 'admin@example.com')->update(['email' => 'changed@example.com']);
        });

        $file = UploadedFile::fake()->createWithContent('backup.silpjbackup', $backup['contents']);

        $response = $this->actingAs($admin->fresh())
            ->post(route('admin.backups.restore.preview'), ['backup_file' => $file]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $token = basename(parse_url($location, PHP_URL_PATH));

        $restore = $this->actingAs($admin->fresh())
            ->post(route('admin.backups.restore.execute', $token), [
                'current_password' => 'Password-123!',
                'confirmation' => 'RESTORE SILPJ',
                'understand_logout' => '1',
            ]);

        $restore->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['email' => 'changed@example.com']);
    }

    public function test_restore_replaces_core_data_but_preserves_executing_admin_password(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('Password-Lama-123!'),
        ]);
        $admin->forceFill(['role' => 'admin', 'is_active' => true])->saveQuietly();

        Belanja::create([
            'user_id' => $admin->id,
            'tanggal' => '2026-10-05',
            'nomor_bukti' => 'BK-001',
            'uraian' => 'Data dari backup',
            'kategori' => 'Barang',
            'jumlah' => 1,
            'harga_satuan' => 100000,
            'total' => 100000,
        ]);

        $backup = app(EncryptedBackupService::class)->createEncryptedPayload();

        $admin->forceFill(['password' => Hash::make('Password-Sekarang-456!')])->saveQuietly();
        $extraUser = User::factory()->create(['email' => 'extra@example.com']);

        $file = UploadedFile::fake()->createWithContent('backup.silpjbackup', $backup['contents']);

        $preview = $this->actingAs($admin->fresh())
            ->post(route('admin.backups.restore.preview'), ['backup_file' => $file]);

        $preview->assertRedirect();
        $location = $preview->headers->get('Location');
        $token = basename(parse_url($location, PHP_URL_PATH));

        $restore = $this->actingAs($admin->fresh())
            ->post(route('admin.backups.restore.execute', $token), [
                'current_password' => 'Password-Sekarang-456!',
                'confirmation' => 'RESTORE SILPJ',
                'understand_logout' => '1',
            ]);

        $restore->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['email' => $extraUser->email]);
        $this->assertDatabaseHas('belanjas', ['uraian' => 'Data dari backup']);

        $restoredAdmin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertSame('admin', $restoredAdmin->role);
        $this->assertTrue($restoredAdmin->isActive());
        $this->assertTrue(Hash::check('Password-Sekarang-456!', $restoredAdmin->password));

        $this->assertNotEmpty(Storage::disk('local')->files('silpj/safety-backups'));
    }
}
