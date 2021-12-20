<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
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
                'name' => 'CarFren App',
                'key' => 'notifications.app',
                'type' => 'customer.setting'
            ],
            [
                'name' => 'Email',
                'key' => 'notifications.email',
                'type' => 'customer.setting'
            ]
        ];

        foreach ($data as $item) {
            Setting::firstOrCreate($item);
        }
    }
}
