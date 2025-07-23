<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $faker = \Faker\Factory::create('fr_FR');
        
        $username = $faker->unique()->userName;
        return [
            'username' => $username,
            'email' => $username . '@ystudent.fr',
            'password' => bcrypt('admin'),
            'about' => $faker->paragraph,
            'email_verified_at' => now(),
            'remember_token' => null,
        ];
    }
}
