<?php

namespace Database\Factories;

use App\Models\SurveyOptionReply;
use App\Models\SurveyOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyOptionReplyFactory extends Factory
{
    protected $model = SurveyOptionReply::class;

    public function definition()
    {
        return [
            'survey_option_id' => SurveyOption::factory(),
            'user_id' => User::factory(),
        ];
    }
}
