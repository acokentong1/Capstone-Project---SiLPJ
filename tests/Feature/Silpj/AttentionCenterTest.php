<?php

namespace Tests\Feature\Silpj;

use App\Models\Bapb;
use App\Models\Belanja;
use App\Models\Kwitansi;
use App\Models\NotaPesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttentionCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_attention_center(): void
    {
        $this->get(route('attention.index'))->assertRedirect(route('login'));
    }

    public function test_user_only_sees_their_own_incomplete_transactions(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $incompleteA = $this->createBelanja($userA, 'LPJ akun A belum lengkap', now()->subDays(10)->toDateString());
        $completeA = $this->createBelanja($userA, 'LPJ akun A sudah lengkap', now()->subDays(20)->toDateString());
        $incompleteB = $this->createBelanja($userB, 'LPJ akun B belum lengkap', now()->subDays(20)->toDateString());

        $this->completeDocuments($userA, $completeA);

        $this->actingAs($userA)
            ->get(route('attention.index'))
            ->assertOk()
            ->assertSee($incompleteA->uraian)
            ->assertDontSee($completeA->uraian)
            ->assertDontSee($incompleteB->uraian);
    }

    public function test_age_filter_can_show_transactions_fourteen_days_or_older(): void
    {
        $user = User::factory()->create();

        $old = $this->createBelanja($user, 'Transaksi lama prioritas', now()->subDays(20)->toDateString());
        $recent = $this->createBelanja($user, 'Transaksi baru', now()->subDays(3)->toDateString());

        $this->actingAs($user)
            ->get(route('attention.index', ['umur' => '14_plus']))
            ->assertOk()
            ->assertSee($old->uraian)
            ->assertDontSee($recent->uraian);
    }

    public function test_navigation_shows_incomplete_lpj_badge(): void
    {
        $user = User::factory()->create();
        $this->createBelanja($user, 'Transaksi membutuhkan perhatian', now()->toDateString());

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Perlu Perhatian: 1 LPJ belum lengkap', false);
    }

    private function createBelanja(User $user, string $uraian, string $tanggal): Belanja
    {
        return Belanja::create([
            'user_id' => $user->id,
            'tanggal' => $tanggal,
            'nomor_bukti' => 'BKT-' . $user->id . '-' . substr(md5($uraian), 0, 6),
            'uraian' => $uraian,
            'kategori' => 'Barang',
            'jumlah' => 1,
            'harga_satuan' => 100000,
            'total' => 100000,
        ]);
    }

    private function completeDocuments(User $user, Belanja $belanja): void
    {
        NotaPesanan::create([
            'user_id' => $user->id,
            'belanja_id' => $belanja->id,
            'nomor_nota' => 'NP-' . $belanja->id,
            'tanggal_nota' => now()->toDateString(),
            'status' => 'Dibuat',
        ]);

        Kwitansi::create([
            'belanja_id' => $belanja->id,
            'nomor_kwitansi' => 'KW-' . $belanja->id,
            'tanggal_kwitansi' => now()->toDateString(),
            'penerima' => 'Penerima Uji',
            'jumlah_uang' => 100000,
            'terbilang' => 'seratus ribu',
        ]);

        Bapb::create([
            'user_id' => $user->id,
            'belanja_id' => $belanja->id,
            'nomor_bapb' => 'BAPB-' . $belanja->id,
            'tanggal_bapb' => now()->toDateString(),
            'hasil_pemeriksaan' => 'Baik dan sesuai',
        ]);
    }
}
