<?php

namespace Tests;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public $header = [];

    public function setUp(): void
    {
        parent::setUp();
        // $this->artisan('db:seed');
    }

    /**
     * Helper function to convert JsonResponse into an array
     * 
     * @param JsonResponse $response
     * @return array 
     */
    public function convertResponseToArray($response): array
    {
        return json_decode(json_encode($response->getData()), true);
    }

    public function login()
    {
        $customer = Customer::factory()->create([
            'salutation' => 'Company'
        ]);

        $this->actingAs($customer);

        $response = $this->postJson('/api/mobile/login', [
            'firebase_token' => 'abc',
            'nric_uen' => $customer->nric_uen,
            'phone' => $customer->phone
        ])->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->header = [
            'Authorization' => 'Bearer ' . explode('|', $response['access_token'])[1]
        ];
    }

    public function admin(string $category = 'all_cars', string $role = 'admin')
    {
        $user = User::factory()->create([
            'category' => $category,
            'role' => $role,
            'password' => bcrypt('password')
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ])->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        $this->header = [
            'Authorization' => 'Bearer ' . explode('|', $response['access_token'])[1]
        ];
    }
}
