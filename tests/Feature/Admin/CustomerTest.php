<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Reports;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_get_all_customers()
    {
        $this->admin();

        $customer = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

        $response = $this->getJson('/api/customers', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals($customer->email, $response['data'][0]['email']);
        $this->assertEquals($customer2->email, $response['data'][1]['email']);
    }

    /** @test */
    public function admin_can_get_one_customer_details()
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

        $response = $this->getJson('/api/customers/' . $customer->id, $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the data will include vehicles with its warranties and services, 
        $this->assertEquals($customer->email, $response['data']['email']);
        $this->assertNotEquals(0, $response['data']['vehicles'][0]['warranties']);
        $this->assertNotEquals(0, $response['data']['vehicles'][0]['services']);
    }

    /** @test */
    public function admin_can_update_customer_profile()
    {
        $this->admin();

        $customer = Customer::factory()->create();

        $response = $this->putJson('/api/customers/' . $customer->id . '/update', [
            'email' => 'updated@mail.com',
            'phone' => '+656247124124'
        ], $this->header)->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert data must be updated
        $this->assertEquals('updated@mail.com', $response['data']['email']);
        $this->assertEquals('+656247124124', $response['data']['phone']);
    }
}
