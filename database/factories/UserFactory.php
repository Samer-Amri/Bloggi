<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator as Faker;
use Illuminate\Support\Str;


class UserFactory extends Factory
{

protected  $model = User::class;

public function definition()
{

    return [
        'name' => 'Editor',
        'username' => 'editor',
        'email' => 'test@bloggi.test',
        'mobile' => '0675388101',
        'email_verified_at' => Carbon::now(),
        'password' => bcrypt('123123123'),
        'status' => 1,
    ];
}


}
