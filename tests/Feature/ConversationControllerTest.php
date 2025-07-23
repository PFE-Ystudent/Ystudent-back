<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\FillRoleSeeder::class);
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $this->actingAs($this->user);
    }

    public function it_returns_user_conversations_with_last_message()
    {
        $conversation = Conversation::factory()->create([
            'requester_id' => $this->user->id,
            'user_id' => $this->otherUser->id,
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'content' => 'Hello there!',
        ]);

        $response = $this->getJson('/api/conversations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'conversations' => [[
                    'id',
                    'requester',
                    'user',
                    'lastMessage' => [
                        'content',
                        'createdAt'
                    ]
                ]]
            ]);
    }

    public function it_creates_a_new_conversation_if_not_exists()
    {
        $response = $this->postJson('/api/conversations', [
            'user_id' => $this->otherUser->id
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'conversation' => [
                    'id',
                    'requester',
                    'user',
                ]
            ]);

        $this->assertDatabaseHas('conversations', [
            'requester_id' => $this->user->id,
            'user_id' => $this->otherUser->id
        ]);
    }

    public function it_returns_existing_conversation_if_already_created()
    {
        $conversation = Conversation::factory()->create([
            'requester_id' => $this->user->id,
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->postJson('/api/conversations', [
            'user_id' => $this->otherUser->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'conversation' => [
                    'id' => $conversation->id
                ]
            ]);
    }

    /** @test */
    public function it_fails_to_create_conversation_with_invalid_user_id()
    {
        $response = $this->postJson('/api/conversations', [
            'user_id' => 999999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('user_id');
    }

    /** @test */
    public function requester_can_hide_conversation()
    {
        $conversation = Conversation::factory()->create([
            'requester_id' => $this->user->id,
            'user_id' => $this->otherUser->id,
            'is_closed_requester' => false,
        ]);

        $response = $this->postJson("/api/conversations/{$conversation->id}/close");

        $response->assertNoContent();

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'is_closed_requester' => true
        ]);
    }

    /** @test */
    public function user_can_hide_conversation()
    {
        $conversation = Conversation::factory()->create([
            'requester_id' => $this->otherUser->id,
            'user_id' => $this->user->id,
            'is_closed_user' => false,
        ]);

        $response = $this->postJson("/api/conversations/{$conversation->id}/close");

        $response->assertNoContent();

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'is_closed_user' => true
        ]);
    }
}
