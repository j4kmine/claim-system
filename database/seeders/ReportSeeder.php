<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Company;
use App\Models\Reports;
use App\Models\Vehicle;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $report = Reports::create([
            'customer_id' => Customer::first()->id,
            'date_of_accident' => now()->addDays(-7),
            'location_of_accident' => 'Yogyakarta',
            'weather_road_condition' => 'Raining & Wet',
            'reporting_type' => 'Claim Own Insurance',
            'number_of_passengers' => 4,
            'is_video_captured' => false,
            'purpose_of_use' => 'Private Use',
            'details' => '-',
            // 'vehicle_car_plate' => 'AB4029DHZ',
            'vehicle_id' => Vehicle::first()->id,
            'vehicle_make' => 'BMW',
            'vehicle_model' => 'i300',
            'insurance_company' => 'Company X',
            'certificate_number' => 'x302',
            'insured_nric' => 'nric302',
            'insured_name' => 'name302',
            'insured_contact_number' => '62877724124',
            'is_visiting_workshop' => true,
            'workshop_id' => Company::first()->id,
            'workshop_visit_date' => now()->addDays(2),
            'is_owner_drives' => false,
            'owner_driver_relationship' => 'Spouse'
        ]);

        $report->driver()->create([
            'name' => 'driver name',
            'nric' => 'driver nric',
            'dob' => now()->addYears(-20),
            'license_pass_date' => now()->addYears(-1),
            'address' => 'Driver Address',
            'contact_number' => '62718428424',
            'email' => 'driver@mail.com',
            'occupations' => 'Working Indoors',
        ]);
    }
}
