<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServicingAppointment;
use App\Models\ServicingSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicingAppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServicingAppointment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'servicing_slot_id' => ServicingSlot::factory()->create()->id,
            'service_id' => Service::factory()->create()->id,
            'appointment_date' => now()->format('Y-m-d'),
            'time_start' => '11:00',
            'interval' => 60
        ];
    }
}
