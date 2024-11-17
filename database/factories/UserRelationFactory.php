<?php

namespace Database\Factories;

use App\Models\UserRelation;
use App\Models\UserRelationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserRelationFactory extends Factory
{
    protected $model = UserRelation::class;

    public function definition()
    {
        return [
            'requester_id' => User::factory(),
            'user_id' => User::factory(),
            'user_relation_type_id' => $this->faker->randomElement([
                UserRelationType::$contact,
                UserRelationType::$request,
                UserRelationType::$blocked,
            ]),
        ];
    }
}
