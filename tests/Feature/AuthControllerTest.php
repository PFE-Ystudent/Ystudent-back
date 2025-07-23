<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\FillRoleSeeder::class);
    }

    private function actingAsAdmin(): self
    {
        $this->seed(\Database\Seeders\FillRoleSeeder::class);

        $admin = User::factory()->create([
            'role_id' => Role::$administrator,
        ]);

        return $this->actingAs($admin);
    }

    public function test_user_can_authenticate_with_correct_credentials()
    {
        $this->seed(\Database\Seeders\FillRoleSeeder::class);

        $password = 'secret123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'token',
            'user' => ['id', 'username', 'email', 'about', 'avatar', 'role', 'createdAt'],
        ]);
    }

    public function test_user_cannot_authenticate_with_wrong_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
        $response->assertJsonValidationErrors('email');
    }

    public function test_authenticate_fails_with_invalid_email_format()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'whatever',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_authenticate_fails_if_email_does_not_exist()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'whatever',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_user_can_register_successfully()
    {
        $this->seed(\Database\Seeders\FillRoleSeeder::class);

        $data = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertOk();
        $response->assertJsonStructure([
            'token',
            'user' => ['id', 'username', 'email', 'about', 'avatar', 'role', 'createdAt'],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'username' => 'newuser',
        ]);
    }

    public function test_registration_fails_with_missing_or_invalid_data()
    {
        $this->seed(\Database\Seeders\FillRoleSeeder::class);

        // Missing fields
        $response = $this->postJson('/api/register', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username', 'email', 'password']);

        // Password confirmation mismatch
        $response = $this->postJson('/api/register', [
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');

        // Username not unique
        $existingUser = User::factory()->create(['username' => 'existing']);
        $response = $this->postJson('/api/register', [
            'username' => 'existing',
            'email' => 'unique@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('username');

        // Email not unique
        $response = $this->postJson('/api/register', [
            'username' => 'uniqueuser',
            'email' => $existingUser->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_authenticated_user_can_logout()
    {
        $this->actingAsAdmin();

        $response = $this->deleteJson('/api/logout');

        $response->assertNoContent();
    }

    public function test_guest_cannot_logout()
    {
        $response = $this->deleteJson('/api/logout');

        $response->assertStatus(401);
    }
}
