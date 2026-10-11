<?php

namespace Tests\Feature\Silpj;

use App\Models\Belanja;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExportRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function schoolUser(): User
    {
        $school = SchoolProfile::create([
            'nama_sekolah' => 'Sekolah Uji',
            'npsn' => '00123456',
        ]);
        return User::factory()->create([
            'school_profile_id' => $school->id,
            'role' => 'bendahara',
        ]);
    }

    private function spending(User $user, array $attributes = []): Belanja
    {
        return Belanja::create(
            array_merge(
                [
                    'user_id' => $user->id,
                    'school_profile_id' => $user->school_profile_id,
                    'tanggal' => '2026-10-10',
                    'tahun_anggaran' => 2026,
                    'nomor_bukti' => '000123',
                    'uraian' => '=1+1',
                    'kategori' => 'Barang',
                    'jumlah' => 2,
                    'harga_satuan' => 25000,
                    'total' => 50000,
                ],
                $attributes,
            ),
        );
    }

    public function test_lpj_download_is_readable_xlsx_and_keeps_text_and_totals(): void
    {
        $user = $this->schoolUser();
        $this->spending($user);
        $this->spending($this->schoolUser(), ['uraian' => 'Sekolah lain']);
        $this->spending($user, ['tahun_anggaran' => 2025]);
        $response = $this->actingAs($user)->get(
            route('laporan-lpj.excel', ['tahun' => 2026]),
        );
        $response->assertOk()->assertDownload();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $book = IOFactory::load($path);
            $sheet = $book->getActiveSheet();
            $this->assertEquals(1, $sheet->getCell('E8')->getValue());
            $this->assertEquals(50000, $sheet->getCell('K8')->getValue());
            $this->assertSame('000123', $sheet->getCell('C18')->getValue());
            $this->assertSame('=1+1', $sheet->getCell('E18')->getValue());
            $this->assertSame('s', $sheet->getCell('E18')->getDataType());
            $book->disconnectWorksheets();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_empty_lpj_export_still_produces_valid_workbook(): void
    {
        $response = $this->actingAs($this->schoolUser())->get(
            route('laporan-lpj.excel'),
        );
        $response->assertOk()->assertDownload();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $book = IOFactory::load($path);
            $this->assertEquals(
                0,
                $book->getActiveSheet()->getCell('E8')->getValue(),
            );
            $book->disconnectWorksheets();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_rekap_page_and_excel_use_same_school_and_filters(): void
    {
        $user = $this->schoolUser();
        $colleague = User::factory()->create([
            'school_profile_id' => $user->school_profile_id,
        ]);
        $included = $this->spending($colleague, [
            'uraian' => 'Belanja bersama',
        ]);
        $this->spending($user, ['tahun_anggaran' => 2025]);
        $this->spending($user, ['tanggal' => '2026-09-10']);
        $this->spending($user, ['kategori' => 'Jasa']);
        $this->spending($this->schoolUser());
        $filters = ['tahun' => 2026, 'bulan' => 10, 'kategori' => 'Barang'];
        $this->actingAs($user)
            ->get(route('rekap.index', $filters))
            ->assertOk()
            ->assertViewHas(
                'items',
                fn($items) => $items->pluck('id')->all() === [$included->id],
            );
        $response = $this->get(route('rekap.export.excel', $filters))
            ->assertOk()
            ->assertDownload();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $book = IOFactory::load($path);
            $this->assertSame(
                'Belanja bersama',
                $book->getActiveSheet()->getCell('B5')->getValue(),
            );
            $this->assertEquals(5, $book->getActiveSheet()->getHighestRow());
            $book->disconnectWorksheets();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_user_without_school_cannot_see_unassigned_spending(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->spending($other);
        $this->actingAs($user)
            ->get(route('laporan-lpj.index'))
            ->assertOk()
            ->assertViewHas('belanjas', fn($items) => $items->isEmpty());
    }

    public function test_invalid_filter_is_rejected_before_export(): void
    {
        $this->actingAs($this->schoolUser())
            ->getJson(route('laporan-lpj.excel', ['tahun' => ['2026']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tahun');
    }

    public function test_profile_creation_links_user_to_new_schema(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('school-profile.store'), [
                'nama_sekolah' => 'Sekolah Baru',
            ])
            ->assertRedirect(route('school-profile.index'));
        $this->assertSame(
            'Sekolah Baru',
            $user->fresh()->schoolProfile->nama_sekolah,
        );
        $this->get(route('school-profile.index'))->assertOk();
    }

    public function test_school_colleague_can_read_existing_kwitansi_but_other_school_cannot(): void
    {
        $owner = $this->schoolUser();
        $belanja = $this->spending($owner);
        $kwitansi = $belanja->kwitansi()->create([
            'nomor_kwitansi' => 'KW-TEST',
            'tanggal_kwitansi' => '2026-10-10',
            'penerima' => 'Toko',
            'jumlah_uang' => 50000,
            'terbilang' => 'lima puluh ribu',
        ]);
        $colleague = User::factory()->create([
            'school_profile_id' => $owner->school_profile_id,
        ]);
        $this->actingAs($colleague)
            ->get(route('kwitansi.show', $kwitansi))
            ->assertOk();
        $this->actingAs($this->schoolUser())
            ->get(route('kwitansi.show', $kwitansi))
            ->assertNotFound();
    }
    public function test_backup_preserves_school_links_and_budgets(): void
    {
        $user = $this->schoolUser();
        $user->forceFill(['role' => 'admin'])->save();
        $this->actingAs($user);
        \App\Models\Anggaran::create([
            'user_id' => $user->id,
            'school_profile_id' => $user->school_profile_id,
            'tahun' => 2026,
            'jumlah' => 1000000,
        ]);
        $service = app(\App\Services\EncryptedBackupService::class);
        $payload = $service->createPayloadArray();
        $this->assertSame(3, $payload['format_version']);
        $this->assertCount(1, $payload['tables']['anggarans']);
        $service->restorePayload($payload, $user);
        $this->assertDatabaseHas('anggarans', [
            'tahun' => 2026,
            'jumlah' => 1000000,
        ]);
        $this->assertNotNull($user->fresh()->schoolProfile);
    }
}
