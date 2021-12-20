<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Claim;
use App\Models\ClaimItem;
use App\Models\ClaimDocument;
use App\Models\Vehicle;
use App\Models\Insurer;
use App\Models\Service;
use Database\Factories\ServiceFactory;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'All Cars Support Staff',
            'email' => 'support_staff@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'all_cars',
            'role' => 'support_staff',
            'status' => 'active',
            'company_id' => null
        ]);

        User::create([
            'name' => 'All Cars Admin',
            'email' => 'admin@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'all_cars',
            'role' => 'admin',
            'status' => 'active',
            'company_id' => null
        ]);

        $workshop = Company::create([
            'name' => 'Autosprint',
            'type' => 'workshop',
            'status' => 'active',
            'code' => 'ABC',
            'acra' => 'UEN12345678',
            'address' => 'Newton Road',
            'contact_no' => '91234567',
            'contact_person' => 'YK',
            'contact_email' => 'admin@autosprint.com',
            'description' => 'A workshop company',
            'status' => 'active',
        ]);

        $wsStaff = User::create([
            'name' => 'Workshop Support Staff',
            'email' => 'workshop_support_staff@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'workshop',
            'role' => 'support_staff',
            'status' => 'active',
            'company_id' => $workshop->id
        ]);

        $wsAdmin = User::create([
            'name' => 'Workshop Admin',
            'email' => 'workshop_admin@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'workshop',
            'role' => 'admin',
            'status' => 'active',
            'company_id' => $workshop->id
        ]);

        $surveyor = Company::create([
            'name' => 'Survey Monkey',
            'type' => 'surveyor',
            'status' => 'active',
            'code' => 'ABC',
            'acra' => 'UEN12345678',
            'address' => 'Newton Road',
            'contact_no' => '91234567',
            'contact_person' => 'Mike',
            'contact_email' => 'admin@surveymonkey.com',
            'description' => 'A survey company',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Surveyor Support Staff',
            'email' => 'surveyor_support_staff@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'surveyor',
            'role' => 'support_staff',
            'status' => 'active',
            'company_id' => $surveyor->id
        ]);

        User::create([
            'name' => 'Surveyor Admin',
            'email' => 'surveyor_admin@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'surveyor',
            'role' => 'admin',
            'status' => 'active',
            'company_id' => $surveyor->id
        ]);

        $insurer = Company::create([
            'name' => 'III',
            'type' => 'insurer',
            'status' => 'active',
            'code' => 'I-III001',
            'acra' => 'UEN12345678',
            'address' => 'Newton Road',
            'contact_no' => '91234567',
            'contact_person' => 'Satish',
            'contact_email' => 'admin@iii.com',
            'description' => 'An insurance company'
        ]);

        User::create([
            'name' => 'Insurer Support Staff',
            'email' => 'insurer_support_staff@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'insurer',
            'role' => 'support_staff',
            'status' => 'active',
            'company_id' => $insurer->id
        ]);

        User::create([
            'name' => 'Insurer Admin',
            'email' => 'insurer_admin@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'insurer',
            'role' => 'admin',
            'status' => 'active',
            'company_id' => $insurer->id
        ]);

        $vehicle = Vehicle::create([
            'chassis_no' => 'ABC1234',
            'registration_no' => 'SGX1234Y',
            'nric_uen' => 'S9212345X',
            'make' => 'BMW',
            'model' => '320i',
            'mileage' => 10000
        ]);
        $claim = Claim::create([
            'creator_id' => $wsStaff->id,
            'vehicle_id' => $vehicle->id,
            'insurer_id' => $insurer->id,
            'workshop_id' => $wsStaff->company_id,
            'ref_no' => Str::random(10),
            'policy_name' => "Ng Yong Ming",
            'policy_certificate_no' => "CERT1234",
            'mileage' => 1000,
            'policy_coverage_from' => "2010-04-10",
            'policy_coverage_to' => "2020-05-10",
            'policy_nric_uen' => "S9212917I",
            'date_of_notification' => "2019-03-10",
            'date_of_loss' => "2020-04-10",
            'cause_of_damage' => "SOMEONE BANG ME",
            'total_claim_amount' => 323.00,
            'status' => "allCars_review"
        ]);

        ClaimItem::create([
            'claim_id' => $claim->id,
            'item_id' => 1,
            'item' => 'Windscreen',
            'amount' => 200,
            'recommended' => 0,
            'type' => 'item'
        ]);
        ClaimItem::create([
            'claim_id' => $claim->id,
            'item_id' => 1,
            'item' => 'Spraying',
            'amount' => 123,
            'recommended' => 0,
            'type' => 'labour'
        ]);
        ClaimDocument::create([
            'claim_id' => $claim->id,
            'name' => 'E1vxbO5hOyPvDDJQrfyhg3KKZALbGfoNkUHnBCc9.jpeg',
            'url' => 'https://laravel-platform.s3.ap-southeast-1.amazonaws.com/claim/quotation/E1vxbO5hOyPvDDJQrfyhg3KKZALbGfoNkUHnBCc9.jpeg',
            'type' => 'quotation'
        ]);
        ClaimDocument::create([
            'claim_id' => $claim->id,
            'name' => '9vZxUefiGut0WGj3fHPeTJgDfHhsaqyGujjJhmmA.jpeg',
            'url' => 'https://laravel-platform.s3.ap-southeast-1.amazonaws.com/claim/damage/9vZxUefiGut0WGj3fHPeTJgDfHhsaqyGujjJhmmA.jpeg',
            'type' => 'damage'
        ]);
        ClaimDocument::create([
            'claim_id' => $claim->id,
            'name' => 'X8UY7o8JbC4qmQY8CXKAAnCBygsqJRyJCpB65EMT.png',
            'url' => 'https://laravel-platform.s3.ap-southeast-1.amazonaws.com/claim/service/X8UY7o8JbC4qmQY8CXKAAnCBygsqJRyJCpB65EMT.png',
            'type' => 'service'
        ]);

        Insurer::create([
            'surveyor_id' => $surveyor->id,
            'insurer_id' => $insurer->id
        ]);

        $insurer = Company::create([
            'name' => 'IIIv2',
            'type' => 'insurer',
            'status' => 'active',
            'code' => 'ABCv2',
            'acra' => 'UEN12345678v2',
            'address' => 'Newton Road',
            'contact_no' => '91234567',
            'contact_person' => 'Satish',
            'contact_email' => 'admin@iiiv2.com',
            'description' => 'An insurance company'
        ]);

        Insurer::create([
            'surveyor_id' => $surveyor->id,
            'insurer_id' => $insurer->id
        ]);

        $surveyor = Company::create([
            'name' => 'Survey Monkeyv2',
            'type' => 'surveyor',
            'status' => 'active',
            'code' => 'ABCv2',
            'acra' => 'UEN12345678v2',
            'address' => 'Newton Road',
            'contact_no' => '91234567',
            'contact_person' => 'Mike',
            'contact_email' => 'admin@surveymonkey.com',
            'description' => 'A survey company',
            'status' => 'active',
        ]);

        $dealer = Company::create([
            'name' => 'Auto Dealer',
            'type' => 'dealer',
            'status' => 'active',
            'code' => 'ABC',
            'acra' => 'UEN12345678',
            'address' => 'Newton Road',
            'contact_no' => '91234561',
            'contact_person' => 'Satish',
            'contact_email' => 'admin@dealer.com',
            'description' => 'An dealer company'
        ]);

        User::create([
            'name' => 'Dealer Salesperson',
            'email' => 'salesperson@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'dealer',
            'role' => 'salesperson',
            'status' => 'active',
            'company_id' => $dealer->id
        ]);

        User::create([
            'name' => 'Dealer Admin',
            'email' => 'dealer_admin@carfren.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'dealer',
            'role' => 'admin',
            'status' => 'active',
            'company_id' => $dealer->id
        ]);

        User::create([
            'name' => 'Jonty Salesperson',
            'email' => 'jkay@smartindustries.asia',
            'password' => bcrypt('carfren!234'),
            'category' => 'dealer',
            'role' => 'salesperson',
            'status' => 'active',
            'company_id' => $dealer->id
        ]);

        User::create([
            'name' => 'Jonty Dealer Admin',
            'email' => 'jonathankay805@gmail.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'dealer',
            'role' => 'admin',
            'status' => 'active',
            'company_id' => $dealer->id
        ]);

        User::create([
            'name' => 'Jonty Support Staff',
            'email' => 'jkay@undercurrentcapital.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'all_cars',
            'role' => 'support_staff',
            'status' => 'active',
            'company_id' => null
        ]);

        User::create([
            'name' => 'Jonty Admin',
            'email' => 'jonathankay@live.com',
            'password' => bcrypt('carfren!234'),
            'category' => 'all_cars',
            'role' => 'admin',
            'status' => 'active',
            'company_id' => null
        ]);


        $this->call(CustomerSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(ServiceTypeSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(ReportSeeder::class);
        $this->call(PriceSeeder::class);
        $this->call(PackageSeeder::class);

        Service::factory(100)->create();
    }
}
