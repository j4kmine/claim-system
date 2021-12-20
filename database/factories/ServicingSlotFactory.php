<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ServicingSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicingSlotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServicingSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'workshop_id' => Company::factory()->create()->id,
            'day' => now()->format('l'),
            'time_start' => '09:00',
            'time_end' => '17:00',
            'interval' => 60,
            'slots_per_interval' => 10,
            'status' => 'active'
        ];
    }
}
