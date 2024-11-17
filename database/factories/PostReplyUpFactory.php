<?php

namespace Database\Factories;

use App\Models\PostReplyUp;
use App\Models\PostReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostReplyUpFactory extends Factory
{
    protected $model = PostReplyUp::class;

    public function definition()
    {
        return [
            'post_reply_id' => PostReply::factory(),
            'user_id' => User::factory(),
        ];
    }
}
