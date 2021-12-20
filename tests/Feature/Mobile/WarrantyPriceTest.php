<?php

namespace Tests\Feature;

use App\Models\WarrantyPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WarrantyPriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_get_all_the_available_warranty_prices()
    {
        $this->login();

        $prices = WarrantyPrice::factory(2)->create(
            [
                'fuel' => 'non_hybrid',
                'type' => 'preowned'
            ]
        );

        $res = $this->getJson('/api/mobile/prices?car_make='
            . $prices[0]->make . '&car_model='
            . $prices[0]->model . '&fuel='
            . $prices[0]->fuel . '&type=preowned', $this->header)
            ->assertStatus(200);
        $res = $this->convertResponseToArray($res);

        $this->assertNotEquals(0, count($res['data']));
    }
}
