<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Maintenance and Servicing',
                'color' => '#00A19D',
                'workshop_id' => 1
            ],
            [
                'name' => 'Repairs or Parts Replacement',
                'color' => '#95DAC1',
                'workshop_id' => 1
            ],
            [
                'name' => 'Fleet Servicing and Management',
                'color' => '#38A3A5',
                'workshop_id' => 1
            ],
            [
                'name' => 'Warranty Claims',
                'color' => '#F3F0D7',
                'workshop_id' => 1
            ],
            [
                'name' => 'Other Services',
                'color' => '#3DB2FF',
                'workshop_id' => 1
            ]
        ];

        foreach ($data as $item) {
            ServiceType::firstOrCreate($item);
        }
    }
}
