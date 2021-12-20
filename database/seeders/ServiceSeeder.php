<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $date = Carbon::create(2021, 10, 10, 12, 12, 00);
        
        $data = [
            [
                'customer_id' => 1,
                'vehicle_id' => 1,
                'workshop_id' => 1,
                'service_type_id' => 1,
                'appointment_date' => $date,
                'status' => 'upcoming',
                'remarks' => 'Call me later'
            ]
        ];

        foreach ($data as $item) {
            Service::firstOrCreate($item);
        }
    }
}
