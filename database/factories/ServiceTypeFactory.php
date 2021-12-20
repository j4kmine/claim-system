<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'workshop_id' => Company::factory()->create()->id,
            'name' => 'Maintenance ' . $this->faker->word(),
            'status' => 'active',
            'description' => $this->faker->sentence()
        ];
    }
}
