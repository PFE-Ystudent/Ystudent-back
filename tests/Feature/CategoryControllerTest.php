<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): self
    {
        $this->seed(\Database\Seeders\FillRoleSeeder::class);

        $admin = User::factory()->create([
            'role_id' => Role::$administrator,
        ]);

        return $this->actingAs($admin);
    }

    private function actingAsNonAdmin(): self
    {
        $this->seed(\Database\Seeders\FillRoleSeeder::class);

        $user = User::factory()->create([
            'role_id' => Role::$member,
        ]);

        return $this->actingAs($user);
    }

    public function test_can_fetch_categories_for_select()
    {
        $this->actingAsNonAdmin();

        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/categories');

        $response->assertOk();
        $response->assertJsonStructure([
            'categories' => [
                '*' => ['id', 'name']
            ]
        ]);
    }

    public function test_admin_can_fetch_all_categories()
    {
        $this->actingAsAdmin();

        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/categories');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'isArchived', 'createdAt']
            ]
        ]);
    }

    public function test_admin_can_store_a_category()
    {
        $this->actingAsAdmin();

        $data = ['name' => 'New Category'];

        $response = $this->postJson('/api/admin/categories', $data);

        $response->assertOk();
        $this->assertDatabaseHas('categories', $data);
    }

    public function test_admin_cannot_store_invalid_category()
    {
        $this->actingAsAdmin();

        // Invalid data
        $response = $this->postJson('/api/admin/categories', ['name' => '']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_admin_can_update_a_category()
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create();

        $data = ['name' => 'Updated Name'];

        $response = $this->putJson("/api/admin/categories/{$category->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('categories', $data);
    }

    public function test_admin_cannot_update_with_invalid_data()
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create();

        $response = $this->putJson("/api/admin/categories/{$category->id}", ['name' => '']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_admin_can_destroy_a_category()
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertNoContent();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_archived' => true,
        ]);
    }

    public function test_admin_can_restore_a_category()
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create(['is_archived' => true]);

        $response = $this->postJson("/api/admin/categories/{$category->id}/restore");

        $response->assertNoContent();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_archived' => false,
        ]);
    }

    public function test_cannot_restore_nonexistent_category()
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/admin/categories/999/restore');

        $response->assertNotFound();
    }

    public function test_non_admin_user_cannot_access_admin_routes()
    {
        $this->actingAsNonAdmin();

        $response = $this->getJson('/api/admin/categories');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_routes()
    {
        $response = $this->getJson('/api/admin/categories');

        $response->assertStatus(401);
    }
}
