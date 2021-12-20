<?php

namespace Database\Seeders;

use App\Models\ServicingSlot;
use Illuminate\Database\Seeder;

class ServicingSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data =
            [
                [
                    'workshop_id' => 1,
                    'day' => 'Monday',
                    'time_start' => '07:00',
                    'time_end' => '17:00',
                    'interval' => 60,
                    'slots_per_interval' => 10,
                    'status' => 'open'
                ],
                [
                    'workshop_id' => 1,
                    'day' => 'Tuesday',
                    'time_start' => '07:00',
                    'time_end' => '17:00',
                    'interval' => 60,
                    'slots_per_interval' => 10,
                    'status' => 'open'
                ]
            ];

        foreach ($data as $item) {
            ServicingSlot::firstOrCreate($item);
        }
    }
}
