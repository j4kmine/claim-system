<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Reports;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_get_customer_vehicles()
    {
        $this->admin();

        $customer = Customer::factory()->create();

        $vehicle = Vehicle::factory()->create();

        $customer->vehicles()->attach($vehicle);

        $response = $this->get(
            '/api/customers/' . $customer->id . '/vehicles',
            $this->header
        )->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertNotEquals(0, count($response['data']['vehicles']));
        $this->assertEquals($vehicle->registration_no, $response['data']['vehicles'][0]['registration_no']);
    }

    /** @test */
    public function admin_can_get_all_vehicles()
    {
        $this->admin();

        Vehicle::factory(2)->create();

        $response = $this->getJson('/api/vehicles', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals(2, count($response['data']['data']));
    }

    /** @test */
    public function admin_can_get_specific_vehicles()
    {
        $this->admin();

        $customer = Customer::factory()->create();

        $vehicle = Vehicle::factory()->create();

        $customer->vehicles()->attach($vehicle);

        Warranty::factory()->create([
            'vehicle_id' => $vehicle->id
        ]);

        Reports::factory()->create([
            'vehicle_id' => $vehicle->id
        ]);

        Service::factory()->create([
            'vehicle_id' => $vehicle->id
        ]);

        $response = $this->getJson('/api/vehicles/' . $vehicle->id, $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the response return customers, warranties, services, and reports
        $this->assertNotEquals(0, count($response['data']['customers']));
        $this->assertNotEquals(0, count($response['data']['warranties']));
        $this->assertNotEquals(0, count($response['data']['services']));
        $this->assertNotEquals(0, count($response['data']['reports']));
    }
}
