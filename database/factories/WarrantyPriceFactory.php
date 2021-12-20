<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\WarrantyPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarrantyPriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WarrantyPrice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'make' => $this->faker->randomElement(['BMW', 'Mercedez Benz', 'Honda', 'Toyota']),
            'model' => $this->faker->randomNumber(3),
            'category' => $this->faker->randomElement(['pre-owned', 'new']),
            'capacity' => $this->faker->randomNumber(2),
            'type' => $this->faker->randomElement(['hybrid', 'non-hybrid']),
            'fuel' => $this->faker->randomNumber(2),
            'price' => $this->faker->randomNumber(4),
            'max_claim' => $this->faker->randomNumber(2),
            'mileage_coverage' => $this->faker->randomNumber(2),
            'warranty_duration' => $this->faker->randomNumber(2),
            'insurer_id' => Company::factory()->create()->id,
            'status' => '-'
        ];
    }
}
