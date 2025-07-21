<?php

namespace Database\Seeders;

use App\Console\Utils\CommandTimer;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PostReply;
use App\Models\PostReplyUp;
use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyOptionReply;
use App\Models\UserRelation;
use App\Models\UserRelationType;
use Database\Seeders\FillUserRelationTypeSeeder;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $userNumber = 30;

        // Fill default data
        $this->call(FillUserRelationTypeSeeder::class);
        $this->call(FillRoleSeeder::class);

        $timer = new CommandTimer();

        // Crée 5 catégories
        $categories = Category::factory()->count(5)->create();

        // Crée 10 utilisateurs
        $timer->startTimer('Seeding Users');

        $admin = User::create([
            'username' => 'Admin',
            'email' => 'admin@ystudient.fr',
            'password' => bcrypt('admin'),
            'email_verified_at' => now(),
            'about' => 'Utilisateur admin',
            'role_id' => 1
        ]);
        $users = User::factory($userNumber - 1)->create();
        $users->prepend($admin);
        $timer->endTimer();

        // Pour chaque utilisateur, crée 3 posts
        $timer->startTimer('Seeding Posts');
        $posts = collect();
        $users->each(function ($user) use ($categories, $users, $posts) {
            Post::factory(rand(0, 8))->create([
                'user_id' => $user->id,
            ])->each(function ($post) use ($categories, $users, $posts) {
                $posts->push($post);
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
        $timer->endTimer();

        // Ajoute des relations entre utilisateurs
        $timer->startTimer('Seeding UserRelations');
        $userRelations = collect();
        for ($i=1; $i <= $userNumber; $i++) {
            for ($k=1; $k <= rand(1, 3); $k++) {
                $userId = ($i + $k) % $userNumber;
                $userRelations->push(UserRelation::factory()->create([
                    'requester_id' => $i,
                    'user_id' => $userId === 0 ? $userNumber : $userId
                ]));
            }
        }
        $timer->endTimer();

        $timer->startTimer('Seeding Messages');
        $conversations = collect();
        $userRelations->where('user_relation_type_id', '=', UserRelationType::$contact)->each(function ($relation) use ($conversations) {
            if (rand(1, 4) !== 1) {
                $conversations->push(Conversation::factory()->create([
                    'requester_id' => $relation->requester_id,
                    'user_id' => $relation->user_id
                ]));
            }
        });

        $conversations->each(function ($conversation) use ($posts) {
            if (rand(1, 5) !== 1) {
                Message::factory(rand(5, 60))->create(function () use ($conversation, $posts) {
                    return [
                        'conversation_id' => $conversation->id,
                        'sender_id' => rand(1, 2) === 1 ? $conversation->requester_id : $conversation->user_id,
                        'post_id' => rand(1, 20) === 1 ? $posts->random()->id : null
                    ];
                });
            }
        });
        $timer->endTimer();
    }
}
