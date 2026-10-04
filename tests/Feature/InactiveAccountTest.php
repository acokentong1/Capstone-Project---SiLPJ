<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InactiveAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_account_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->forceFill(['is_active' => false])->saveQuietly();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
