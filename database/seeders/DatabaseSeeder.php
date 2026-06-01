<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // User::factory()->create([
        //     'full_name' => 'Demo User',
        //     'business_name' => 'Demo Business',
        //     'phone' => '919999999999',
        //     'city' => 'Saharanpur',
        //     'market_type' => 'retail',
        // ]);

        // User::factory(5)->create();

        $this->call([
            RouteSeeder::class,
            //LoadSeeder::class,
        ]);
    }
}
