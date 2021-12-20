<?php

namespace Tests\Feature\Mobile;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_get_their_profiles()
    {
        $this->login();

        $response = $this->getJson('/api/mobile/profiles', $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the data
        $this->assertEquals(Auth::user()->email, $response['data']['email']);
        $this->assertEquals(Auth::user()->name, $response['data']['name']);
        $this->assertEquals(Auth::user()->phone, $response['data']['phone']);
    }

    /** @test */
    public function customer_can_update_their_profiles()
    {
        $this->login();

        $customer = Customer::factory()->create();

        $response = $this->postJson('/api/mobile/profiles/update', [
            'customer_id' => $customer->id,
            'email' => 'updatedmail@mail.com',
            'nric' => 'updatednric',
            'dob' => '1996-10-10',
            'mobile' => '6277464793'
        ], $this->header)->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the updated data
        $this->assertEquals('updatedmail@mail.com', $response['data']['email']);
        $this->assertEquals('updatednric', $response['data']['nric_uen']);
        $this->assertEquals('1996-10-10', $response['data']['date_of_birth']);
        $this->assertEquals('6277464793', $response['data']['phone']);
    }

    /** @test */
    public function customer_cannot_update_with_ununique_email()
    {
        $this->login();

        $customer = Customer::factory()->create();

        Customer::factory()->create([
            'email' => 'user@mail.com'
        ]);

        $response = $this->postJson('/api/mobile/profiles/update', [
            'customer_id' => $customer->id,
            'email' => 'user@mail.com',
            'nric' => 'updatednric',
            'dob' => '1996-10-10',
            'mobile' => '6277464793'
        ], $this->header)->assertStatus(422);
        $response = $this->convertResponseToArray($response);

        $this->assertArrayHasKey('email', $response['errors']);
    }
}
