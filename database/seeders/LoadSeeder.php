<?php

namespace Database\Seeders;

use App\Models\Load;
use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoadSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        $route = Route::where('route_name', 'Saharanpur to Dehradun')->first();

        $loads = [
            [
                'from_city' => 'Saharanpur',
                'to_city' => 'Dehradun',
                'vehicle_type' => 'Truck',
                'available_space' => 40,
                'departure_date' => now()->addDay()->toDateString(),
                'departure_time' => '09:00',
                'notes' => 'Furniture delivery, have empty space for co-loading',
                'phone' => '919999999991',
                'route_id' => $route?->id,
            ],
            [
                'from_city' => 'Biharigarh',
                'to_city' => 'Dehradun',
                'vehicle_type' => 'Mini Truck',
                'available_space' => 15,
                'departure_date' => now()->addDay()->toDateString(),
                'departure_time' => '10:00',
                'notes' => 'Hardware supplies, looking to share transport',
                'phone' => '919999999992',
                'route_id' => $route?->id,
            ],
            [
                'from_city' => 'Meerut',
                'to_city' => 'Roorkee',
                'vehicle_type' => 'Mini Truck',
                'available_space' => 25,
                'departure_date' => now()->addDays(2)->toDateString(),
                'departure_time' => '14:00',
                'notes' => 'Furniture delivery',
                'phone' => '919999999993',
            ],
            [
                'from_city' => 'Delhi',
                'to_city' => 'Dehradun',
                'vehicle_type' => 'Container',
                'available_space' => 60,
                'departure_date' => now()->addDays(3)->toDateString(),
                'departure_time' => '06:00',
                'notes' => 'Electronics shipment, handle with care',
                'phone' => '919999999994',
            ],
            [
                'from_city' => 'Muzaffarnagar',
                'to_city' => 'Delhi',
                'vehicle_type' => 'Truck',
                'available_space' => 35,
                'departure_date' => now()->addDay()->toDateString(),
                'departure_time' => '11:00',
                'notes' => 'Agricultural produce',
                'phone' => '919999999995',
            ],
        ];

        foreach ($loads as $index => $loadData) {
            $user = $users[$index % $users->count()];

            Load::create(array_merge($loadData, [
                'user_id' => $user->id,
                'status' => 'active',
                'expires_at' => now()->addHours(24),
            ]));
        }
    }
}
