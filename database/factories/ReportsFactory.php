<?php

namespace Database\Factories;

use App\Models\Reports;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reports::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'customer_id' => Customer::factory()->create()->id,
            'date_of_accident' => now()->addDays(-20)->format('Y-m-d'),
            'location_of_accident' => 'Yogyakarta',
            'weather_road_condition' => 'Clear & Dry',
            'reporting_type' => 'Reporting Only',
            'number_of_passengers' => 3,
            'is_video_captured' => 0,
            'purpose_of_use' => 'Private Use',
            'details' => $this->faker->sentence(),
            'vehicle_make' => $this->faker->word,
            'vehicle_model' => $this->faker->word,
            'insurance_company' => $this->faker->word,
            'certificate_number' => $this->faker->word,
            'insured_nric' => $this->faker->word,
            'insured_name' => $this->faker->word,
            'insured_contact_number' => $this->faker->phoneNumber,
            'is_visiting_workshop' => 1,
            'workshop_id' => Company::factory()->create()->id,
            'workshop_visit_date' => now()->addDays(20)->format('Y-m-d'),
            'is_owner_drives' => 1,
            'owner_driver_relationship' => 'Spouse',
            'vehicle_id' => Vehicle::factory()->create()->id
        ];
    }
}
