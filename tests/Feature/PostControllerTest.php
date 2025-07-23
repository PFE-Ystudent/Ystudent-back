<?php

namespace Tests\Feature;

use App\Events\MessageCreateEvent;
use App\Models\Category;
use App\Models\FavoritePost;
use App\Models\Post;
use App\Models\User;
use App\Models\UserRelation;
use App\Models\UserRelationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\FillRoleSeeder::class);
        $this->seed(\Database\Seeders\FillUserRelationTypeSeeder::class);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->category = Category::factory()->create();
    }

    public function test_index_returns_user_posts()
    {
        Post::factory()->count(3)->create(['user_id' => $this->user->id]);
        Post::factory()->count(2)->create();

        $response = $this->json('GET', '/api/posts/me', ['per_page' => 10, 'page' => 1]);

        $response->assertStatus(200)
            ->assertJsonStructure(['posts', 'lastPage'])
            ->assertJsonCount(3, 'posts');
    }

    public function test_favorite_post_list()
    {
        $post = Post::factory()->create();

        $fp = new FavoritePost();
        $fp->user()->associate($this->user->id);
        $fp->post()->associate($post->id);
        $fp->save();

        $response = $this->json('GET', '/api/posts/favorite', ['per_page' => 10, 'page' => 1]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'posts');
    }

    public function test_new_post_list()
    {
        Post::factory()->count(5)->create();

        $response = $this->json('GET', '/api/posts/new', ['per_page' => 10, 'page' => 1]);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'posts');
    }

    public function test_store_creates_post_with_survey_integration()
    {
        $payload = [
            'title' => 'Mon titre',
            'content' => 'Contenu du post',
            'categories' => [$this->category->id],
            'integrations' => [
                [
                    'type' => 'survey',
                    'data' => [
                        'question' => 'Question sondage ?',
                        'options' => ['Option 1', 'Option 2']
                    ],
                ],
            ],
        ];

        $response = $this->json('POST', '/api/posts', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'Mon titre')
            ->assertJsonPath('content', 'Contenu du post');
        
        $this->assertDatabaseHas('posts', ['title' => 'Mon titre']);
        $this->assertDatabaseHas('surveys', ['question' => 'Question sondage ?']);
    }

    public function test_show_returns_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->json('GET', "/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonPath('post.id', $post->id);
    }

    public function test_update_modifies_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $payload = ['title' => 'Titre modifié'];

        $response = $this->json('PUT', "/api/posts/{$post->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Titre modifié');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Titre modifié']);
    }

    public function test_destroy_soft_deletes_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->json('DELETE', "/api/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_archived' => true]);
    }

    public function test_favorite_toggle()
    {
        $post = Post::factory()->create();

        // Add
        $response = $this->json('POST', "/api/posts/{$post->id}/favorite");
        $response->assertStatus(204);
        $this->assertDatabaseHas('favorite_posts', [
            'post_id' => $post->id,
            'user_id' => $this->user->id,
        ]);

        // Remove
        $response = $this->json('POST', "/api/posts/{$post->id}/favorite");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('favorite_posts', [
            'post_id' => $post->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_share_post_broadcasts_event()
    {
        Event::fake([MessageCreateEvent::class]);

        $post = Post::factory()->create();

        $userToShare = User::factory()->create();

         $relation = new UserRelation();
        $relation->user_id = $userToShare->id;
        $relation->requester_id = $this->user->id;
        $relation->user_relation_type_id = UserRelationType::$contact;
        $relation->save();


        $payload = [
            'users' => [$userToShare->id],
            'content' => 'Message de partage'
        ];

        $response = $this->json('POST', "/api/posts/{$post->id}/share", $payload);

        $response->assertStatus(204);

        Event::assertDispatched(MessageCreateEvent::class);
    }

    public function test_add_files_to_post()
    {
        Storage::fake('public');

        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $file1 = UploadedFile::fake()->image('photo1.jpg');
        $file2 = UploadedFile::fake()->image('photo2.png');

        $response = $this->json('POST', "/api/posts/{$post->id}/files", [
            'files' => [$file1, $file2]
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(2);

        $this->assertTrue(Storage::disk('public')->exists('/post-files/' . $file1->hashName()));
        $this->assertTrue(Storage::disk('public')->exists('/post-files/' . $file2->hashName()));
    }
}
