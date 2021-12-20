<?php

namespace Tests\Feature\Mobile;

use App\Activities\AccidentInspection;
use Tests\TestCase;
use App\Models\Company;
use App\Models\Reports;
use App\Models\Vehicle;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccidentReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_submit_and_update_the_report()
    {
        $this->login();
        Notification::fake();

        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $company = Company::factory()->create();

        $customer->vehicles()->attach($vehicle);

        $response = $this->postJson('/api/mobile/reports', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_make' => 'BMW',
            'vehicle_model' => '123',
            'insurance_company' => 'Company XYZ',
            'certificate_number' => 'Cert 123',
            'insured_nric' => 'nric x123',
            'insured_name' => 'insured name',
            'insured_contact_number' => '624125125',
            'date_of_accident' => now()->addDays(-100)->format('Y-m-d'),
            'time_of_accident' => '10:10',
            'location_of_accident' => 'Indonesia',
            'weather_road_condition' => 'Clear & Dry',
            'reporting_type' => 'Reporting Only',
            'number_of_passengers' => 2,
            'is_video_captured' => 0,
            'purpose_of_use' => 'Private Use',
            'is_owner_drives' => 1,
            'owner_driver_relationship' => 'Spouse',
            'is_visiting_workshop' => 1,
            'workshop_id' => $company->id,
            'workshop_visit_date' => now()->addDays(100)->format('Y-m-d'),
            'workshop_visit_time' => '10:10'
        ], $this->header)->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the data successfully stored
        $this->assertEquals(1, count(Reports::all()));
        $this->assertEquals('BMW', $response['data']['vehicle_make']);
        $this->assertEquals('624125125', $response['data']['insured_contact_number']);
        $this->assertEquals('Reporting Only', $response['data']['reporting_type']);

        /**
         * Update the report
         */
        $report = Reports::first();
        $response = $this->putJson('/api/mobile/reports/' . $report->id . '/update', [
            'customer_id' => $customer->id,
            'weather_road_condition' => 'After Rain & Wet',
            'reporting_type' => 'Claim Own Insurance',
        ], $this->header)->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals('After Rain & Wet', $response['data']['weather_road_condition']);
        $this->assertEquals('Claim Own Insurance', $response['data']['reporting_type']);

        Notification::assertSentTo(
            [Auth::user()],
            AccidentInspection::class
        );
    }

    /** @test */
    public function customer_cannot_submit_invalid_date()
    {
        $this->login();
        Notification::fake();

        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $company = Company::factory()->create();

        $customer->vehicles()->attach($vehicle);

        /**
         * Submit invalid date of accident
         */
        $response = $this->postJson('/api/mobile/reports', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_make' => 'BMW',
            'vehicle_model' => '123',
            'insurance_company' => 'Company XYZ',
            'certificate_number' => 'Cert 123',
            'insured_nric' => 'nric x123',
            'insured_name' => 'insured name',
            'insured_contact_number' => '624125125',
            'date_of_accident' => now()->addDays(100)->format('Y-m-d'),
            'time_of_accident' => '10:10',
            'location_of_accident' => 'Indonesia',
            'weather_road_condition' => 'Clear & Dry',
            'reporting_type' => 'Reporting Only',
            'number_of_passengers' => 2,
            'is_video_captured' => 0,
            'purpose_of_use' => 'Private Use',
            'is_owner_drives' => 1,
            'owner_driver_relationship' => 'Spouse',
            'is_visiting_workshop' => 1,
            'workshop_id' => $company->id,
            'workshop_visit_date' => now()->addDays(100)->format('Y-m-d'),
            'workshop_visit_time' => '10:10'
        ], $this->header)->assertStatus(422); // Invalid date of accident
        $response = $this->convertResponseToArray($response);

        /**
         * Submit workshop visit date
         */
        $response = $this->postJson('/api/mobile/reports', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_make' => 'BMW',
            'vehicle_model' => '123',
            'insurance_company' => 'Company XYZ',
            'certificate_number' => 'Cert 123',
            'insured_nric' => 'nric x123',
            'insured_name' => 'insured name',
            'insured_contact_number' => '624125125',
            'date_of_accident' => now()->addDays(-100)->format('Y-m-d'),
            'time_of_accident' => '10:10',
            'location_of_accident' => 'Indonesia',
            'weather_road_condition' => 'Clear & Dry',
            'reporting_type' => 'Reporting Only',
            'number_of_passengers' => 2,
            'is_video_captured' => 0,
            'purpose_of_use' => 'Private Use',
            'is_owner_drives' => 1,
            'owner_driver_relationship' => 'Spouse',
            'is_visiting_workshop' => 1,
            'workshop_id' => $company->id,
            'workshop_visit_date' => now()->addDays(-100)->format('Y-m-d'),
            'workshop_visit_time' => '10:10'
        ], $this->header)->assertStatus(422); // Invalid date of accident
        $response = $this->convertResponseToArray($response);
    }

    /** @test */
    public function customer_cannot_submit_invalid_data()
    {
        $this->login();
        Notification::fake();

        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $company = Company::factory()->create();

        $customer->vehicles()->attach($vehicle);

        /**
         * Submit invalid data:
         * 1. Invalid weather road condition
         * 2. Invalid reporting type
         * 3. Invalid Purpose of Use
         * 4. Invalid Owner Driver Relationships
         */
        $response = $this->postJson('/api/mobile/reports', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_make' => 'BMW',
            'vehicle_model' => '123',
            'insurance_company' => 'Company XYZ',
            'certificate_number' => 'Cert 123',
            'insured_nric' => 'nric x123',
            'insured_name' => 'insured name',
            'insured_contact_number' => '624125125',
            'date_of_accident' => now()->addDays(-100)->format('Y-m-d'),
            'time_of_accident' => '10:10',
            'location_of_accident' => 'Indonesia',
            'weather_road_condition' => 'Invalid Clear & Dry',
            'reporting_type' => 'Invalid - Reporting Only',
            'number_of_passengers' => 2,
            'is_video_captured' => 0,
            'purpose_of_use' => 'Invalid - Private Use',
            'is_owner_drives' => 1,
            'owner_driver_relationship' => 'Invalid - Spouse',
            'is_visiting_workshop' => 1,
            'workshop_id' => $company->id,
            'workshop_visit_date' => now()->addDays(100)->format('Y-m-d'),
            'workshop_visit_time' => '10:10'
        ], $this->header)->assertStatus(422);
        $response = $this->convertResponseToArray($response);

        $this->assertArrayHasKey('weather_road_condition', $response['errors']);
        $this->assertArrayHasKey('reporting_type', $response['errors']);
        $this->assertArrayHasKey('purpose_of_use', $response['errors']);
        $this->assertArrayHasKey('owner_driver_relationship', $response['errors']);
    }

    /** @test */
    public function customer_can_save_the_driver_and_another_vehicles_that_involved()
    {
        $this->login();

        $accident = Reports::factory()->create();

        // Set the driver
        $response = $this->postJson('/api/mobile/reports/' . $accident->id . '/drivers', [
            'customer_id' => Customer::first()->id,
            'name' => 'Driver Name',
            'nric' => 'Driver NRIC',
            'dob' => '1996-10-10',
            'license_pass_date' => '2020-10-10',
            'address' => 'Indonesia',
            'contact_number' => '62818248124',
            'email' => 'mail@mail.com',
            'occupations' => 'Working Indoors'
        ], $this->header)->assertStatus(201);
        $response = $this->convertResponseToArray($response);

        // Assert the data
        $this->assertEquals('Driver NRIC', $response['data']['nric']);
        $this->assertEquals('Working Indoors', $response['data']['occupations']);

        // Save another vehicles that involved
        $response = $this->postJson('/api/mobile/reports/' . $accident->id . '/involves', [
            'customer_id' => Customer::first()->id,
            'vehicle_plate_number' => 'AB2414',
            'vehicle_make' => 'BMW',
            'vehicle_model' => '123',
            'driver_name' => 'Driver Name',
            'driver_nric' => 'Driver NRIC',
            'driver_contact_number' => '6124124124',
            'driver_address' => 'Indonesia'
        ], $this->header)->assertStatus(201);
        $response = $this->convertResponseToArray($response);

        // Assert the data
        $this->assertEquals('AB2414', $response['data']['vehicle_plate_number']);
        $this->assertEquals('6124124124', $response['data']['driver']['contact_number']);
    }
}
