<?php

namespace Database\Factories;

use App\Models\Load;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoadFactory extends Factory
{
    protected $model = Load::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'from_city' => fake()->city(),
            'to_city' => fake()->city(),
            'vehicle_type' => fake()->randomElement(['Truck', 'Mini Truck', 'Container', 'Pickup']),
            'available_space' => fake()->numberBetween(1, 100),
            'departure_date' => now()->addDays(fake()->numberBetween(1, 7))->toDateString(),
            'departure_time' => fake()->randomElement(['06:00', '09:00', '12:00', '14:00', '16:00', '18:00']),
            'notes' => fake()->optional()->sentence(),
            'phone' => '91' . fake()->numerify('##########'),
            'status' => 'active',
            'expires_at' => now()->addHours(24),
        ];
    }
}
