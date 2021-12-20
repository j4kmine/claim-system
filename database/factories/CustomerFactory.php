<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'salutation' => 'Mr',
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'password' => bcrypt('password'),
            'status' => 'active',
            'nric_uen' => Str::uuid()
        ];
    }
}
