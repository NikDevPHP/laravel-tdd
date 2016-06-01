<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(CodePress\CodeUser\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name' => 'user',
        'email' => 'user@email.com',
        'password' => bcrypt(123456),
        'remember_token' => str_random(10),
    ];
});