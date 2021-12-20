<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\ServiceType;
use App\Models\ServicingSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ServiceAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function workshop_admin_can_manage_servicing_types()
    {
        $this->admin('workshop', 'admin');

        $company = Company::factory()->create();

        Auth::user()->update([
            'company_id' => $company->id
        ]);

        /**
         * Create new service type
         */
        $response = $this->postJson('/api/service-types', [
            'name' => 'Maintenance',
            'status' => 'active',
            'description' => 'lorem ipsum'
        ], $this->header)->assertStatus(201);

        // Assert the data stored in the database
        $this->assertEquals(1, count(ServiceType::all()));

        /**
         * Get all service types
         */
        $response  = $this->getJson('/api/service-types', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert service types exists
        $this->assertEquals(1, count($response['data']));

        /**
         * Update service type
         */
        $response  = $this->putJson('/api/service-types/' . ServiceType::first()->id . '/update', [
            'name' => 'Maintenance Updated',
            'status' => 'active',
            'description' => 'lorem ipsum'
        ], $this->header)->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the data has been updated
        $this->assertEquals('Maintenance Updated', ServiceType::first()->name);
    }

    /** @test */
    public function workshop_admin_can_manage_servicing_slots()
    {
        $this->admin('workshop', 'admin');

        $company = Company::factory()->create();

        Auth::user()->update([
            'company_id' => $company->id
        ]);

        /**
         * Create new servicing slots
         */
        $response = $this->postJson('/api/companies/slots', [
            [
                'day' => 'Monday',
                'time_start' => '08:00',
                'time_end' => '17:00',
                'interval' => 90,
                'slots_per_interval' => 10,
                'status' => 'active'
            ],
            [
                'day' => 'Tuesday',
                'time_start' => '08:00',
                'time_end' => '17:00',
                'interval' => 90,
                'slots_per_interval' => 10,
                'status' => 'active'
            ]
        ], $this->header)->assertStatus(200);

        // Assert servicing slots stored
        $this->assertEquals(2, count(ServicingSlot::all()));

        /**
         * Update servicing slots
         */
        $response = $this->postJson('/api/companies/slots', [
            [
                'day' => 'Monday',
                'time_start' => '10:00',
                'time_end' => '17:00',
                'interval' => 120,
                'slots_per_interval' => 10,
                'status' => 'active'
            ]
        ], $this->header)->assertStatus(200);

        // Assert the data has been updated
        $slot = ServicingSlot::where('day', 'Monday')->first();
        $this->assertEquals('10:00', $slot->time_start);
        $this->assertEquals(120, $slot->interval);

        /**
         * Get servicing slots
         */
        $response = $this->getJson('/api/companies/slots', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals(2, count($response['data']));
    }
}
