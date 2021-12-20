<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
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
                'salutation' => 'Mr',
                'name' => 'John Doe',
                'password' => bcrypt('password'),
                'status' => 'active'
            ]
        ];

        foreach ($data as $item) {
            Customer::firstOrCreate($item);
        }

        // Attach to vehicle
        $vehicle = Vehicle::first();
        $customer = Customer::first();
        $customer->vehicles()->sync($vehicle);
    }
}
