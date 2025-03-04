<?php

namespace Database\Factories;

use App\Models\Post;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Str;

class PostFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Post::class;

    public function definition()
    {
        $faker = FakerFactory::create();

        return [
            'title' => $faker->sentence(mt_rand(3, 6), true),
            'title_en' => $faker->sentence(mt_rand(3, 6), true),
            'slug' => Str::slug($faker->sentence()),
            'slug_en' => Str::slug($faker->sentence()),
            'description' => $faker->paragraph(),
            'description_en' => $faker->paragraph(),
            'status' => $faker->boolean(),
            'comment_able' => $faker->boolean(),
            'user_id' => \App\Models\User::factory(),
            'category_id' => \App\Models\Category::factory(),
        ];
    }
}
