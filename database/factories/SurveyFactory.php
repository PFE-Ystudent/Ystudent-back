<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    public function definition()
    {
        return [
            'question' => $this->faker->sentence,
            'post_id' => Post::factory(),
        ];
    }
}
