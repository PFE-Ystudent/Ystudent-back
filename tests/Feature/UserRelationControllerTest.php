<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRelation;
use App\Models\UserRelationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRelationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\FillRoleSeeder::class);
        $this->seed(\Database\Seeders\FillUserRelationTypeSeeder::class);
    }

    private function createUserRelation($userId, $requesterId, $relationTypeId): UserRelation
    {
        $relation = new UserRelation();
        $relation->user_id = $userId;
        $relation->requester_id = $requesterId;
        $relation->user_relation_type_id = $relationTypeId;
        $relation->save();

        return $relation;
    }

    public function test_send_relation_request()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $response = $this->actingAs($sender)->postJson("/api/users/{$receiver->id}/relations/request");

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_relations', [
            'user_id' => $receiver->id,
            'requester_id' => $sender->id,
            'user_relation_type_id' => UserRelationType::$request
        ]);
    }

    public function test_send_relation_request_conflict()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->createUserRelation($receiver->id, $sender->id, UserRelationType::$request);

        $response = $this->actingAs($sender)->postJson("/api/users/{$receiver->id}/relations/request");
        $response->assertStatus(409);
    }

    public function test_reply_to_request_accepted()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->createUserRelation($receiver->id, $sender->id, UserRelationType::$request);

        $response = $this->actingAs($receiver)->postJson("/api/users/{$sender->id}/relations/request/reply", [
            'is_accepted' => true
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_relations', [
            'user_id' => $receiver->id,
            'requester_id' => $sender->id,
            'user_relation_type_id' => UserRelationType::$contact
        ]);
    }

    public function test_reply_to_request_rejected()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->createUserRelation($receiver->id, $sender->id, UserRelationType::$request);

        $response = $this->actingAs($receiver)->postJson("/api/users/{$sender->id}/relations/request/reply", [
            'is_accepted' => false
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseMissing('user_relations', [
            'user_id' => $receiver->id,
            'requester_id' => $sender->id
        ]);
    }

    public function test_get_contacts()
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->createUserRelation($me->id, $other->id, UserRelationType::$contact);

        $response = $this->actingAs($me)->getJson('/api/users/relations/contact');

        $response->assertOk();
        $response->assertJsonFragment(['username' => $other->username]);
    }

    public function test_get_contacts_for_select()
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->createUserRelation($me->id, $other->id, UserRelationType::$contact);

        $response = $this->actingAs($me)->getJson('/api/users/relations/contact/select');

        $response->assertOk();
        $response->assertJsonFragment(['name' => $other->username]);
    }
}
