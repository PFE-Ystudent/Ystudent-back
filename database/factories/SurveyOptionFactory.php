<?php

namespace Database\Factories;

use App\Models\SurveyOption;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyOptionFactory extends Factory
{
    protected $model = SurveyOption::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'survey_id' => Survey::factory(),
        ];
    }
}
