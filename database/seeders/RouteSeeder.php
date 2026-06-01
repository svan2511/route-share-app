<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            // ═══════════════════════════════════════════════
            // CORRIDOR 1: SAHARANPUR ↔ DEHRADUN & UK
            // ═══════════════════════════════════════════════
            [
                'route_name' => 'Saharanpur to Dehradun',
                'from_city' => 'Saharanpur',
                'to_city' => 'Dehradun',
                'stops' => ['Saharanpur','Gagalheri', 'Chhutmalpur', 'Biharigarh', 'Sundarpur', 'Mohand', 'Dehradun'],
            ],
            [
                'route_name' => 'Saharanpur to Roorkee via Chhutmalpur',
                'from_city' => 'Saharanpur',
                'to_city' => 'Roorkee',
                'stops' => ['Saharanpur','Gagalheri', 'Chhutmalpur','Choli', 'Bhagwanpur', 'Karondi', 'Roorkee'],
            ],
            [
                'route_name' => 'Saharanpur to Roorkee via Gaghleri',
                'from_city' => 'Saharanpur',
                'to_city' => 'Roorkee',
                'stops' => ['Saharanpur','Gagalheri', 'Bhagwanpur', 'Karondi', 'Roorkee'],
            ],
            [
                'route_name' => 'Saharanpur to Haridwar',
                'from_city' => 'Saharanpur',
                'to_city' => 'Haridwar',
                'stops' => ['Saharanpur','Gagalheri', 'Chhutmalpur','Choli', 'Bhagwanpur', 'Karondi', 'Roorkee','Jwalapur','Bahadrabad', 'Haridwar'],
            ],

            [
                'route_name' => 'Saharanpur to Haridwar via bhagwanpur',
                'from_city' => 'Saharanpur',
                'to_city' => 'Haridwar',
                'stops' => ['Saharanpur','Gagalheri', 'Bhagwanpur', 'ImliKhera', 'Bahadrabad', 'Haridwar'],
            ],
            [
                'route_name' => 'Saharanpur to Rishikesh',
                'from_city' => 'Saharanpur',
                'to_city' => 'Rishikesh',
                'stops' => ['Saharanpur','Gagalheri', 'Chhutmalpur','Choli', 'Bhagwanpur', 'Karondi', 'Roorkee','Jwalapur','Bahadrabad', 'Haridwar','Rishikesh'],
            ],

            [
                'route_name' => 'Saharanpur to Rishikesh via bhagwanpur',
                'from_city' => 'Saharanpur',
                'to_city' => 'Rishikesh',
                'stops' => ['Saharanpur','Gagalheri', 'Bhagwanpur', 'ImliKhera', 'Bahadrabad', 'Haridwar','Rishikesh'],
            ],
            [
                'route_name' => 'Dehradun to Haridwar',
                'from_city' => 'Dehradun',
                'to_city' => 'Haridwar',
                'stops' => ['Dehradun', 'Doiwala',  'Haridwar'],
            ],
            [
                'route_name' => 'Dehradun to Rishikesh',
                'from_city' => 'Dehradun',
                'to_city' => 'Rishikesh',
                'stops' => ['Dehradun', 'Doiwala', 'Rishikesh'],
            ],
            [
                'route_name' => 'Roorkee to Haridwar',
                'from_city' => 'Roorkee',
                'to_city' => 'Haridwar',
                'stops' => ['Roorkee', 'Haridwar'],
            ],
            [
                'route_name' => 'Roorkee to Najibabad',
                'from_city' => 'Roorkee',
                'to_city' => 'Najibabad',
                'stops' => ['Roorkee', 'Laksar', 'Najibabad'],
            ],
            [
                'route_name' => 'Haridwar to Rishikesh to Devprayag',
                'from_city' => 'Haridwar',
                'to_city' => 'Devprayag',
                'stops' => ['Haridwar', 'Rishikesh', 'Shivpuri', 'Devprayag'],
            ],
            [
                'route_name' => 'Rishikesh to Badrinath Highway',
                'from_city' => 'Rishikesh',
                'to_city' => 'Badrinath',
                'stops' => ['Rishikesh', 'Devprayag', 'Srinagar', 'Rudraprayag', 'Karnaprayag', 'Joshimath', 'Badrinath'],
            ],
            [
                'route_name' => 'Rishikesh to Kedarnath Highway',
                'from_city' => 'Rishikesh',
                'to_city' => 'Kedarnath',
                'stops' => ['Rishikesh', 'Devprayag', 'Srinagar', 'Rudraprayag', 'Guptkashi', 'Kedarnath'],
            ],
            [
                'route_name' => 'Dehradun to Chakrata Road',
                'from_city' => 'Dehradun',
                'to_city' => 'Chakrata',
                'stops' => ['Dehradun', 'Vikasnagar', 'Kalsi', 'Chakrata'],
            ],
            [
                'route_name' => 'Dehradun to Mussoorie',
                'from_city' => 'Dehradun',
                'to_city' => 'Mussoorie',
                'stops' => ['Dehradun', 'Mussoorie'],
            ],

            // ═══════════════════════════════════════════════
            // CORRIDOR 2: SAHARANPUR ↔ DELHI NCR
            // ═══════════════════════════════════════════════
            [
                'route_name' => 'Saharanpur to Delhi via Muzaffarnagar',
                'from_city' => 'Saharanpur',
                'to_city' => 'Delhi',
                'stops' => ['Saharanpur', 'Deoband', 'Muzaffarnagar', 'Meerut', 'Ghaziabad', 'Delhi'],
            ],

             [
                'route_name' => 'Saharanpur to Delhi via Shamli',
                'from_city' => 'Saharanpur',
                'to_city' => 'Delhi',
                'stops' => ['Saharanpur', 'Rampur', 'Nanuta', 'Thana Bhawan', 'Shamli', 'Baraut', 'Baghpat', 'Delhi'],
            ],
            [
                'route_name' => 'Saharanpur to Muzaffarnagar via Deoband',
                'from_city' => 'Saharanpur',
                'to_city' => 'Muzaffarnagar',
                'stops' => ['Saharanpur', 'Deoband', 'Muzaffarnagar'],
            ],
            [
                'route_name' => 'Saharanpur to Meerut',
                'from_city' => 'Saharanpur',
                'to_city' => 'Meerut',
                'stops' => ['Saharanpur', 'Deoband', 'Muzaffarnagar', 'Meerut'],
            ],
           
            [
                'route_name' => 'Saharanpur to Gangoh ',
                'from_city' => 'Saharanpur',
                'to_city' => 'Kairana',
                'stops' => ['Saharanpur', 'Nakur', 'Gangoh'],
            ],
            [
                'route_name' => 'Muzaffarnagar to Delhi',
                'from_city' => 'Muzaffarnagar',
                'to_city' => 'Delhi',
                'stops' => ['Muzaffarnagar', 'Meerut', 'Ghaziabad', 'Delhi'],
            ],
            [
                'route_name' => 'Muzaffarnagar to Baghpat',
                'from_city' => 'Muzaffarnagar',
                'to_city' => 'Baghpat',
                'stops' => ['Muzaffarnagar', 'Chhaprauli', 'Baghpat'],
            ],
            [
                'route_name' => 'Meerut to Delhi',
                'from_city' => 'Meerut',
                'to_city' => 'Delhi',
                'stops' => ['Meerut', 'Ghaziabad', 'Delhi'],
            ],
            [
                'route_name' => 'Meerut to Hapur',
                'from_city' => 'Meerut',
                'to_city' => 'Hapur',
                'stops' => ['Meerut', 'Hapur'],
            ],
            [
                'route_name' => 'Delhi to Ghaziabad',
                'from_city' => 'Delhi',
                'to_city' => 'Ghaziabad',
                'stops' => ['Delhi', 'Ghaziabad'],
            ],
            [
                'route_name' => 'Delhi to Noida',
                'from_city' => 'Delhi',
                'to_city' => 'Noida',
                'stops' => ['Delhi', 'Noida'],
            ],
            [
                'route_name' => 'Delhi to Gurugram',
                'from_city' => 'Delhi',
                'to_city' => 'Gurugram',
                'stops' => ['Delhi', 'Gurugram'],
            ],
            [
                'route_name' => 'Delhi to Faridabad',
                'from_city' => 'Delhi',
                'to_city' => 'Faridabad',
                'stops' => ['Delhi', 'Faridabad'],
            ],
            [
                'route_name' => 'Kairana to Shamli',
                'from_city' => 'Kairana',
                'to_city' => 'Shamli',
                'stops' => ['Kairana', 'Shamli'],
            ],

           
        ];

        foreach ($routes as $routeData) {
            $stops = $routeData['stops'];
            unset($routeData['stops']);

            $route = Route::create($routeData);

            foreach ($stops as $order => $stop) {
                RouteStop::create([
                    'route_id' => $route->id,
                    'stop_name' => $stop,
                    'stop_order' => $order + 1,
                    'time_offset_minutes' => $order * 60,
                ]);
            }
        }
    }
}
