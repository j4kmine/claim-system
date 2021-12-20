<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $service_type = ServiceType::where('workshop_id', 1)->get();

        return [
            'customer_id' => Customer::factory()->create()->id,
            'vehicle_id' => Vehicle::factory()->create()->id,
            'workshop_id' => 1,
            'service_type_id' => $service_type->random()->id,
            'appointment_date' => now()->addDays($this->faker->numberBetween(1, 30)),
            'status' => 'upcoming',
            'rescheduled_count' => 0,
            'remarks' => $this->faker->word()
        ];
    }
}
