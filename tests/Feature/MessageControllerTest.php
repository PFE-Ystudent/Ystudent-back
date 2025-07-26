<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\FillRoleSeeder::class);
        $this->user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($this->user);

        $this->conversation = Conversation::factory()->create([
            'requester_id' => $this->user->id,
            'user_id' => $otherUser->id,
        ]);
    }

    public function it_returns_messages_for_a_conversation()
    {
        Message::factory()->count(3)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/conversations/{$this->conversation->id}/messages");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'messages' => [['id', 'content', 'sender', 'createdAt']],
                'firstMessageId',
                'lastMessageId'
            ])
            ->assertJsonCount(3, 'messages');
    }

    public function it_can_paginate_messages_before_a_given_id()
    {
        $messages = Message::factory()->count(5)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/conversations/{$this->conversation->id}/messages?before_id=" . $messages[4]->id);

        $response->assertStatus(200);
        $this->assertLessThan($messages[4]->id, $response->json('messages')[0]['id']);
    }

    public function it_can_paginate_messages_after_a_given_id()
    {
        $messages = Message::factory()->count(5)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/conversations/{$this->conversation->id}/messages?after_id=" . $messages[0]->id);

        $response->assertStatus(200);
        $this->assertGreaterThan($messages[0]->id, $response->json('messages')[0]['id']);
    }

    public function it_can_store_a_new_message()
    {
        $response = $this->postJson("/api/conversations/{$this->conversation->id}/messages", [
            'content' => 'Hello world!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message.content', 'Hello world!');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
            'content' => 'Hello world!',
        ]);
    }

    public function it_can_update_a_message()
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
            'content' => 'Initial content',
        ]);

        $response = $this->putJson("/api/messages/{$message->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message.content', 'Updated content');

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'content' => 'Updated content',
        ]);
    }

    public function it_can_soft_delete_a_message()
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
            'is_archived' => false,
        ]);

        $response = $this->deleteJson("/api/messages/{$message->id}");

        $response->assertNoContent();

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'is_archived' => true,
        ]);
    }
}
