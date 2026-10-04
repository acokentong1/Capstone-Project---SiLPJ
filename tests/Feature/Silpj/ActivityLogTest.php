<?php

namespace Tests\Feature\Silpj;

use App\Models\ActivityLog;
use App\Models\Belanja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_activity_log(): void
    {
        $this->get(route('activity-logs.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_only_sees_their_own_activity(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Belanja::create([
            'user_id' => $userA->id,
            'tanggal' => '2026-10-05',
            'nomor_bukti' => 'A-001',
            'uraian' => 'Belanja milik akun A',
            'kategori' => 'Barang',
            'jumlah' => 1,
            'harga_satuan' => 50000,
            'total' => 50000,
        ]);

        Belanja::create([
            'user_id' => $userB->id,
            'tanggal' => '2026-10-05',
            'nomor_bukti' => 'B-001',
            'uraian' => 'Belanja milik akun B',
            'kategori' => 'Barang',
            'jumlah' => 1,
            'harga_satuan' => 60000,
            'total' => 60000,
        ]);

        $this->actingAs($userA)
            ->get(route('activity-logs.index'))
            ->assertOk()
            ->assertSee('Belanja milik akun A')
            ->assertDontSee('Belanja milik akun B');
    }

    public function test_creating_belanja_writes_activity_log(): void
    {
        $user = User::factory()->create();

        Belanja::create([
            'user_id' => $user->id,
            'tanggal' => '2026-10-05',
            'nomor_bukti' => 'ACT-001',
            'uraian' => 'ATK untuk audit log',
            'kategori' => 'Barang',
            'jumlah' => 2,
            'harga_satuan' => 25000,
            'total' => 50000,
        ]);

        $this->assertTrue(
            ActivityLog::query()
                ->where('user_id', $user->id)
                ->where('action', 'create')
                ->where('description', 'like', '%ATK untuk audit log%')
                ->exists()
        );
    }

    public function test_web_response_contains_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
