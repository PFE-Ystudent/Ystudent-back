<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\PostReply;
use App\Models\PostReplyUp;
use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyOptionReply;
use App\Models\UserRelation;
use Database\Seeders\FillUserRelationTypeSeeder;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $userNumber = 30;
        // Exécute le seeder pour UserRelationType
        $this->call(FillUserRelationTypeSeeder::class);

        // Crée 5 catégories
        $categories = Category::factory()->count(5)->create();

        // Crée 10 utilisateurs
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@ystudient.fr',
            'password' => bcrypt('admin'),
            'email_verified_at' => now(),
            'about' => 'Utilisateur admin'
        ]);
        $users = User::factory($userNumber - 1)->create();
        $users->prepend($admin);

        // Pour chaque utilisateur, crée 3 posts
        $users->each(function ($user) use ($categories, $users) {
            Post::factory(rand(0, 8))->create([
                'user_id' => $user->id,
            ])->each(function ($post) use ($categories, $users) {
                $post->categories()->attach($categories->random(2));

                PostReply::factory(rand(0, 6))->create(function () use ($post, $users) {
                    return [
                        'post_id' => $post->id,
                        'user_id' => $users->random()->id,
                    ];
                })->each(function ($reply) use ($users) {
                    PostReplyUp::factory(rand(0, 3))->create(function () use ($reply, $users) {
                        return [
                            'post_reply_id' => $reply->id,
                            'user_id' => $users->random()->id,
                        ];
                    });
                });

                if (rand(1, 5) === 1) {
                    $survey = Survey::factory()->create([
                        'post_id' => $post->id,
                    ]);

                    SurveyOption::factory(rand(3, 4))->create([
                        'survey_id' => $survey->id,
                    ])->each(function ($option) use ($users) {
                        SurveyOptionReply::factory(rand(1, 5))->create(function () use ($users, $option) {
                            return [
                                'survey_option_id' => $option->id,
                                'user_id' => $users->random()->id,
                            ];
                        });
                    });
                }
            });
        });

        // Ajoute des relations entre utilisateurs
        for ($i=1; $i <= $userNumber; $i++) {
            for ($k=1; $k <= rand(1, 3); $k++) {
                $userId = ($i + $k) % $userNumber;
                UserRelation::factory(1)->create([
                    'requester_id' => $i,
                    'user_id' => $userId === 0 ? $userNumber : $userId
                ]);
            }
        }
    }
}
