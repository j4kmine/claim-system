<?php

namespace Database\Seeders;

use App\Models\WarrantyPrice;
use Illuminate\Database\Seeder;

class PriceSeeder extends Seeder
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
                'make' => 'AUDI',
                'model' => 'A1 SPORTBACK',
                'category' => 'CONTINENTAL',
                'capacity' => 999,
                'type' => 'preowned',
                'fuel' => 'non_hybrid',
                'price' => 380.00,
                'max_claim' => 10000,
                'mileage_coverage' => 15000,
                'warranty_duration' => 0.50,
                'insurer_id' => 3,
                'package' => 'CarFren Package 1',
                'status' => 'active'
            ]
        ];

        foreach ($data as $item) {
            WarrantyPrice::firstOrCreate($item);
        }
    }
}
