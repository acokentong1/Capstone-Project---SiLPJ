<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_open_admin_area(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_open_user_management(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->saveQuietly();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->saveQuietly();

        $response = $this->actingAs($admin)
            ->patch(route('admin.users.status', $admin), ['is_active' => 0]);

        $response->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->isActive());
    }
}
