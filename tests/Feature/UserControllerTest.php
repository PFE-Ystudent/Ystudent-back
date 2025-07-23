<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\FillRoleSeeder::class);
    }

    public function it_returns_authenticated_user_data()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users/me');

        $response->assertOk()
                 ->assertJson([
                     'id' => $user->id,
                     'username' => $user->username,
                     'email' => $user->email,
                 ]);
    }

    public function it_returns_user_details()
    {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        Sanctum::actingAs($authUser);

        $response = $this->getJson("/api/users/{$targetUser->id}");

        $response->assertOk()
                 ->assertJsonStructure([
                     'id',
                     'username',
                     'about',
                     'role',
                     'createdAt',
                     'postsCount',
                     'postRepliesCount',
                     'relationType',
                     'avatar',
                 ]);
    }

    public function it_updates_user_profile()
    {
        Storage::fake('public');

        $user = User::factory()->create(['avatar' => null]);
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/users/me', [
            'username' => 'newusername',
            'about' => 'Updated about me',
            'avatar' => $file,
        ]);

        $response->assertOk()
                 ->assertJson([
                     'username' => 'newusername',
                     'about' => 'Updated about me',
                 ]);

        $user->refresh();

        $avatarPath = str_replace('/storage/', '', $user->avatar);
        $this->assertTrue(Storage::disk('public')->exists($avatarPath));
    }

    public function it_returns_paginated_posts_for_user()
    {
        $user = User::factory()->create();
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        Post::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->id}/posts?per_page=5&page=1");

        $response->assertOk()
                 ->assertJsonStructure([
                     'posts',
                     'lastPage',
                 ]);
    }

    public function it_fetches_users_by_username_query()
    {
        $user = User::factory()->create(['username' => 'targetuser']);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/search/users?query=target');

        $response->assertOk()
                 ->assertJsonFragment([
                     'name' => 'targetuser',
                 ]);
    }

    public function it_requires_authentication_for_protected_routes()
    {
        $response = $this->getJson('/api/users/me');
        $response->assertUnauthorized();
    }
}
