<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customers_can_get_their_vehicles()
    {
        $this->login();

        // Create user and customer
        $customer = Auth::user();
        $customer2 = Customer::factory()->create();

        // Create 1 owned vehicle, and 1 granted access vehicle
        $ownedVehicle = Vehicle::factory()->create();
        $grantedVehicle = Vehicle::factory()->create();
        $customer->vehicles()->attach($ownedVehicle);
        $customer->vehicles()->attach($grantedVehicle, [
            'granted_by' => $customer2->id
        ]);

        // Get All Vehicles
        $response = $this->getJson('/api/mobile/vehicles', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert user has 2 vehicles
        $this->assertEquals(2, count($response['data']));

        // Get Summary Vehicles
        $response = $this->getJson('/api/mobile/vehicles/summary?customer_id=' . $customer->id, $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the owned and granted vehicles
        $this->assertEquals(1, $response['data']['owned']['count']);
        $this->assertEquals(1, $response['data']['granted_access']['count']);
    }
}
