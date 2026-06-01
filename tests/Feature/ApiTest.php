<?php

namespace Tests\Feature;

use App\Models\Load;
use App\Models\OtpCode;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private array $userData;
    private array $loadData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userData = [
            'full_name' => 'Test User',
            'business_name' => 'Test Business',
            'phone' => '919999999999',
            'city' => 'Saharanpur',
            'market_type' => 'retail',
        ];

        $this->loadData = [
            'from_city' => 'Saharanpur',
            'to_city' => 'Dehradun',
            'vehicle_type' => 'Truck',
            'available_space' => 40,
            'departure_date' => now()->addDay()->toDateString(),
            'departure_time' => '09:00',
            'notes' => 'Test notes',
            'phone' => '919999999991',
        ];

        $route = Route::create([
            'route_name' => 'Saharanpur to Dehradun',
            'from_city' => 'Saharanpur',
            'to_city' => 'Dehradun',
        ]);

        foreach (['Saharanpur', 'Chhutmalpur', 'Biharigarh', 'Mohand', 'Dehradun'] as $i => $stop) {
            RouteStop::create([
                'route_id' => $route->id,
                'stop_name' => $stop,
                'stop_order' => $i + 1,
            ]);
        }
    }

    private function createOtp(string $phone): string
    {
        $otp = '123456';
        OtpCode::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);
        return $otp;
    }

    public function test_user_can_register_with_phone_only(): void
    {
        $response = $this->postJson('/api/register', [
            'phone' => '919999999999',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success', 'message', 'data' => ['token', 'user'],
            ]);

        $this->assertDatabaseHas('users', ['phone' => '919999999999']);
    }

    public function test_send_otp_fails_for_unregistered_phone(): void
    {
        $response = $this->postJson('/api/send-otp', [
            'phone' => '919999999999',
        ]);

        $response->assertStatus(422);
    }

    public function test_registered_user_can_login_via_otp(): void
    {
        User::factory()->create(['phone' => '919999999999']);

        $this->postJson('/api/send-otp', ['phone' => '919999999999']);
        $otp = OtpCode::first()->otp;

        $response = $this->postJson('/api/verify-otp', [
            'phone' => '919999999999',
            'otp' => $otp,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message', 'data' => ['token', 'user'],
            ]);
    }

    public function test_otp_with_invalid_code_fails(): void
    {
        User::factory()->create(['phone' => '919999999999']);

        $response = $this->postJson('/api/verify-otp', [
            'phone' => '919999999999',
            'otp' => '000000',
        ]);

        $response->assertStatus(422);
    }

    public function test_verify_otp_fails_for_unregistered_phone(): void
    {
        // Create OTP directly in DB but no user exists for this phone
        OtpCode::create([
            'phone' => '919999999998',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/verify-otp', [
            'phone' => '919999999998',
            'otp' => '123456',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_register_duplicate_phone(): void
    {
        User::factory()->create(['phone' => '919999999999']);

        $response = $this->postJson('/api/register', [
            'full_name' => 'Test User',
            'business_name' => 'Test Business',
            'phone' => '919999999999',
            'city' => 'Saharanpur',
            'market_type' => 'retail',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_create_load(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/loads', $this->loadData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success', 'message', 'data' => ['id', 'from_city', 'to_city'],
            ]);

        $this->assertDatabaseHas('loads', ['from_city' => 'Saharanpur']);
    }

    public function test_unauthenticated_user_cannot_create_load(): void
    {
        $response = $this->postJson('/api/loads', $this->loadData);

        $response->assertStatus(401);
    }

    public function test_can_view_active_loads(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Load::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'active',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->withToken($token)
            ->getJson('/api/loads');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'per_page', 'total']);
    }

    public function test_can_view_load_details(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $load = Load::factory()->create([
            'user_id' => $user->id,
            'route_id' => 1,
        ]);

        $response = $this->withToken($token)
            ->getJson("/api/loads/{$load->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $load->id);
    }

    public function test_user_can_update_own_load(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $load = Load::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($token)
            ->putJson("/api/loads/{$load->id}", [
                'from_city' => 'Delhi',
                'to_city' => 'Roorkee',
                'vehicle_type' => 'Mini Truck',
                'available_space' => 30,
                'departure_date' => now()->addDays(2)->toDateString(),
                'departure_time' => '14:00',
                'phone' => '919999999991',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('loads', ['id' => $load->id, 'from_city' => 'Delhi']);
    }

    public function test_user_cannot_update_others_load(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $load = Load::factory()->create([
            'user_id' => $owner->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($token)
            ->putJson("/api/loads/{$load->id}", [
                'from_city' => 'Delhi',
                'to_city' => 'Roorkee',
                'vehicle_type' => 'Mini Truck',
                'available_space' => 30,
                'departure_date' => now()->addDays(2)->toDateString(),
                'departure_time' => '14:00',
                'phone' => '919999999991',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_load(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $load = Load::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($token)
            ->deleteJson("/api/loads/{$load->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('loads', ['id' => $load->id]);
    }

    public function test_user_can_mark_load_as_completed(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $load = Load::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($token)
            ->patchJson("/api/loads/{$load->id}/complete");

        $response->assertStatus(200);
        $this->assertDatabaseHas('loads', ['id' => $load->id, 'status' => 'completed']);
    }

    public function test_user_can_view_my_loads(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Load::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Load::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Load::factory()->create(['user_id' => $user->id, 'status' => 'expired']);

        $response = $this->withToken($token)->getJson('/api/my-loads');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['active', 'completed', 'expired']]);
    }

    public function test_user_can_view_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonPath('data.full_name', $user->full_name);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson('/api/profile', [
                'business_name' => 'Updated Business',
                'city' => 'Dehradun',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'business_name' => 'Updated Business',
            'city' => 'Dehradun',
        ]);
    }

    public function test_user_can_view_routes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/routes');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_user_can_find_route_based_matches(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Sharma Furniture: Saharanpur→Dehradun (full route coverage)
        $load = Load::factory()->create([
            'user_id' => $user->id,
            'from_city' => 'Saharanpur',
            'to_city' => 'Dehradun',
            'route_id' => 1,
            'status' => 'active',
            'expires_at' => now()->addHours(24),
        ]);

        // Gupta Hardware: wants Biharigarh→Dehradun
        // Should match with Sharma's load because Biharigarh is a stop on Route 1
        $response = $this->withToken($token)
            ->getJson('/api/matches?from_city=Biharigarh&to_city=Dehradun');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $load->id);

        // Also test that exact matches still work
        $response2 = $this->withToken($token)
            ->getJson('/api/matches?from_city=Saharanpur&to_city=Dehradun&exclude_load_id=' . $load->id);

        $response2->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_route_segment_covering_works_correctly(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Load from Chhutmalpur→Mohand (partial route segment)
        // Should match for Biharigarh→Dehradun search because:
        // Chhutmalpur(order 2) <= Biharigarh(order 3) AND Mohand(order 4) <= Dehradun(order 5)
        // Wait: load's to_city(order 4) must be >= request's to_city(order 5)
        // Mohand is order 4, Dehradun is order 5. 4 < 5, so this would NOT match.
        // Let's create a correct scenario:

        // Load from Saharanpur→Mohand (covers Saharanpur through Mohand)
        $load = Load::factory()->create([
            'user_id' => $user->id,
            'from_city' => 'Saharanpur',
            'to_city' => 'Mohand',
            'route_id' => 1,
            'status' => 'active',
            'expires_at' => now()->addHours(24),
        ]);

        // Search from Chhutmalpur→Biharigarh
        // Saharanpur(order 1) <= Chhutmalpur(order 2) AND Mohand(order 4) >= Biharigarh(order 3)
        // Yes! This should match.
        $response = $this->withToken($token)
            ->getJson('/api/matches?from_city=Chhutmalpur&to_city=Biharigarh');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $load->id);
    }

    public function test_user_can_save_and_unsave_load(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $load = Load::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'active',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->withToken($token)
            ->postJson("/api/saved-loads/{$load->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.saved', true);

        $this->assertDatabaseHas('saved_loads', [
            'user_id' => $user->id,
            'load_id' => $load->id,
        ]);

        $response = $this->withToken($token)
            ->postJson("/api/saved-loads/{$load->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.saved', false);

        $this->assertDatabaseMissing('saved_loads', [
            'user_id' => $user->id,
            'load_id' => $load->id,
        ]);
    }

    public function test_user_can_view_saved_loads(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/saved-loads');

        $response->assertStatus(200);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_validation_errors_returned(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/loads', [
                'from_city' => '',
                'to_city' => '',
            ]);

        $response->assertStatus(422);
    }

    public function test_feed_search_returns_route_based_results(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $load = Load::factory()->create([
            'user_id' => $user->id,
            'from_city' => 'Saharanpur',
            'to_city' => 'Dehradun',
            'route_id' => 1,
            'status' => 'active',
            'departure_date' => now()->addDay()->toDateString(),
            'departure_time' => '09:00',
            'expires_at' => now()->addDay()->addHours(10),
        ]);

        $response = $this->withToken($token)
            ->getJson('/api/loads?from_city=Saharanpur&to_city=Dehradun');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $load->id);

        // Also test intermediate stop search
        $response2 = $this->withToken($token)
            ->getJson('/api/loads?from_city=Chhutmalpur&to_city=Biharigarh');

        $response2->assertStatus(200)
            ->assertJsonPath('data.0.id', $load->id);
    }

    public function test_feed_search_excludes_expired_loads(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Load with past departure
        $pastLoad = Load::factory()->create([
            'user_id' => $user->id,
            'from_city' => 'Saharanpur',
            'to_city' => 'Dehradun',
            'route_id' => 1,
            'status' => 'active',
            'departure_date' => now()->subDay()->toDateString(),
            'departure_time' => '09:00',
            'expires_at' => now()->subDay()->addHours(10),
        ]);

        $response = $this->withToken($token)
            ->getJson('/api/loads?from_city=Saharanpur&to_city=Dehradun');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }
}
