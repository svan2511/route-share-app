<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'business_name' => fake()->company(),
            'phone' => '91' . fake()->unique()->numerify('##########'),
            'city' => fake()->city(),
            'market_type' => fake()->randomElement(['retail', 'wholesale', 'manufacturing', 'logistics']),
            'remember_token' => Str::random(10),
        ];
    }
}
