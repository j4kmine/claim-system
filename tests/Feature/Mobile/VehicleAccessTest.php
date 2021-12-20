<?php

namespace Tests\Feature\Mobile;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class VehicleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_give_and_revoke_access_to_another_customer()
    {
        $this->login();

        // Create a customer and vehicle
        $customer = Auth::user();
        $vehicle = Vehicle::factory()->create();

        // Attach the customer that owned the vehicle
        $customer->vehicles()->attach($vehicle);

        // Get All Accessess
        $response = $this->getJson(
            '/api/mobile/vehicles/' . $vehicle->id . '/access',
            $this->header
        )->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert that the customer is the only owned vehicle
        $this->assertEquals($customer->id, $response['data']['owners'][0]['id']);
        $this->assertEquals(0, count($response['data']['users']));

        /**
         * Give access to another customer
         */
        $anotherCustomer = Customer::factory()->create();
        $response = $this->postJson('/api/mobile/vehicles/' . $vehicle->id . '/access', [
            'customer_id' => $customer->id,
            'name' => $anotherCustomer->name,
            'nric' => $anotherCustomer->nric_uen,
            'phone' => $anotherCustomer->phone
        ], $this->header)->assertStatus(200);

        // Get All Accessess
        $response = $this->getJson('/api/mobile/vehicles/' . $vehicle->id . '/access?customer_id=' . $customer->id, $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert that the the vehicle is owned by 1 user, and granted access by 1 user (another customer)
        $this->assertEquals($customer->id, $response['data']['owners'][0]['id']);
        $this->assertEquals($anotherCustomer->id, ($response['data']['users'][0]['id']));

        /**
         * Revoke the access
         */
        $response = $this->deleteJson('/api/mobile/vehicles/' . $vehicle->id . '/access', [
            'nric' => $anotherCustomer->nric_uen
        ], $this->header)->assertStatus(200);

        // Get All Accessess
        $response = $this->getJson('/api/mobile/vehicles/' . $vehicle->id . '/access?customer_id=' . $customer->id, $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert that the customer is the only owned vehicle
        $this->assertEquals($customer->id, $response['data']['owners'][0]['id']);
        $this->assertEquals(0, count($response['data']['users']));
    }
}
