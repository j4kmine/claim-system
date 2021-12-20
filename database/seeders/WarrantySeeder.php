<?php

namespace Database\Seeders;

use App\Models\Warranty;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class WarrantySeeder extends Seeder
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
                'ref_no' => 'ref-' . Str::uuid(),
                'ci_no' => 'ci-' . Str::uuid(),
                'customer_id' => 1,
                'vehicle_id' => 1,
                'dealer_id' => 1,
                'creator_id' => 1,
                'insurer_id' => 1,
                'price' => 10000,
                'max_claim' => 3,
                'mileage' => 4,
                'mileage_coverage' => 2,
                'warranty_duration' => 20,
                'start_date' => now(),
                'remarks' => 'abc',
                'status' => 'active'
            ]
        ];

        foreach ($data as $item) {
            Warranty::firstOrCreate($item);
        }
    }
}
