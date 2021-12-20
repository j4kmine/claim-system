<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Service;
use App\Models\Reports;
use Tests\TestCase;

class DashboardAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_access_servicing_dashboard()
    {
        $this->admin();

        /**
         * Get total count
         */
        $servicing = Service::factory(2)->create();

        $res = $this->postJson('/api/dashboard/servicing/count', [
            'duration' => 'day'
        ], $this->header)->assertStatus(200);
        $res = $this->convertResponseToArray($res);

        $this->assertEquals(2, $res['count'][0]['total']);

        /**
         * Get Servicing Detail
         */

         $res = $this->getJson('/api/dashboard/servicing/details?id=1',
                $this->header)->assertStatus(200);
         $res = $this->convertResponseToArray($res);

         $this->assertEquals($servicing[0]->remarks, $res['servicing']['remarks']);

         /**
          * Get Servicing History
          */
          $res = $this->getJson('/api/dashboard/servicing/history/1',
          $this->header)->assertStatus(200);
          $res = $this->convertResponseToArray($res);

          $this->assertEquals(1, count($res['data']));

          /**
           * Get Servicing by Status
           */
          $res = $this->getJson('/api/dashboard/servicing/upcoming',
          $this->header)->assertStatus(200);
          $res = $this->convertResponseToArray($res);

          $this->assertEquals(2, count($res['data']));

          /**
           * Get Active Servicing
           */
          $res = $this->getJson('/api/dashboard/servicing',
          $this->header)->assertStatus(200);
          $res = $this->convertResponseToArray($res);

          $this->assertEquals(2, count($res['data']));

          /**
           * Get Servicing with status and duration
           */
          $res = $this->getJson('/api/dashboard/servicing/month/upcoming',
          $this->header)->assertStatus(200);
          $res = $this->convertResponseToArray($res);

          $this->assertEquals(2, count($res['data']));
    }

     /** @test */
     public function users_can_access_accidents_dashboard()
     {
         $this->admin();
 
         /**
          * Get total count
          */
         $accidents = Reports::factory(2)->create();
 
         $res = $this->postJson('/api/dashboard/accidents/count', [
             'duration' => 'day'
         ], $this->header)->assertStatus(200);
         $res = $this->convertResponseToArray($res);
 
         $this->assertEquals(2, $res['count'][0]['total']);

         /**
          * Get Servicing Detail
          */

          $res = $this->getJson('/api/dashboard/accidents/details?id=1',
                 $this->header)->assertStatus(200);
          $res = $this->convertResponseToArray($res);
 
          $this->assertEquals($accidents[0]->details, $res['accident']['details']);
 
          /**
           * Get Servicing History
           */
           $res = $this->getJson('/api/dashboard/accidents/history/1',
           $this->header)->assertStatus(200);
           $res = $this->convertResponseToArray($res);
 
           $this->assertEquals(1, count($res['data']));

           /**
            * Get Servicing by Status
            */
           $res = $this->getJson('/api/dashboard/accidents/pending',
           $this->header)->assertStatus(200);
           $res = $this->convertResponseToArray($res);
 
           $this->assertEquals(2, count($res['data']));
 
           /**
            * Get Active Servicing
            */
           $res = $this->getJson('/api/dashboard/accidents',
           $this->header)->assertStatus(200);
           $res = $this->convertResponseToArray($res);
 
           $this->assertEquals(2, count($res['data']));
 
           /**
            * Get Servicing with status and duration
            */
           $res = $this->getJson('/api/dashboard/accidents/month/pending',
           $this->header)->assertStatus(200);
           $res = $this->convertResponseToArray($res);
 
           $this->assertEquals(2, count($res['data']));
           
     }
}
