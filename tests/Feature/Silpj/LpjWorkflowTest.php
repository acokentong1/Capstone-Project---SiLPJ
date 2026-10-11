<?php
namespace Tests\Feature\Silpj;

use App\Models\{Anggaran, Belanja, SchoolProfile, User};
use App\Services\EncryptedBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LpjWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function actor(
        string $role = 'bendahara',
        ?int $schoolId = null,
    ): User {
        $schoolId ??= SchoolProfile::create(['nama_sekolah' => 'Sekolah Uji'])
            ->id;
        return User::factory()->create([
            'role' => $role,
            'school_profile_id' => $schoolId,
        ]);
    }

    private function spending(User $user, bool $complete = false): Belanja
    {
        $item = Belanja::create([
            'user_id' => $user->id,
            'school_profile_id' => $user->school_profile_id,
            'tahun_anggaran' => 2026,
            'tanggal' => '2026-10-11',
            'nomor_bukti' => 'BKT-UJI',
            'uraian' => 'ATK',
            'kategori' => 'Barang',
            'jumlah' => 1,
            'harga_satuan' => 10000,
            'total' => 10000,
        ]);
        if ($complete) {
            $item
                ->notaPesanan()
                ->create([
                    'user_id' => $user->id,
                    'nomor_nota' => 'NP-' . $item->id,
                    'tanggal_nota' => '2026-10-11',
                    'status' => 'Selesai',
                ]);
            $item
                ->kwitansi()
                ->create([
                    'nomor_kwitansi' => 'KW-' . $item->id,
                    'tanggal_kwitansi' => '2026-10-11',
                    'penerima' => 'Toko',
                    'jumlah_uang' => 10000,
                    'terbilang' => 'sepuluh ribu',
                ]);
            $item
                ->bapb()
                ->create([
                    'user_id' => $user->id,
                    'nomor_bapb' => 'BAPB-' . $item->id,
                    'tanggal_bapb' => '2026-10-11',
                    'hasil_pemeriksaan' => 'Baik dan sesuai',
                ]);
            $this->actingAs($user)
                ->post(route('lpj-review.upload', $item->id), [
                    'bukti' => $this->imageFile(),
                ])
                ->assertRedirect();
        }
        return $item;
    }

    private function imageFile(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'bukti.png',
            base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a6xkAAAAASUVORK5CYII=',
            ),
        );
    }

    private function transition(
        User $user,
        Belanja $item,
        string $action,
        ?string $reason = null,
    ) {
        return $this->actingAs($user)->postJson(
            route('lpj-review.transition', $item->id),
            compact('action', 'reason'),
        );
    }

    public function test_complete_lifecycle_and_locked_data(): void
    {
        $owner = $this->actor();
        $head = $this->actor('kepala_sekolah', $owner->school_profile_id);
        $item = $this->spending($owner, true);
        $this->transition($owner, $item, 'ajukan')->assertRedirect();
        $this->assertSame('diajukan', $item->fresh()->review_status);
        $this->actingAs($owner)
            ->postJson(route('lpj-review.upload', $item->id), [
                'bukti' => $this->imageFile(),
            ])
            ->assertUnprocessable();
        $this->transition($head, $item, 'setujui')->assertRedirect();
        $this->assertSame('disetujui', $item->fresh()->review_status);
        $this->transition(
            $head,
            $item,
            'buka',
            'Perbaiki rincian harga',
        )->assertRedirect();
        $this->assertSame('revisi', $item->fresh()->review_status);
        $this->assertNull($item->fresh()->approved_at);
        $this->assertDatabaseHas('lpj_events', [
            'belanja_id' => $item->id,
            'action' => 'buka',
            'reason' => 'Perbaiki rincian harga',
        ]);
    }

    public function test_admin_cannot_approve_own_transaction(): void
    {
        $admin = $this->actor('admin');
        $item = $this->spending($admin, true);
        $this->transition($admin, $item, 'ajukan')->assertRedirect();
        $this->transition($admin, $item, 'setujui')->assertForbidden();
    }

    public function test_incomplete_submission_and_invalid_transition_are_rejected(): void
    {
        $owner = $this->actor();
        $item = $this->spending($owner);
        $this->transition($owner, $item, 'ajukan')->assertUnprocessable();
        $head = $this->actor('kepala_sekolah', $owner->school_profile_id);
        $this->transition($head, $item, 'setujui')->assertUnprocessable();
        $this->assertSame('draf', $item->fresh()->review_status);
    }

    public function test_another_school_cannot_review_or_download_evidence(): void
    {
        $owner = $this->actor();
        $item = $this->spending($owner, true);
        $foreign = $this->actor('kepala_sekolah');
        $this->actingAs($foreign)
            ->get(route('lpj-review.show', $item->id))
            ->assertNotFound();
        $this->get(
            route('lpj-review.download', $item->attachments()->first()->id),
        )->assertNotFound();
        $this->transition($foreign, $item, 'setujui')->assertNotFound();
    }

    public function test_reader_and_head_cannot_create_financial_documents(): void
    {
        foreach (['user', 'kepala_sekolah'] as $role) {
            $actor = $this->actor($role);
            foreach (
                [
                    'belanja.store',
                    'nota-pesanan.store',
                    'kwitansi.store',
                    'bapb.store',
                    'anggaran.store',
                ]
                as $route
            ) {
                $this->actingAs($actor)
                    ->postJson(route($route), [])
                    ->assertForbidden();
            }
        }
    }

    public function test_revision_requires_a_reason_and_submitter_cannot_approve(): void
    {
        $owner = $this->actor();
        $item = $this->spending($owner, true);
        $head = $this->actor('kepala_sekolah', $owner->school_profile_id);
        $this->transition($owner, $item, 'ajukan')->assertRedirect();
        $this->transition($head, $item, 'revisi', '')->assertUnprocessable();
        $this->transition($owner, $item, 'setujui')->assertForbidden();
        $this->transition(
            $head,
            $item,
            'revisi',
            'Periksa kembali bukti',
        )->assertRedirect();
    }

    public function test_uploaded_file_type_limit_and_checksum(): void
    {
        $owner = $this->actor();
        $item = $this->spending($owner);
        $this->actingAs($owner)
            ->postJson(route('lpj-review.upload', $item->id), [
                'bukti' => UploadedFile::fake()->createWithContent(
                    'evil.php',
                    '<?php echo 1;',
                ),
            ])
            ->assertUnprocessable();
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('lpj-review.upload', $item->id), [
                'bukti' => $this->imageFile(),
            ])->assertRedirect();
        }
        $this->postJson(route('lpj-review.upload', $item->id), [
            'bukti' => $this->imageFile(),
        ])->assertUnprocessable();
        $file = $item->attachments()->first();
        $response = $this->get(route('lpj-review.download', $file->id))
            ->assertOk()
            ->assertDownload('bukti.png');
        $this->assertSame(
            $file->sha256,
            hash('sha256', $response->streamedContent()),
        );
    }

    public function test_edit_updates_kwitansi_and_records_before_and_after(): void
    {
        $owner = $this->actor();
        $item = $this->spending($owner, true);
        Anggaran::create([
            'user_id' => $owner->id,
            'school_profile_id' => $owner->school_profile_id,
            'tahun' => 2026,
            'jumlah' => 1000000,
        ]);
        $this->actingAs($owner)
            ->patch(route('belanja.update', $item->id), [
                'tanggal' => '2026-10-11',
                'tahun_anggaran' => 2026,
                'nomor_bukti' => 'BKT-UJI',
                'uraian' => 'ATK',
                'kategori' => 'Barang',
                'jumlah' => 2,
                'harga_satuan' => 10000,
                'reason' => 'Koreksi jumlah barang',
            ])
            ->assertRedirect(route('belanja.index'));
        $this->assertEquals(20000, $item->fresh()->kwitansi->jumlah_uang);
        $event = $item
            ->reviewEvents()
            ->where('action', 'ubah_belanja')
            ->firstOrFail();
        $this->assertEquals(10000, $event->details['before']['total']);
        $this->assertEquals(20000, $event->details['after']['total']);
    }

    public function test_locked_transaction_cannot_be_updated_or_deleted_by_direct_url(): void
    {
        $owner = $this->actor();
        $item = $this->spending($owner, true);
        $this->transition($owner, $item, 'ajukan')->assertRedirect();
        $this->actingAs($owner)
            ->patchJson(route('belanja.update', $item->id), [
                'tanggal' => '2026-10-11',
                'tahun_anggaran' => 2026,
                'nomor_bukti' => 'BKT-UJI',
                'uraian' => 'ATK',
                'kategori' => 'Barang',
                'jumlah' => 2,
                'harga_satuan' => 10000,
                'reason' => 'Percobaan perubahan',
            ])
            ->assertUnprocessable();
        $this->deleteJson(route('belanja.destroy', $item->id), [
            'reason' => 'Percobaan penghapusan',
        ])->assertUnprocessable();
        $this->assertEquals(10000, $item->fresh()->total);
    }

    public function test_account_with_financial_records_cannot_delete_itself(): void
    {
        $owner = $this->actor();
        $this->spending($owner);
        $this->actingAs($owner)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion');
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_backup_roundtrip_preserves_evidence_and_review_history(): void
    {
        $owner = $this->actor();
        $admin = $this->actor('admin', $owner->school_profile_id);
        $item = $this->spending($owner, true);
        $this->transition($owner, $item, 'ajukan')->assertRedirect();
        $this->transition($admin, $item, 'setujui')->assertRedirect();
        $service = app(EncryptedBackupService::class);
        $payload = $service->createPayloadArray();
        $checksum = $item->attachments()->first()->sha256;
        $this->assertNotEmpty($payload['tables']['lpj_events']);
        $service->restorePayload($payload, $admin);
        $this->assertSame('disetujui', $item->fresh()->review_status);
        $this->assertSame(
            $checksum,
            $item->fresh()->attachments()->first()->sha256,
        );
    }

    public function test_dashboard_and_report_totals_agree_for_same_year(): void
    {
        $owner = $this->actor();
        $this->spending($owner);
        $old = $this->spending($owner);
        $old->tahun_anggaran = 2025;
        $old->save();
        $this->actingAs($owner)
            ->get(route('dashboard', ['tahun' => 2026]))
            ->assertOk()
            ->assertViewHas('totalBelanja', 10000);
        $this->get(route('rekap.index', ['tahun' => 2026]))
            ->assertOk()
            ->assertViewHas('summary', fn($value) => $value['total'] == 10000);
    }
}
