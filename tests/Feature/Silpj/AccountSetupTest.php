<?php

namespace Tests\Feature\Silpj;

use App\Models\SchoolProfile;
use App\Models\User;
use App\Services\InitialSetup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSetupTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        return [
            'nama_sekolah' => 'Sekolah Uji', 'npsn' => '01234567', 'alamat' => 'Jalan Sekolah 1',
            'name' => 'Admin Pertama', 'email' => 'admin@example.test',
            'password' => 'Rahasia123!', 'password_confirmation' => 'Rahasia123!',
        ];
    }

    public function test_empty_installation_redirects_login_and_registration_to_setup(): void
    {
        $this->get('/')->assertRedirect(route('setup.create'));
        $this->get('/login')->assertRedirect(route('setup.create'));
        $this->post('/register', $this->setupData())->assertRedirect(route('setup.create'));
        $this->assertDatabaseCount('users', 0);
        $this->get(route('setup.create'))->assertOk()->assertSee('Pengaturan Awal');
    }

    public function test_setup_creates_linked_admin_and_closes_permanently(): void
    {
        $this->post(route('setup.store'), $this->setupData())->assertRedirect(route('admin.users.create'));
        $user = User::firstOrFail();
        $this->assertSame('admin', $user->role);
        $this->assertSame('01234567', $user->schoolProfile->npsn);
        $this->assertTrue(Hash::check('Rahasia123!', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->assertFalse(app(InitialSetup::class)->needed());
        $this->post('/logout');
        $this->post(route('setup.store'), $this->setupData())->assertForbidden();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('school_profiles', 1);
        // Even removal of all users must not reopen the setup gate.
        DB::table('users')->delete();
        DB::table('school_profiles')->delete();
        $this->assertFalse(app(InitialSetup::class)->needed());
    }

    public function test_existing_reader_also_blocks_public_setup(): void
    {
        User::factory()->create(['role' => 'user']);
        $this->get(route('setup.create'))->assertRedirect(route('login'));
        $this->post(route('setup.store'), $this->setupData())->assertForbidden();
        $this->assertDatabaseCount('school_profiles', 0);
    }

    public function test_invalid_setup_does_not_create_partial_school_or_user(): void
    {
        $data = $this->setupData();
        $data['password_confirmation'] = 'berbeda';
        $this->post(route('setup.store'), $data)->assertSessionHasErrors('password');
        $this->assertDatabaseCount('school_profiles', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertTrue(app(InitialSetup::class)->needed());
    }

    public function test_admin_creates_each_role_linked_to_selected_school(): void
    {
        $school = SchoolProfile::create(['nama_sekolah' => 'Sekolah Uji']);
        $admin = User::factory()->create(['role' => 'admin', 'school_profile_id' => $school->id]);
        $this->actingAs($admin)->get(route('admin.users.create'))->assertOk()->assertSee('Sekolah Uji');
        foreach (['admin', 'bendahara', 'kepala_sekolah'] as $role) {
            $data = $this->setupData();
            $data['email'] = $role.'@example.test';
            $data['role'] = $role;
            $data['school_profile_id'] = $school->id;
            $this->post(route('admin.users.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
            $created = User::where('email', $data['email'])->firstOrFail();
            $this->assertSame($role, $created->role);
            $this->assertEquals($school->id, $created->school_profile_id);
            $this->assertTrue(Hash::check($data['password'], $created->password));
        }
    }

    public function test_non_admin_cannot_create_accounts(): void
    {
        $user = User::factory()->create(['role' => 'bendahara']);
        $this->actingAs($user)->get(route('admin.users.create'))->assertForbidden();
        $this->post(route('admin.users.store'), $this->setupData())->assertForbidden();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_reset_requires_admin_password_and_hashes_new_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'AdminPassword123']);
        $target = User::factory()->create();
        $data = ['current_password' => 'wrong', 'password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123'];
        $this->actingAs($admin)->put(route('admin.users.password', $target), $data)->assertSessionHasErrors('current_password');
        $this->assertFalse(Hash::check('NewPassword123', $target->fresh()->password));
        $data['current_password'] = 'AdminPassword123';
        $this->put(route('admin.users.password', $target), $data)->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewPassword123', $target->fresh()->password));
    }

    public function test_duplicate_email_and_invalid_school_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $data = $this->setupData() + ['role' => 'admin', 'school_profile_id' => 999999];
        $data['email'] = $admin->email;
        $this->actingAs($admin)->post(route('admin.users.store'), $data)->assertSessionHasErrors(['email', 'school_profile_id']);
        $this->assertDatabaseCount('users', 1);
    }
}
