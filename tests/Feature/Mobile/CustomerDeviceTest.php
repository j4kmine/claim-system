<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerDeviceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_manage_their_device_ids()
    {
        $this->login();

        $response = $this->postJson('/api/mobile/devices', [
            'device_id' => 'abc'
        ], $this->header)->assertStatus(201);
        $response = $this->convertResponseToArray($response);

        // Assert that user has device id
        $this->assertEquals('abc', Auth::user()->devices[0]->device_id);

        // Remove the device id
        $response = $this->deleteJson('/api/mobile/devices', [
            'device_id' => 'abc'
        ], $this->header)->assertStatus(200);
        $response = $this->convertResponseToArray($response);

        // Assert that user doesnt have any devices
        $this->assertEquals(0, count(Auth::user()->fresh()->devices));
    }
}
