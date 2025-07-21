<?php

namespace Database\Factories;

use App\Models\PostReply;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostReplyFactory extends Factory
{
    protected $model = PostReply::class;

    public function definition()
    {
        return [
            'content' => $this->faker->paragraphs(2, true),
            'is_archived' => false,
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
        ];
    }
}
