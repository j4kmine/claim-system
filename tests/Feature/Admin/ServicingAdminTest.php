<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\ServicingSlot;
use App\Models\WarrantyPrice;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ServicingAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_get_all_servicing_appointments()
    {
        $this->admin('workshop');

        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        Service::factory()->create([
            'workshop_id' => $company1->id
        ]);

        Service::factory()->create([
            'workshop_id' => $company2->id
        ]);

        $response = $this->getJson('/api/servicing', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals(2, count($response['data']));

        /**
         * Filter by Workshop ID
         */
        $response = $this->getJson('/api/servicing?workshop_id=' . $company1->id, $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals(1, count($response['data']));
    }

    /** @test */
    public function users_can_create_new_appointment()
    {
        $this->admin('workshop');

        $company = Company::factory()->create();

        $slot = ServicingSlot::factory()->create([
            'workshop_id' => $company->id
        ]);

        $type = ServiceType::factory()->create([
            'workshop_id' => $company->id
        ]);

        $response = $this->postJson('/api/servicing', [
            'date' => now()->format('Y-m-d'),
            'time' => $slot->time_start,
            'workshop_id' => $company->id,
            'service_type_id' => $type->id,
            'customer_name' => 'John Doe',
            'nric' => '123',
            'phone' => '+65712312',
            'email' => 'john@mail.com',
            'vehicle_number' => 'SG123ABC',
            'vehicle_make' => 'BMW',
            'vehicle_model' => 'i302'
        ], $this->header)
            ->assertStatus(201);

        // Update Service
        $response = $this->putJson('/api/servicing/1/update', [
            'status' => 'completed'
        ], $this->header)->assertStatus(200);
    }

    /** @test */
    public function users_cannot_create_new_appointment_with_invalid_timeslot()
    {
        $this->admin('workshop');

        $company = Company::factory()->create();

        $slot = ServicingSlot::factory()->create([
            'workshop_id' => $company->id
        ]);

        $type = ServiceType::factory()->create([
            'workshop_id' => $company->id
        ]);

        // Invalid time slot
        $res = $this->postJson('/api/servicing', [
            'date' => now()->format('Y-m-d'),
            'time' => '23:00',
            'workshop_id' => $company->id,
            'service_type_id' => $type->id,
            'customer_name' => 'John Doe',
            'nric' => '123',
            'phone' => '+65712312',
            'email' => 'john@mail.com',
            'vehicle_number' => 'SG123ABC',
            'vehicle_make' => 'BMW',
            'vehicle_model' => 'i302'
        ], $this->header)->assertStatus(500);

        $this->assertEquals('timeslot.not.available', $res['data']);

        // Invalid time slot
        $res = $this->postJson('/api/servicing', [
            'date' => now()->addDays(1)->format('Y-m-d'),
            'time' => $slot->time_start,
            'workshop_id' => $company->id,
            'service_type_id' => $type->id,
            'customer_name' => 'John Doe',
            'nric' => '123',
            'phone' => '+65712312',
            'email' => 'john@mail.com',
            'vehicle_number' => 'SG123ABC',
            'vehicle_make' => 'BMW',
            'vehicle_model' => 'i302'
        ], $this->header)->assertStatus(500);

        $this->assertEquals('timeslot.not.available', $res['data']);
    }

    /** @test */
    public function users_can_add_servicing_reports()
    {
        $this->admin();

        $service = Service::factory()->create();

        /**
         * Save Servicing Report
         */
        $response = $this->postJson('/api/servicing/' . $service->id . '/reports', [
            'servicing_status' => 'completed',
            'total_amount' => 9.99,
            'remarks' => 'test',
            'all_cars_remarks' => 'test2'
        ], $this->header)->assertStatus(200);

        /**
         * Get Servicing Report
         */
        $response = $this->getJson('/api/servicing/' . $service->id . '/reports', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertNotEquals(0, count($response['data']));
    }

    /** @test */
    public function users_can_upload_documents_to_reports()
    {
        $this->admin();

        $service = Service::factory()->create();

        /**
         * Save Servicing Report documents
         */
        $res = $this->postJson('/api/servicing/' . $service->id . '/reports/documents', [
            'file' => UploadedFile::fake()->image('docs.pdf')->size(100),
        ], $this->header)->assertStatus(201);

        $this->assertEquals(1, count($service->servicing_reports[0]->documents));

        /**
         * Save Servicing Report invoices
         */
        $res = $this->postJson('/api/servicing/' . $service->id . '/reports/invoices', [
            'file' => UploadedFile::fake()->image('docs.pdf')->size(100),
        ], $this->header)->assertStatus(201);

        $this->assertEquals(1, count($service->servicing_reports[0]->invoices));

        /**
         * Delete Servicing Report documents
         */
        $res = $this->deleteJson('/api/servicing-reports/documents/1', [], $this->header)->assertStatus(200);

        $this->assertEquals(0, count($service->fresh()->servicing_reports[0]->documents));

        /**
         * Delete Servicing Report invoices
         */
        $res = $this->deleteJson('/api/servicing-reports/invoices/1', [], $this->header)->assertStatus(200);

        $this->assertEquals(0, count($service->fresh()->servicing_reports[0]->invoices));
    }

    /** @test */
    public function users_cannot_add_servicing_reports_with_invalid_data()
    {
        $this->admin();

        $service = Service::factory()->create();

        /**
         * Save Servicing Report
         */
        $response = $this->postJson('/api/servicing/' . $service->id . '/reports', [
            'servicing_status' => 'unknown',
            'total_amount' => 9.99,
            'remarks' => 'test',
            'all_cars_remarks' => 'test2'
        ], $this->header)->assertStatus(422);

        /**
         * Save Servicing Report documents
         */
        $res = $this->postJson('/api/servicing/' . $service->id . '/reports/documents', [
            'file' => UploadedFile::fake()->image('image.png')->size(100),
        ], $this->header)->assertStatus(422);

        /**
         * Save Servicing Report invoices
         */
        $res = $this->postJson('/api/servicing/' . $service->id . '/reports/invoices', [
            'file' => UploadedFile::fake()->image('image.png')->size(100),
        ], $this->header)->assertStatus(422);
    }

    /** @test */
    public function users_can_get_car_models_and_makes()
    {
        $this->admin();

        WarrantyPrice::factory(3)->create();

        $res = $this->getJson('/api/servicing/car-models', $this->header)->assertStatus(200);
        $res = $this->convertResponseToArray($res);

        $this->assertEquals(3, count($res['data']));

        $res = $this->getJson('/api/servicing/car-makes', $this->header)->assertStatus(200);
        $res = $this->convertResponseToArray($res);

        $this->assertEquals(3, count($res['data']));
    }
}
