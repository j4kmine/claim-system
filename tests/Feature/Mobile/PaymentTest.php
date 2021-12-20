<?php

namespace Tests\Feature\Mobile;

use App\Models\Warranty;
use App\Models\WarrantyPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customers_can_checkout_warranty_using_paynow()
    {
        $this->login();

        $warranty = Warranty::factory()->create([
            'customer_id' => Auth::user()->id
        ]);

        // Get the payment reference
        $response = $this->postJson('/api/mobile/warranties/paynow-ref', [
            'warranty_id' => $warranty->id
        ], $this->header)->assertStatus(201);
        $response = $this->convertResponseToArray($response);

        // Assert that the return will have reference
        $this->assertNotEmpty($response['data']['reference']);

        // Complete the payment
        $reference = $response['data']['reference'];
        $response = $this->postJson('/api/mobile/warranties/paynow', [
            'warranty_id' => $warranty->id,
            'reference' => $reference
        ], $this->header)->assertStatus(200);
    }
}
