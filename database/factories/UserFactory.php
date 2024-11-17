<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $faker = \Faker\Factory::create('fr_FR');
        
        return [
            'username' => $faker->unique()->userName,
            'email' => $faker->unique()->safeEmail,
            'password' => bcrypt('admin'),
            'about' => $faker->paragraph,
            'email_verified_at' => now(),
            'remember_token' => null,
        ];
    }
}
