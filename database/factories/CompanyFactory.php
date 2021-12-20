<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Autosprint ' . substr(Str::uuid(), 0, 5), 
            'type' => 'workshop', 
            'status' => 'active',
            'code' => Str::uuid(),
            'acra' => 'UEN' . $this->faker->randomNumber(5),
            'address' => $this->faker->address,
            'contact_no' => $this->faker->randomNumber(5),
            'contact_person' => 'YK',
            'contact_email' => $this->faker->email,
            'description' => 'A workshop company',
            'status' => 'active',
        ];
    }
}
