<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Warranty;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarrantyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Warranty::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ref_no' => $this->faker->sentence(12),
            'vehicle_id' => Vehicle::factory()->create()->id,
            'customer_id' => Customer::factory()->create()->id,
            'dealer_id' => Company::factory()->create()->id,
            'insurer_id' => Company::factory()->create()->id,
            'price' => $this->faker->numberBetween(3000, 5000),
            'max_claim' => $this->faker->numberBetween(10, 100),
            'mileage_coverage' => $this->faker->numberBetween(100, 5000),
            'warranty_duration' => $this->faker->numberBetween(1, 50),
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'remarks' => '',
            'status' => 'pending-payment'
        ];
    }
}
