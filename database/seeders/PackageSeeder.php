<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
       The possible categories are:

            New Cars
            - III x CarFren+: 3 Years
            - III x CarFren+: 5 Years
            - III x CarFren+: 10 Years

            Pre-Owned Cars
            - Good Friend: 6 Months
            - Best Friend: 6 Months
            - Best Friend: 1 Year
            - Best Friend: 2 years
            - Best Friend: 3 years
        */
        $data = [
            [
                'name' => 'III x CarFren+',
                'duration' => 3,
                'mileage_coverage' => 100000,
                'type' => 'new'
            ],
            [
                'name' => 'III x CarFren+',
                'duration' => 5,
                'mileage_coverage' => 130000,
                'type' => 'new'
            ],
            [
                'name' => 'III x CarFren+',
                'duration' => 10,
                'mileage_coverage' => 260000,
                'type' => 'new'
            ],
            [
                'name' => 'Good Friend',
                'duration' => 0.5,
                'mileage_coverage' => 15000,
                'type' => 'preowned'
            ],
            [
                'name' => 'Best Friend',
                'duration' => 0.5,
                'mileage_coverage' => 15000,
                'type' => 'preowned'
            ],
            [
                'name' => 'Best Friend',
                'duration' => 1,
                'mileage_coverage' => 25000,
                'type' => 'preowned'
            ],
            [
                'name' => 'Best Friend',
                'duration' => 2,
                'mileage_coverage' => 50000,
                'type' => 'preowned'
            ],
            [
                'name' => 'Best Friend',
                'duration' => 3,
                'mileage_coverage' => 75000,
                'type' => 'preowned'
            ],
        ];

        foreach ($data as $item) {
            Package::firstOrCreate($item);
        }
    }
}
