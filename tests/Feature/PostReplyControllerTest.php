<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostReplyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\FillRoleSeeder::class);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->post = Post::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_list_post_replies()
    {
        PostReply::factory()->count(3)->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/posts/{$this->post->id}/replies");

        $response->assertStatus(200)
                 ->assertJsonStructure(['postReplies' => [['id', 'content', 'author']]]);
    }

    public function test_can_create_post_reply()
    {
        $payload = ['content' => 'This is a reply'];

        $response = $this->postJson("/api/posts/{$this->post->id}/replies", $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['content' => 'This is a reply']);

        $this->assertDatabaseHas('post_replies', [
            'content' => 'This is a reply',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_update_post_reply()
    {
        $reply = PostReply::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'content' => 'Old content',
        ]);

        $response = $this->patchJson("/api/posts/replies/{$reply->id}", ['content' => 'Updated content']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['content' => 'Updated content']);
    }

    public function test_can_delete_post_reply()
    {
        $reply = PostReply::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/posts/replies/{$reply->id}");

        $response->assertStatus(204);
        $this->assertDatabaseHas('post_replies', [
            'id' => $reply->id,
            'is_archived' => true,
        ]);
    }

    public function test_can_upvote_and_unvote_post_reply()
    {
        $reply = PostReply::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => User::factory()->create()->id,
        ]);

        // Upvote
        $response = $this->postJson("/api/posts/replies/{$reply->id}/up");
        $response->assertStatus(200)
                 ->assertJsonFragment(['isUpVoted' => true]);

        $this->assertDatabaseHas('post_reply_ups', [
            'post_reply_id' => $reply->id,
            'user_id' => $this->user->id,
        ]);

        // Remove upvote
        $response = $this->postJson("/api/posts/replies/{$reply->id}/up");
        $response->assertStatus(200)
                 ->assertJsonFragment(['isUpVoted' => false]);

        $this->assertDatabaseMissing('post_reply_ups', [
            'post_reply_id' => $reply->id,
            'user_id' => $this->user->id,
        ]);
    }

    // TODO: A implémenter côté front
    // public function test_can_upload_files_to_reply()
    // {
    //     Storage::fake('public');

    //     $reply = PostReply::factory()->create([
    //         'post_id' => $this->post->id,
    //         'user_id' => $this->user->id,
    //     ]);

    //     $file = UploadedFile::fake()->image('reply-image.jpg');

    //     $response = $this->postJson("/api/posts/replies/{$reply->id}/images", [
    //         'files' => [$file],
    //     ]);

    //     $response->assertStatus(201)
    //              ->assertJsonStructure([[
    //                  'id',
    //                  'filename',
    //                  'filepath',
    //                  'post_reply_id',
    //              ]]);

    //     $this->assertTrue(Storage::disk('public')->exists('/post_reply_files/' . $file->hashName()));
    // }
}
