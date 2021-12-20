<?php

namespace Tests\Feature\Mobile;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customers_can_get_all_their_settings()
    {
        $this->login();

        // Create two new settings
        $settingOne = Setting::factory()->create();
        $settingTwo = Setting::factory()->create([
            'name' => 'Email',
            'key' => 'notifications.email',
        ]);
        $customer = Customer::factory()->create();

        // Attach the settings to the customer
        $customer->settings()->attach($settingOne, [
            'value' => json_encode(true)
        ]);

        $customer->settings()->attach($settingTwo, [
            'value' => json_encode(false)
        ]);

        $response = $this->getJson('/api/mobile/settings?customer_id=' . $customer->id, $this->header)
            ->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the user will have 2 settings
        $this->assertEquals(2, count($response['data']));
    }

    /** @test */
    public function customers_can_update_their_settings()
    {
        $this->login();

        // Create a setting
        $setting = Setting::factory()->create([
            'key' => 'notifications.app'
        ]);

        $customer = Customer::factory()->create();

        // Attach the settings to the customer
        $customer->settings()->attach($setting, [
            'value' => json_encode(true)
        ]);

        // Update notifications.app to false
        $response = $this->putJson('/api/mobile/settings', [
            'customer_id' => $customer->id,
            'key' => 'notifications.app',
            'value' => json_encode(false)
        ], $this->header)->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert the notifications.app false
        $this->assertEquals('false', $response['data'][0]['pivot']['value']);
    }
}
