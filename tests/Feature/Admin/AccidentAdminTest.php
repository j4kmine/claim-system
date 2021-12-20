<?php

namespace Tests\Feature\Admin;

use App\Models\AccidentReport;
use Tests\TestCase;
use App\Models\Reports;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccidentAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_get_accident_reports()
    {
        $this->admin();

        Reports::factory(2)->create();

        // Get list of accident
        $response = $this->getJson('/api/accidents', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertEquals(2, count($response['data']['data']));

        // Get one accident
        $response = $this->getJson('/api/accidents/1', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->assertArrayHasKey('vehicle', $response['data']);
        $this->assertArrayHasKey('customer', $response['data']);
        $this->assertArrayHasKey('driver', $response['data']);
        $this->assertArrayHasKey('vehicle_involves', $response['data']);
        $this->assertArrayHasKey('documents', $response['data']);
    }

    /** @test */
    public function users_cannot_get_accident_reports_with_invalid_id()
    {
        $this->admin();

        $res = $this->getJson('/api/accidents/1', $this->header)
            ->assertStatus(404);
    }

    /** @test */
    public function users_can_update_accident_status()
    {
        /**
         * Update from All Cars
         */
        $this->admin();

        $accident = Reports::factory()->create();

        $res = $this->postJson('/api/accidents/' . $accident->id . '/reports', [
            'remarks' => 'Remarks from All Cars',
            'accident_status' => 'completed'
        ], $this->header)->assertStatus(200);
        $res = $this->convertResponseToArray($res);

        $this->assertEquals('Remarks from All Cars', $accident->reports[0]->all_cars_remarks);
        $this->assertEquals('completed', $res['data']['status']);

        /**
         * Update from Workshop
         */
        $this->admin('workshop');

        $res = $this->postJson('/api/accidents/' . $accident->id . '/reports', [
            'remarks' => 'Remarks from Workshop',
            'accident_status' => 'completed'
        ], $this->header)->assertStatus(200);
        $res = $this->convertResponseToArray($res);

        $this->assertEquals('Remarks from Workshop', $accident->fresh()->reports[0]->remarks);
        $this->assertEquals('completed', $res['data']['status']);
    }

    /** @test */
    public function user_cannot_update_with_invalid_data()
    {
        $this->admin();

        $accident = Reports::factory()->create();

        $res = $this->postJson('/api/accidents/' . $accident->id . '/reports', [
            'remarks' => 'Remarks from Workshop',
            'accident_status' => 'unknown'
        ], $this->header)->assertStatus(422);
    }

    /** @test */
    public function user_can_upload_documents()
    {
        $this->admin();

        $accident = Reports::factory()->create();

        $res = $this->postJson('/api/accidents/' . $accident->id . '/documents', [
            'file' => UploadedFile::fake()->image('docs.jpg')->size(100),
            'type' => 'accident_report'
        ], $this->header)->assertStatus(200);

        // Assert report has documents
        $this->assertEquals(1, count($accident->reports[0]->documents));

        /**
         * Removing Documents
         */
        $res = $this->deleteJson('/api/accident-reports/documents/1', [], $this->header)
            ->assertStatus(200);

        // Assert report has documents
        $this->assertEquals(0, count($accident->reports[0]->fresh()->documents));
    }
}
