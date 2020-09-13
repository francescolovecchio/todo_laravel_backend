<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TodoList;
use Faker\Generator as Faker;

$factory->define(TodoList::class, function (Faker $faker) {
    return [
        "name" => $faker->name,
    ];
});
