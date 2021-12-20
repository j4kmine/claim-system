<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\WarrantyPrice;
use Illuminate\Database\Seeder;

class WarrantyPriceSeeder extends Seeder
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
                'make' => 'BMW',
                'model' => '123',
                'category' => 'pre-owned',
                'capacity' => '1',
                'type' => 'hybrid',
                'fuel' => '1',
                'price' => 4102,
                'max_claim' => 2,
                'mileage_coverage' => 3,
                'warranty_duration' => 4,
                'insurer_id' => Company::first()->id,
                'status' => '-'
            ],
            [
                'make' => 'BMW',
                'model' => '123',
                'category' => 'new',
                'capacity' => '1',
                'type' => 'hybrid',
                'fuel' => '1',
                'price' => 4102,
                'max_claim' => 2,
                'mileage_coverage' => 3,
                'warranty_duration' => 4,
                'insurer_id' => Company::first()->id,
                'status' => '-'
            ],
            [
                'make' => 'BMW',
                'model' => '123',
                'category' => 'pre-owned',
                'capacity' => '1',
                'type' => 'non-hybrid',
                'fuel' => '1',
                'price' => 4102,
                'max_claim' => 2,
                'mileage_coverage' => 3,
                'warranty_duration' => 4,
                'insurer_id' => Company::first()->id,
                'status' => '-'
            ]
        ];

        foreach ($data as $item) {
            WarrantyPrice::firstOrCreate($item);
        }
    }
}
