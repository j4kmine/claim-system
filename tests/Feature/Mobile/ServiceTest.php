<?php

namespace Tests\Feature\Mobile;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Company;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\ServiceType;
use App\Models\ServicingSlot;
use App\Activities\BookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customers_can_get_their_services()
    {
        $this->login();

        // Create 2 services for the customer
        $service = Service::factory()->create([
            'customer_id' => Auth::user()->id
        ]);
        $service = Service::factory()->create([
            'customer_id' => Auth::user()->id
        ]);

        $response = $this->getJson('/api/mobile/services', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert that customer have 2 services
        $this->assertEquals(2, count($response['data']));
    }

    /** @test */
    public function customers_can_get_detail_service()
    {
        $this->login();

        // Create a service
        $service = Service::factory()->create([
            'customer_id' => Auth::user()->id
        ]);

        // Get service detail
        $response = $this->getJson('/api/mobile/services/' . $service->id, $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert that service exists
        $this->assertNotEquals(0, count($response['data']));
    }

    /** @test */
    public function customers_cannot_get_detail_service_with_invalid_id()
    {
        $this->login();

        // Get service detail
        $response = $this->getJson('/api/mobile/services/1', $this->header)
            ->assertStatus(403);
    }

    /** @test */
    public function customers_can_book_new_appointment()
    {
        $this->login();
        Notification::fake();

        // Create new models
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $company = Company::factory()->create();
        $serviceType = ServiceType::factory()->create();

        // Create a time slot
        ServicingSlot::factory()->create([
            'workshop_id' => $company->id,
            'day' => now()->addDays(10)->format('l'),
            'time_start' => '09:00',
            'time_end' => '17:00',
            'status' => 'active'
        ]);

        // Book new appointment
        $response = $this->postJson('/api/mobile/services', [
            'vehicle_id' => $vehicle->id,
            'workshop_id' => $company->id,
            'service_type_id' => $serviceType->id,
            'appointment_date' => now()->addDays(10)->format('Y-m-d'),
            'appointment_time' => '10:00'
        ], $this->header)->assertStatus(201);

        $response = $this->convertResponseToArray($response);

        // Assert that customer successfully booked new appointment
        $this->assertEquals('upcoming', $response['data']['status']);
        $this->assertEquals(1, count(Service::all()));

        // Assert that appointments created
        $this->assertEquals(1, count(DB::table('servicing_appointments')->get()));

        Notification::assertSentTo(
            [Auth::user()],
            BookService::class
        );
    }

    /** @test */
    public function customers_cannot_book_appointment_with_invalid_appointment_date()
    {
        $this->login();
        Notification::fake();

        // Create new models
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $company = Company::factory()->create();
        $serviceType = ServiceType::factory()->create();

        // Create a time slot
        ServicingSlot::factory()->create([
            'workshop_id' => $company->id,
            'day' => now()->addDays(10)->format('l'),
            'time_start' => '09:00',
            'time_end' => '17:00',
            'status' => 'active'
        ]);

        /**
         * Book with invalid appointment date
         */
        $response = $this->postJson('/api/mobile/services', [
            'vehicle_id' => $vehicle->id,
            'workshop_id' => $company->id,
            'service_type_id' => $serviceType->id,
            'appointment_date' => now()->addDays(-10)->format('Y-m-d'),
            'appointment_time' => now()->format('H:i')
        ], $this->header)->assertStatus(422);
        $response = $this->convertResponseToArray($response);
        $this->assertEquals('invalid.appointment.date', $response['data']);

        /**
         * Book with invalid timeslot
         */
        // Book with invalid date
        $response = $this->postJson('/api/mobile/services', [
            'vehicle_id' => $vehicle->id,
            'workshop_id' => $company->id,
            'service_type_id' => $serviceType->id,
            'appointment_date' => now()->addDays(11)->format('Y-m-d'),
            'appointment_time' => '07:00'
        ], $this->header)->assertStatus(422);
        $response = $this->convertResponseToArray($response);
        $this->assertEquals('timeslot.not.available', $response['data']);

        // Book with invalid time (after closing)
        $response = $this->postJson('/api/mobile/services', [
            'vehicle_id' => $vehicle->id,
            'workshop_id' => $company->id,
            'service_type_id' => $serviceType->id,
            'appointment_date' => now()->addDays(10)->format('Y-m-d'),
            'appointment_time' => '18:00'
        ], $this->header)->assertStatus(422);
        $response = $this->convertResponseToArray($response);
        $this->assertEquals('timeslot.not.available', $response['data']);

        // Book with invalid time (before opening)
        $response = $this->postJson('/api/mobile/services', [
            'vehicle_id' => $vehicle->id,
            'workshop_id' => $company->id,
            'service_type_id' => $serviceType->id,
            'appointment_date' => now()->addDays(10)->format('Y-m-d'),
            'appointment_time' => '06:00'
        ], $this->header)->assertStatus(422);
        $response = $this->convertResponseToArray($response);
    }

    /** @test */
    public function customers_can_update_their_service()
    {
        $this->login();

        $service = Service::factory()->create([
            'customer_id' => Auth::user()->id
        ]);

        // Update the appointment date and status
        $date = now()->addDays(10)->format('Y-m-d');
        $time = now()->format('H:i');
        $response = $this->putJson('/api/mobile/services/' . $service->id . '/update', [
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => 'canceled'
        ], $this->header)->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $appointmentDate = Carbon::parse($response['data']['appointment_date']);

        // Assert the appointment date and status
        $this->assertEquals($date, $appointmentDate->format('Y-m-d'));
        $this->assertEquals('canceled', $response['data']['status']);
    }

    /** @test */
    public function customers_can_delete_their_appointments()
    {
        $this->login();

        $service = Service::factory()->create();

        // Delete the service
        $response = $this->deleteJson('/api/mobile/services/' . $service->id, [], $this->header)
            ->assertStatus(200);

        $this->assertEquals(0, count(Service::all()));
    }
}
