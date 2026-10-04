<?php

namespace Tests\Feature\Silpj;

use App\Models\Belanja;
use App\Models\Kwitansi;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_create_kwitansi_for_another_users_belanja(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $belanja = $this->createBelanja($owner, 150000);

        $response = $this->actingAs($attacker)->post(route('kwitansi.store'), [
            'belanja_id' => $belanja->id,
            'penerima' => 'Penerima Tidak Sah',
            'terbilang' => 'seratus lima puluh ribu rupiah',
            'jumlah_uang' => 1,
            'nomor_kwitansi' => 'KW-PALSU',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('kwitansis', 0);
    }

    public function test_user_cannot_create_nota_for_another_users_belanja(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $belanja = $this->createBelanja($owner, 120000);

        $this->actingAs($attacker)
            ->post(route('nota-pesanan.store'), ['belanja_id' => $belanja->id])
            ->assertNotFound();

        $this->assertDatabaseCount('nota_pesanans', 0);
    }

    public function test_user_cannot_create_bapb_for_another_users_belanja(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $belanja = $this->createBelanja($owner, 130000);

        $this->actingAs($attacker)->post(route('bapb.store'), [
            'belanja_id' => $belanja->id,
            'tanggal_bapb' => '2026-10-04',
            'hasil_pemeriksaan' => 'Baik dan sesuai',
        ])->assertNotFound();

        $this->assertDatabaseCount('bapbs', 0);
    }

    public function test_kwitansi_amount_and_number_are_generated_from_server_data(): void
    {
        $user = User::factory()->create();
        $belanja = $this->createBelanja($user, 275000);
        $this->createSchoolProfile($user);

        $response = $this->actingAs($user)->post(route('kwitansi.store'), [
            'belanja_id' => $belanja->id,
            'penerima' => 'Toko Contoh',
            'terbilang' => 'teks manipulasi',
            'jumlah_uang' => 1,
            'nomor_kwitansi' => 'KW-DIMANIPULASI',
        ]);

        $kwitansi = Kwitansi::firstOrFail();

        $response->assertRedirect(route('kwitansi.show', $kwitansi->id));
        $this->assertSame('KW-' . str_pad((string) $belanja->id, 3, '0', STR_PAD_LEFT), $kwitansi->nomor_kwitansi);
        $this->assertSame('275000.00', $kwitansi->jumlah_uang);
        $this->assertSame('dua ratus tujuh puluh lima ribu', $kwitansi->terbilang);
    }

    public function test_database_prevents_two_kwitansi_for_the_same_belanja(): void
    {
        $user = User::factory()->create();
        $belanja = $this->createBelanja($user, 100000);

        Kwitansi::create([
            'belanja_id' => $belanja->id,
            'nomor_kwitansi' => 'KW-001',
            'tanggal_kwitansi' => '2026-10-04',
            'penerima' => 'Toko A',
            'jumlah_uang' => 100000,
            'terbilang' => 'seratus ribu rupiah',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Kwitansi::create([
            'belanja_id' => $belanja->id,
            'nomor_kwitansi' => 'KW-002',
            'tanggal_kwitansi' => '2026-10-04',
            'penerima' => 'Toko B',
            'jumlah_uang' => 100000,
            'terbilang' => 'seratus ribu rupiah',
        ]);
    }

    public function test_one_user_can_only_have_one_school_profile(): void
    {
        $user = User::factory()->create();

        SchoolProfile::create([
            'user_id' => $user->id,
            'nama_sekolah' => 'Sekolah A',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        SchoolProfile::create([
            'user_id' => $user->id,
            'nama_sekolah' => 'Sekolah B',
        ]);
    }

    public function test_user_cannot_edit_another_users_school_profile(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $school = SchoolProfile::create([
            'user_id' => $owner->id,
            'nama_sekolah' => 'Sekolah Pemilik',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('school-profile.edit', $school));

        $response->assertForbidden();
    }

    public function test_user_cannot_edit_another_users_belanja(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $belanja = $this->createBelanja($owner, 100000);

        $response = $this->actingAs($otherUser)
            ->get(route('belanja.edit', $belanja));

        $response->assertForbidden();
    }

    public function test_belanja_total_is_calculated_on_the_server(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('belanja.store'), [
            'tanggal' => '2026-10-04',
            'nomor_bukti' => 'BKT-001',
            'uraian' => 'ATK',
            'kategori' => 'Barang',
            'jumlah' => 3,
            'harga_satuan' => 25000,
            'total' => 1,
        ])->assertRedirect(route('belanja.index'));

        $this->assertDatabaseHas('belanjas', [
            'user_id' => $user->id,
            'jumlah' => 3,
            'harga_satuan' => 25000,
            'total' => 75000,
        ]);
    }

    public function test_invalid_belanja_category_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('belanja.create'))
            ->post(route('belanja.store'), [
                'tanggal' => '2026-10-04',
                'nomor_bukti' => 'BKT-002',
                'uraian' => 'Input tidak valid',
                'kategori' => 'Kategori Bebas',
                'jumlah' => 1,
                'harga_satuan' => 1000,
            ]);

        $response->assertRedirect(route('belanja.create'));
        $response->assertSessionHasErrors('kategori');
        $this->assertDatabaseCount('belanjas', 0);
    }

    private function createSchoolProfile(User $user): SchoolProfile
    {
        return SchoolProfile::create([
            'user_id' => $user->id,
            'nama_sekolah' => 'Sekolah Pengujian',
        ]);
    }

    private function createBelanja(User $user, int $total): Belanja
    {
        return Belanja::create([
            'user_id' => $user->id,
            'tanggal' => '2026-10-04',
            'nomor_bukti' => 'BKT-' . $user->id . '-' . $total,
            'uraian' => 'Belanja pengujian',
            'kategori' => 'Barang',
            'jumlah' => 1,
            'harga_satuan' => $total,
            'total' => $total,
        ]);
    }
}
