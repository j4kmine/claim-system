<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ServiceType;
use App\Models\ServicingSlot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WorkshopAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_access_workshops()
    {
        $this->admin('all_cars');

        $company = Company::factory(4)->create([
            'type' => 'workshop'
        ]);

        $response = $this->getJson('/api/workshops', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals(4, count($response['data']));
    }

    /** @test */
    public function users_can_access_service_types_belongs_to_workshop()
    {
        $this->admin('all_cars');

        $company = Company::factory()->create([
            'type' => 'workshop'
        ]);

        ServiceType::factory(2)->create([
            'workshop_id' => $company->id
        ]);

        $response = $this->getJson('/api/workshops/' . $company->id . '/service-types', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals(2, count($response['data']));
    }

    /** @test */
    public function users_can_access_available_time_slots()
    {
        $this->admin('all_cars');

        $company = Company::factory()->create([
            'type' => 'workshop'
        ]);

        ServicingSlot::factory()->create([
            'day' => 'Sunday',
            'workshop_id' => $company->id
        ]);

        ServicingSlot::factory()->create([
            'day' => 'Monday',
            'workshop_id' => $company->id
        ]);

        $response = $this->getJson('/api/workshops/' . $company->id . '/slots', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals(2, count($response['data']));
        $this->assertEquals('Sunday', $response['data'][0]['day']);
        $this->assertEquals('Monday', $response['data'][1]['day']);

        /**
         * Generate available times
         */
        $date = Carbon::createFromFormat('l', 'Sunday');
        $response = $this->getJson('/api/workshops/' . $company->id . '/times?date=' . $date->format('Y-m-d'), $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertTrue(count($response['data']) > 5);
    }
}
