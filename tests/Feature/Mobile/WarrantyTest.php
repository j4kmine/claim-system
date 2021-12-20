<?php

namespace Tests\Feature\Mobile;

use App\Activities\BuyWarranty;
use App\Activities\EnquiryWarranty;
use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\WarrantyPrice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarrantyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customers_can_buy_warranties()
    {
        $this->login();
        Notification::fake();

        $customer = Auth::user();
        $vehicle = Vehicle::factory()->create();

        $price = WarrantyPrice::factory()->create();

        $customer->vehicles()->attach($vehicle);

        /**
         * Fake the uploaded file for log card and assesment report
         */
        $logCard = UploadedFile::fake()->image('log_card.jpg');
        $AssesmentReport = UploadedFile::fake()->image('assesment_report.jpg');

        $response = $this->postJson('/api/mobile/warranties/buy', [
            'warranty_price_id' => $price->id,
            'vehicle_id' => $vehicle->id,
            'car_make' => $vehicle->make,
            'car_model' => $vehicle->model,
            'is_pre_owned' => 1,
            'is_hybrid' => 1,
            'mileage' => $vehicle->mileage,
            'manufacture_year' => '2020',
            'registration_date' => '2020-10-10',
            'chassis_no' => $vehicle->chassis_no,
            'engine_no' => 'x123',
            'nric_uen' => 'b123',
            'salutation' => 'Mr',
            'driver_name' => 'John',
            'driver_address' => 'Indonesia',
            'driver_email' => 'john@mail,com',
            'driver_number' => '61421424124',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'log_card' => $logCard,
            'assesment_report' => $AssesmentReport
        ], $this->header)->assertStatus(201);
        $response = $this->convertResponseToArray($response);

        // Assert the data
        $this->assertEquals('pending-payment', $response['data']['status']);
        $this->assertEquals($price->mileage_coverage, $response['data']['mileage_coverage']);
        $this->assertEquals($price->price, $response['data']['price']);

        Notification::assertSentTo(
            [Auth::user()],
            BuyWarranty::class
        );
    }

    /** @test */
    public function customers_need_provides_mileage_and_assesment_report_for_pre_owned()
    {
        $this->login();
        Notification::fake();

        $customer = Auth::user();
        $vehicle = Vehicle::factory()->create();

        $price = WarrantyPrice::factory()->create();

        $customer->vehicles()->attach($vehicle);

        /**
         * Fake the uploaded file for log card and assesment report
         */
        $logCard = UploadedFile::fake()->image('log_card.jpg');

        $response = $this->postJson('/api/mobile/warranties/buy', [
            'warranty_price_id' => $price->id,
            'vehicle_id' => $vehicle->id,
            'car_make' => $vehicle->make,
            'car_model' => $vehicle->model,
            'is_pre_owned' => 1,
            'is_hybrid' => 1,
            'manufacture_year' => '2020',
            'registration_date' => '2020-10-10',
            'chassis_no' => $vehicle->chassis_no,
            'engine_no' => 'x123',
            'nric_uen' => 'b123',
            'salutation' => 'Mr',
            'driver_name' => 'John',
            'driver_address' => 'Indonesia',
            'driver_email' => 'john@mail,com',
            'driver_number' => '61421424124',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'log_card' => $logCard,
        ], $this->header)->assertStatus(422);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals('missing.mileage.or.assesment-report', $response['data']);
    }

    /** @test */
    public function customers_can_enquiry_warranties()
    {
        $this->login();
        Notification::fake();

        $customer = Auth::user();
        $vehicle = Vehicle::factory()->create();

        $customer->vehicles()->attach($vehicle);

        $logCard = UploadedFile::fake()->image('log_card.jpg');

        $AssesmentReport = UploadedFile::fake()->image('assesment_report.jpg');

        $response = $this->postJson('/api/mobile/warranties/enquiry', [
            'vehicle_id' => $vehicle->id,
            'car_make' => $vehicle->make,
            'car_model' => $vehicle->model,
            'is_pre_owned' => 0,
            'is_hybrid' => 1,
            'mileage' => $vehicle->mileage,
            'manufacture_year' => '2020',
            'registration_date' => '2020-10-10',
            'chassis_no' => $vehicle->chassis_no,
            'engine_no' => 'x123',
            'nric_uen' => 'b123',
            'salutation' => 'Mr',
            'driver_name' => 'John',
            'driver_address' => 'Indonesia',
            'driver_email' => 'john@mail,com',
            'driver_number' => '61421424124',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'log_card' => $logCard,
            'assesment_report' => $AssesmentReport
        ], $this->header)->assertStatus(201);
        $response = $this->convertResponseToArray($response);

        // Assert data
        $this->assertEquals('pending-enquiry', $response['data']['status']);
        $this->assertFalse(isset($response['data']['price']));

        Notification::assertSentTo(
            [Auth::user()],
            EnquiryWarranty::class
        );
    }
}
