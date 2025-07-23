<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyOptionReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyControllerTest extends TestCase
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

    public function test_user_can_reply_to_survey_option()
    {
        $this->actingAs($this->user);

        $survey = Survey::factory()->create();
        $option1 = SurveyOption::factory()->create(['survey_id' => $survey->id, 'name' => 'Option 1']);
        $option2 = SurveyOption::factory()->create(['survey_id' => $survey->id, 'name' => 'Option 2']);

        $response = $this->postJson("/api/survey/options/{$option1->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'survey' => [
                         'id',
                         'question',
                         'options' => [
                             ['id', 'name', 'isSelected', 'replyCount']
                         ]
                     ]
                 ]);

        $this->assertDatabaseHas('survey_option_replies', [
            'survey_option_id' => $option1->id,
            'user_id' => $this->user->id,
        ]);

        $this->postJson("/api/survey/options/{$option2->id}");

        $this->assertDatabaseHas('survey_option_replies', [
            'survey_option_id' => $option2->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseMissing('survey_option_replies', [
            'survey_option_id' => $option1->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_delete_survey_reply()
    {
        $this->actingAs($this->user);

        $survey = Survey::factory()->create();
        $option = SurveyOption::factory()->create([
            'survey_id' => $survey->id,
            'name' => 'Option A'
        ]);

        SurveyOptionReply::factory()->create([
            'user_id' => $this->user->id,
            'survey_option_id' => $option->id
        ]);

        $response = $this->deleteJson("/api/survey/options/{$option->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'survey' => [
                        'id',
                        'question',
                        'options' => [
                            ['id', 'name', 'isSelected', 'replyCount']
                        ]
                    ]
                ]);

        $this->assertDatabaseMissing('survey_option_replies', [
            'user_id' => $this->user->id,
            'survey_option_id' => $option->id
        ]);
    }
}
