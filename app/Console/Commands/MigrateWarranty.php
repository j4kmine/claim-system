<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Insurer;
use App\Models\Proposer;
use App\Models\Vehicle;
use App\Models\Warranty;
use App\Models\WarrantyDocument;
use Carbon\Carbon;
use Illuminate\Support\Str;
use mikehaertl\pdftk\Pdf;

class MigrateWarranty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:warranty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running Migration Warranty from old Carfren Platform';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $warranty = $this->normalizeCsv(Storage::path('old-platform/warranty.csv'));
        $dealer = $this->normalizeCsv(Storage::path('old-platform/dealer.csv'));
        $proposer = $this->normalizeCsv(Storage::path('old-platform/proposer.csv'));
        $vehicle = $this->normalizeCsv(Storage::path('old-platform/vehicle.csv'));
        $insurer = $this->createInsurer();

        $relation_data = (object) [
            'dealer'    => $dealer,
            'proposer'  => $proposer,
            'vehicle'   => $vehicle,
            'insurer'   => $insurer
        ];

        //filter from masterlist
        $vehicle_no_list = collect([]);
        $missing_vehicle = [];
        foreach ($this->masterlist as $key => $value) {
            $_vehicle = $vehicle->where('registration_no', $value[1])->first();
            if ($_vehicle) {
                // echo $value . "\n";
                // echo $_vehicle['id'] . "\n";
                $_warranty = $warranty->where('vehicle_id', $_vehicle['id'])->where('price', '>', 0)->first();
                $vehicle_no_list[$value[0]] = $_warranty;
                // $vehicle_no_list->push($_warranty);
            } else {
                $missing_vehicle[$key] = $value;
            }
        }

        //normalize masterlist
        $masterlist = collect([]);
        foreach ($this->masterlist as $key => $value) {
            $masterlist->push([
                'ci_no' => $value[0],
                'vehicle_no' => $value[1]
            ]);
        }

        //normalize missing capacity
        $this->normalize_missing_capacity = collect([]);
        foreach ($this->missing_capacity as $key => $value) {
            $this->normalize_missing_capacity->push([
                'vehicle_no' => $value[0],
                'capacity' => $value[1]
            ]);
        }

        //masterlist warranty insert
        $success = 0;
        $error = 0;
        foreach ($vehicle_no_list as $key => $value) {
            echo "Migrating warranty from masterlist $key..";
            try {
                DB::transaction(function () use ($value, $relation_data, $masterlist) {
                    $this->insertWarranty((object)$value, $relation_data, $masterlist);
                });
                DB::commit();
                $success++;
                echo "done $key \n";
            } catch (\Throwable $th) {
                $error++;
                echo 'error, please check Laravel logs warranty_id -> ' . $value['id'] . "\n";
                DB::rollBack();
                Log::debug($th);
            }
        }
        echo "Done migrating Warranties from Old Carfren Platform | success $success | error $error \n";

        return 1;
    }

    private function insertWarranty($data, $relation_data, $masterlist = null)
    {
        //get vehicle
        $vehicle = (object) $relation_data->vehicle->where('id', $data->vehicle_id)->first();

        $proposer = $this->createProposer((object)$relation_data->proposer->where('id', $data->proposer_id)->first());
        $new_vehicle = $this->createVehicle($vehicle, $proposer, $data);
        $dealer = $this->createDealer((object)$relation_data->dealer->where('id', $data->dealer_id)->first());
        $insurer = $relation_data->insurer;

        echo ' checking relation data ';

        $warranty_data = (object) [
            'ref_no'        => Str::random(10),
            'proposer_id'   => $proposer->id,
            'vehicle_id'    => $new_vehicle->id,
            'insurer_id'    => $insurer->insurer_id,
            'dealer_id'     => $dealer->id,
            'price'         => $data->price,
            // 'package'       => $data->,
            'remarks'       => $data->remarks,
            'max_claim'     => $data->max_claim,
            'mileage_coverage'  => $data->mileage_coverage,
            'warranty_duration' => $data->duration,
            'start_date'        => $data->start_at,
            'status'            => 'completed',
            'expiry_date'       => $data->end_at,
            'submitted_at'      => now(),
        ];

        $warranty = Warranty::where('proposer_id', $proposer->id)
            ->where('vehicle_id', $new_vehicle->id)
            ->where('insurer_id', $insurer->id)
            ->where('dealer_id', $dealer->id)
            ->first();

        if (!$warranty) {
            $this->createWarranty($warranty_data, $data, $vehicle, $masterlist);
        }
    }

    private function createWarranty($warranty_data, $data, $vehicle, $masterlist = null)
    {
        echo ' create warranty ';
        if (!$masterlist) {
            $warranty = Warranty::create((array)$warranty_data);
            //create ci
            if ($data->category == 'new_warranty') {
                $last_warranty = Warranty::where('status', 'completed')->where('ci_no', 'like', 'C%')->orderBy('ci_no', 'desc')->first();
            } else {
                $last_warranty = Warranty::where('status', 'completed')->where('ci_no', 'like', 'UC%')->orderBy('ci_no', 'desc')->first();
            }

            if ($last_warranty == null) {
                if ($data->category == 'new_warranty') {
                    $warranty->ci_no = "C04308";
                } else {
                    $warranty->ci_no = "UC00359";
                }
            } else {
                if ($data->category == 'new_warranty') {
                    $warranty->ci_no = "C" . sprintf("%05d", (ltrim(explode("C", $last_warranty->ci_no)[1]) + 1));
                } else {
                    $warranty->ci_no = "UC" . sprintf("%05d", (ltrim(explode("UC", $last_warranty->ci_no)[1]) + 1));
                }
            }
            $warranty->save();
        } else {
            $ci_from_masterlist = $masterlist->where('vehicle_no', $vehicle->registration_no)->first()['ci_no'];

            //check if ci already exists
            $_warranty = Warranty::where('ci_no', $ci_from_masterlist)->first();
            if (!$_warranty) {
                $warranty = Warranty::create((array)$warranty_data);
                $warranty->ci_no = $ci_from_masterlist;
                $warranty->save();
            } else {
                echo ' ci already exists ';
                return;
            }
        }

        $warranty = Warranty::with(['vehicle', 'proposer'])->where('id', $warranty->id)->first();

        //insert log card
        echo ' log card ';
        $warranty_log = WarrantyDocument::where('warranty_id', $warranty->id)->where('type', 'log')->first();

        $explode_url = explode('vehicle', $vehicle->log_card_url);

        $file_name = $explode_url[count($explode_url) - 1];

        //remove params
        $explode_file_name = explode('?', $file_name);

        $file_name = $explode_file_name[0];

        // explode('/', $vehicle->log_card_url)[5] //old way

        if (!$warranty_log) {
            $log_card = Storage::disk('s3_old')->temporaryUrl(
                'vehicle' . $file_name,
                Carbon::now()->addMinutes(30)
            );

            $path = 'warranty/log' . $file_name;

            Storage::disk('s3')->put($path, file_get_contents($log_card));
            sleep(1);

            WarrantyDocument::create([
                'warranty_id' => $warranty->id,
                'name' => explode('/', $file_name)[1],
                'url' => Storage::disk('s3')->url($path),
                'type' => 'log'
            ]);
        }

        // Create Warranty Booklets
        $this->generatingWarrantyBooklet($warranty);
        $this->generatingFormSalesrespon($warranty);
        $this->generatingCIForm($warranty);
    }

    private function createProposer($data)
    {
        $proposer = Proposer::where('nric_uen', $data->nric)->first();

        if (!$proposer) {
            $proposer = Proposer::create([
                'salutation'    => $data->salutation,
                'name'          => $data->name,
                'email'         => $data->email,
                'phone'         => $data->mobile,
                'nric_uen'      => $data->nric,
                'address'       => $data->address,
                'date_of_birth' => $data->date_of_birth,
            ]);

            $customer = Customer::where('email', $data->email)->first();
            if (!$customer) {
                $customer = Customer::where('phone', $data->mobile)->first();
                if (!$customer) {
                    Customer::create([
                        'salutation'    => $data->salutation,
                        'name'          => $data->name,
                        'email'         => $data->email,
                        'phone'         => $data->mobile,
                        'nric_uen'      => $data->nric,
                        'address'       => $data->address,
                        'date_of_birth' => $data->date_of_birth,
                        'status'        => 'active'
                    ]);
                }
            }
        }

        return $proposer;
    }

    private function createVehicle($data, $proposer, $warranty)
    {
        echo ' create vehicle ';
        $vehicle = Vehicle::where('registration_no', $data->registration_no)
            ->where('chassis_no', $data->chassis_no)
            ->where('engine_no', $data->engine_no)
            ->first();

        $capacity = $data->capacity;
        if ($capacity <= 10) {
            $search_missing_capacity = $this->normalize_missing_capacity->where('vehicle_no', $data->registration_no)->first();
            $capacity = $search_missing_capacity['capacity'];
        }

        $registration_date = $data->registration_date;
        if ($registration_date) {
            //format date
            $date = explode('/', $registration_date);
            $registration_date = "$date[2]-$date[0]-$date[1]";
        } else {
            throw $vehicle->registration_no . ' date undefined \n';
        }

        if (!$vehicle) {
            $vehicle = Vehicle::create([
                'registration_no'   => $data->registration_no,
                'chassis_no'        => $data->chassis_no,
                'engine_no'         => $data->engine_no,
                'make'              => $data->make,
                'model'             => $data->model,
                'mileage'           => $data->mileage,
                'manufacture_year'  => $data->manufacture_year,
                'registration_date' => $registration_date,
                'nric_uen'          => $proposer->nric,
                'type'              => $data->category == 'new_warranty' ? 'new' : 'preowned',
                'capacity'          => $capacity,
                'fuel'              => $data->hybrid == "true" ? 'hybrid' : 'non_hybrid',
            ]);

            //BUG | check if after created, the registration_date is not 0000-00-00
            $veh = Vehicle::find($vehicle->id);
            $reg_date = $veh->registration_date;
            while ($reg_date == "0000-00-00") {
                echo " loop date $reg_date \n";
                $veh->delete();

                $vehicle = Vehicle::create([
                    'registration_no'   => $data->registration_no,
                    'chassis_no'        => $data->chassis_no,
                    'engine_no'         => $data->engine_no,
                    'make'              => $data->make,
                    'model'             => $data->model,
                    'mileage'           => $data->mileage,
                    'manufacture_year'  => $data->manufacture_year,
                    'registration_date' => $registration_date,
                    'nric_uen'          => $proposer->nric,
                    'type'              => $data->category == 'new_warranty' ? 'new' : 'preowned',
                    'capacity'          => $capacity,
                    'fuel'              => $data->hybrid == "true" ? 'hybrid' : 'non_hybrid',
                ]);

                $veh = Vehicle::find($vehicle->id);
                $reg_date = $veh->registration_date;
            }
        } else {
            $reg_date = $vehicle->registration_date;
            while ($reg_date == "0000-00-00") {
                echo " date from csv $registration_date \n";
                echo " loop date $reg_date \n";
                $vehicle->delete();

                $vehicle = Vehicle::create([
                    'registration_no'   => $data->registration_no,
                    'chassis_no'        => $data->chassis_no,
                    'engine_no'         => $data->engine_no,
                    'make'              => $data->make,
                    'model'             => $data->model,
                    'mileage'           => $data->mileage,
                    'manufacture_year'  => $data->manufacture_year,
                    'registration_date' => $registration_date,
                    'nric_uen'          => $proposer->nric,
                    'type'              => $data->category == 'new_warranty' ? 'new' : 'preowned',
                    'capacity'          => $capacity,
                    'fuel'              => $data->hybrid == "true" ? 'hybrid' : 'non_hybrid',
                ]);

                $vehicle = Vehicle::find($vehicle->id);
                $reg_date = $vehicle->registration_date;
            }
        }

        return $vehicle;
    }

    private function createDealer($data)
    {
        $dealer = Company::where('name', $data->name)->first();
        if (!$dealer) {
            $dealer = Company::create([
                'name'              => $data->name,
                'code'              => $data->code,
                'status'            => 'active',
                'type'              => 'dealer',
                'acra'              => $data->acra,
                'address'           => $data->address,
                'contact_person'    => $data->contact_person,
                'contact_number'    => $data->contact_number,
            ]);
        }

        return $dealer;
    }


    private  function createInsurer()
    {
        $company = Company::where('name', 'India International Insurance Pte Ltd (Warranty Insurance)')->first();

        if (!$company) {
            $company = Company::create([
                'name'  => 'India International Insurance Pte Ltd (Warranty Insurance)',
                'type'  => 'insurer',
                'status' => 'active',
                'contact_email' => 'lingmin@iii.com.sg',
            ]);

            Insurer::create([
                'insurer_id'    => $company->id,
                'surveyor_id'   => $company->id,
            ]);
        }

        $insurer = Insurer::where('insurer_id', $company->id)->first();

        return $insurer;
    }

    private function generatingWarrantyBooklet($warranty)
    {
        echo ' booklet ';
        $pdf = new Pdf(storage_path('app/documents/') . 'warranty-booklet.pdf');
        $filename = Str::uuid() . ".pdf";
        $file_path = storage_path('app/generated/') . $filename;

        $data = [];
        $data['Type of Warranty'] = $warranty->vehicle->type == "new" ? "New" : "Pre-Owned";
        $data['Full Name'] = $warranty->proposer->name;
        $data['Contact No'] = $warranty->proposer->phone;
        $data['Registration Number'] = $warranty->vehicle->registration_no;
        $data['Make_Model'] = $warranty->vehicle->make . " " . $warranty->vehicle->model;
        // $data['Eng Cap'] = $warranty->vehicle->capacity;
        $data['Chassis Number'] = $warranty->vehicle->chassis_no;
        $data['Engine Number'] = $warranty->vehicle->engine_no;
        $data['Certificate Number'] = $warranty->ci_no;
        $data['DDMMYYYY to DDMMYYYY'] = $warranty->format_start_date . " to " . $warranty->end_date;
        // $data['Start of Cover'] = $warranty->format_start_date;
        // $data['End of Cover'] = $warranty->end_date;
        $data['Mileage Covered'] = $warranty->mileage_coverage;
        $data['Commencement Mileage'] = $warranty->mileage;
        $data['Maximum Claim Limit Per Year'] = $warranty->max_claim;
        // $data['Policy Limit'] = $warranty->warranty_duration * $warranty->max_claim;
        $data['Dealer'] = $warranty->dealer->name;
        $pdf->fillForm($data)->needAppearances()->saveAs($file_path);
        Storage::disk('s3')->put('warranty/warranty/' . $filename, fopen($file_path, 'r+'));
        sleep(1);
        Storage::disk('local')->delete("generated/$filename");
        $path = Storage::disk('s3')->url('warranty/warranty/' . $filename);
        WarrantyDocument::create([
            'warranty_id' => $warranty->id,
            'name' => $filename,
            'url' => $path,
            'type' => 'warranty'
        ]);
    }


    private function generatingFormSalesrespon($warranty)
    {
        echo ' salesrespon ';
        $pdf = new Pdf(storage_path('app/documents/') . 'warranty-' . $warranty->vehicle->type . '-application.pdf');
        $filename = Str::uuid() . ".pdf";
        $file_path = storage_path('app/generated/') . $filename;
        // de([$warranty->vehicle->mileage, $warranty->vehicle->registration_no]);
        $data = [
            'agent_code' => 'AllCars Pte Ltd',
            'dealer_name' => $warranty->dealer->name,
            'salutation' => $warranty->proposer->salutation,
            'proposer_name' => $warranty->proposer->name,
            'nric' => $warranty->proposer->nric_uen,
            'address' => $warranty->proposer->address,
            'email' => $warranty->proposer->email,
            'mobile' => $warranty->proposer->phone,
            'registration_no' => $warranty->vehicle->registration_no,
            'mileage' => $warranty->vehicle->mileage,
            'model' => $warranty->vehicle->model,
            'make' => $warranty->vehicle->make,
            'year_manufacture' => $warranty->vehicle->manufacture_year,
            'engine_no' => $warranty->vehicle->engine_no,
            'chasis_no' => $warranty->vehicle->chassis_no,
            'vehicle_reg_date' => $warranty->vehicle->format_registration_date,
            'claim' => "SGD" . $warranty->max_claim,
            'start_dd' => Carbon::parse($warranty->start_date)->format("d"),
            'start_mm' => Carbon::parse($warranty->start_date)->format("m"),
            'start_yyyy' => Carbon::parse($warranty->start_date)->format("Y"),
            'end_dd' => Carbon::parse($warranty->start_date)->addMonths($warranty->warranty_duration * 12)->subDays(1)->format("d"),
            'end_mm' => Carbon::parse($warranty->start_date)->addMonths($warranty->warranty_duration * 12)->subDays(1)->format("m"),
            'end_yyyy' => Carbon::parse($warranty->start_date)->addMonths($warranty->warranty_duration * 12)->subDays(1)->format("Y"),
            'submission_date' => Carbon::now()->format("d/m/Y"),
            'premium' => $warranty->price,
            '3_years_or_100k' => $warranty->warranty_duration == 5 ? 'Yes' : 'Off',
            '10_years_or_260k' => $warranty->warranty_duration == 10 ? 'Yes' : 'Off',
        ];
        if ($warranty->vehicle->type == 'new') {
            $data['mileage'] = $warranty->mileage == null ? 0 : $warranty->mileage;
            $data['claim'] = "SGD" . $warranty->max_claim;
            $data['submission_date'] = Carbon::now()->format("d/m/Y");
            $data['3_years_or_100k'] = $warranty->warranty_duration == 5 ? 'Yes' : 'Off';
            $data['10_years_or_260k'] = $warranty->warranty_duration == 10 ? 'Yes' : 'Off';
        } else {
            $data['claim_limit'] = "SGD" . $warranty->max_claim;
            $data['date'] = Carbon::now()->format("d/m/Y");
            $data['assessment'] = 'On';
            $data['6_months_or_12k'] = $warranty->warranty_duration == 0.5 ? 'On' : 'Off';
            $data['3years_or_75k'] = $warranty->warranty_duration == 3 ? 'On' : 'Off';
            $data['1_year_or_25k'] = $warranty->warranty_duration == 1 ? 'On' : 'Off';
        }


        $pdf->fillForm($data)->needAppearances()->saveAs($file_path);
        Storage::disk('s3')->put('warranty/form/' . $filename, fopen($file_path, 'r+'));
        sleep(1);
        Storage::disk('local')->delete("generated/$filename");
        $path = Storage::disk('s3')->url('warranty/form/' . $filename);
        $warranty_form = WarrantyDocument::create([
            'warranty_id' => $warranty->id,
            'name' => $filename,
            'url' => $path,
            'type' => 'form'
        ]);
    }

    private function generatingCIForm($warranty)
    {
        echo ' ci ';
        // Create CI Form for insurer
        $pdf = new Pdf(storage_path('app/documents/') . 'new-ci-' . $warranty->vehicle->type . '-template.pdf');
        $filename = Str::uuid() . ".pdf";
        $file_path = storage_path('app/generated/') . $filename;

        $capacity = $warranty->vehicle->capacity;

        $data = [
            'Dealer_Name' => $warranty->dealer->name,
            'Dealer_Address' => $warranty->dealer->address,
            'Owner_Name' => $warranty->proposer->name,
            'Cert_No' => $warranty->ci_no,
            'Mileage' => number_format($warranty->mileage_coverage, 0, '.', ',') . "km",
            'Max_Claim' => "S$" . number_format($warranty->max_claim, 0, '.', ',') . "/-",
            'Vehicle_No' => $warranty->vehicle->registration_no,
            'YOM' => $warranty->vehicle->manufacture_year,
            'Make' => $warranty->vehicle->make,
            'Engine_Cap' => $capacity,
            'Model' => $warranty->vehicle->model,
            'Chassis_No' => $warranty->vehicle->chassis_no,
            'DDMMYYYY to DDMMYYYY' => $warranty->format_start_date . " to " . $warranty->end_date,
            'DDMMYYYY' => Carbon::now()->format("dmY"),
            'Engine_No' => $warranty->vehicle->engine_no
        ];

        if ($warranty->vehicle->type == 'preowned') {
            $data['Start_Mileage'] = $warranty->mileage;
        }

        $result = $pdf->fillForm($data)->needAppearances()->saveAs($file_path);
        Storage::disk('s3')->put('warranty/ci/' . $filename, fopen($file_path, 'r+'));
        sleep(1);
        Storage::disk('local')->delete("generated/$filename");
        $path = Storage::disk('s3')->url('warranty/ci/' . $filename);
        $warranty_ci = WarrantyDocument::create([
            'warranty_id' => $warranty->id,
            'name' => $filename,
            'url' => $path,
            'type' => 'ci'
        ]);

        // create coc documents
        $condition1 = strpos(strtolower($warranty->package), 'good friend');
        $condition2 = strpos(strtolower($warranty->package), 'carfren+');
        $condition3 = strpos(strtolower($warranty->package), 'iii x carfren+');
        if (
            ($condition1 !== false && $warranty->warranty_duration == '0.50' && $warranty->vehicle->type == 'preowned')
            || ($condition2 !== false || $condition3 !== false)
        ) {
            $pdf = new Pdf(storage_path('app/documents/') . 'coc-document.pdf');
            $filename = Str::uuid() . ".pdf";
            $file_path = storage_path('app/generated/') . $filename;

            $data = [
                'Dealer_Name' => $warranty->dealer->name,
                'Dealer_Address' => $warranty->dealer->address,
                'Owner_Name' => $warranty->proposer->name,
                'Cert_No' => $warranty->ci_no,
                'Mileage' => $warranty->mileage_coverage,
                'Vehicle_No' => $warranty->vehicle->registration_no,
                'YOM' => $warranty->vehicle->manufacture_year,
                'Make' => $warranty->vehicle->make,
                'Engine_Cap' => $capacity,
                'Model' => $warranty->vehicle->model,
                'Chassis_No' => $warranty->vehicle->chassis_no,
                'DDMMYYYY to DDMMYYYY' => $warranty->format_start_date . " to " . $warranty->end_date,
                'DDMMYYYY' => Carbon::now()->format("dmY"),
                'Engine_No' => $warranty->vehicle->engine_no,
                'Max_Claim' => $warranty->max_claim,
                'Coverage_Type' => $warranty->package
            ];

            if ($warranty->vehicle->type == 'preowned') {
                $data['Start_Mileage'] = $warranty->mileage;
            }

            $result = $pdf->fillForm($data)->needAppearances()->saveAs($file_path);
            Storage::disk('s3')->put('warranty/coc/' . $filename, fopen($file_path, 'r+'));
            sleep(1);
            $path = Storage::disk('s3')->url('warranty/coc/' . $filename);
            $warranty_coc = WarrantyDocument::create([
                'warranty_id' => $warranty->id,
                'name' => $filename,
                'url' => $path,
                'type' => 'coc'
            ]);
        }
    }

    private function normalizeCsv($csv)
    {
        $csv_data = fopen($csv, "r");

        $data_array = [];
        $i = 0;

        while (($data = fgetcsv($csv_data, 1000, ",")) !== FALSE) {
            $data_array[$i] = $data;
            $i++;
        }

        $field = array_shift($data_array);

        $new_array = [];
        foreach ($data_array as $key => $value) {
            foreach ($field as $a => $b) {
                $new_array[$key][$b] = $value[$a];
            }
        }

        return collect($new_array);
    }

    private $normalize_missing_capacity = [];

    private $missing_capacity = [
        ["SMT2239G", 1497],
        ["SMT6006K", 1497],
        ["SFF5784K", 2487],
        ["SMT2535B", 2487],
        ["SMT2587B", 2487],
        ["SJW757K", 2995],
        ["SMS9009M", 2995],
        ["SDV8877X", 1998],
        ["SMT2523K", 1991],
        ["SMT2537X", 2487],
        ["SMT2757C", 2487],
        ["SMT2627U", 2487],
        ["SMT2979D", 2487],
        ["SMT2795S", 2487],
        ["SMT2987E", 2999],
        ["SBF98M", 2894],
        ["SLX338H", 1497],
        ["SC19G", 2996],
        ["SMT5089C", 2487],
        ["SMT5189Y", 2487],
        ["SKH6666S", 1950],
        ["SKE8253C", 2487],
        ["SLB4320Y", 1332],
        ["SLB8248Y", 2487],
        ["SMT5866B", 2487],
        ["SMT2002Y", 1991],
        ["SMG4Z", 1991],
        ["SFV5179Z", 1991],
        ["SDM500H", 1991],
        ["SKV4333S", 1496],
        ["SFP1991C", 2996],
        ["SMT6580T", 1332],
        ["SMT7129H", 5038],
        ["SMT7576A", 1496],
        ["SMT7424E", 2487],
        ["SMT7481P", 1496],
        ["SJE24L", 1496],
        ["SMT7806L", 2487],
        ["SMT7660P", 1332],
        ["SKA16U", 2999],
        ["SDV15U", 1991],
        ["SMU3138C", 1332],
        ["SMU1663R", 1332],
        ["SLU5522A", 1496],
        ["SMU4481D", 1496],
        ["SBQ3311U", 1496],
        ["SMU7532X", 2999],
        ["SGE8899L", 1991],
        ["SFS3838J", 1991],
        ["SMV8282E", 2995],
        ["EM3636T", 2894],
        ["SMU4553E", 2493],
        ["SKA27M", 3996],
        ["SMX230Y", 1332],
        ["SMT6071X", 1498],
        ["SJZ1551P", 2993],
        ["SMT5142G", 1997],
        ["SMU8297T", 3800],
        ["SLM2738G", 2494],
        ["EZ338E", 1595],
        ["SBL2888E", 3498],
        ["SMT227D", 1997],
        ["SMC4569E", 2493],
        ["SGD8638D", 2987],
        ["SMU8741C", 1984],
        ["SMQ5018Z", 4395],
        ["SLJ5327E", 1999],
        ["SKT148Y", 2996],
        ["SJY9533U", 2354],
        ["SFW555Z", 3598],
        ["SMU9409G", 1999],
        ["SMV3455G", 1991],
        ["SKN1140T", 1798],
        ["GBK5559R", 3198],
        ["SMV5831R", 1496],
        ["SKJ9231H", 3498],
        ["SMV4709T", 1991],
        ["SKT4066Z", 1595],
        ["SLE2664C", 1498],
        ["SML9475L", 2362],
        ["SKP4232G", 2672],
        ["SMV8789K", 6749],
        ["SMW6813G", 1997],
        ["SLQ4833E", 1998],
        ["SBB3888B", 1998],
        ["SMX2483X", 1998],
        ["SMY2950J", 1998],
        ["SGM5555G", 4999],
        ["SBF1104Y", 1998],
        ["SLL9988K", 1998],
        ["SLS9928Y", 1332],
        ["ED28R", 3996],
        ["SGC51D", 1999],
        ["SMM9988T", 2995],
        ["SMY3560U", 1991],
        ["SFC1K", 3996],
        ["SMY5028C", 1332],
        ["SMY4970M", 1332],
        ["SMY5062C", 1332],
        ["SMY5041M", 2999],
        ["SMZ8101Z", 2999],
        ["SLJ8829D", 1332],
        ["SMY9418J", 1332],
        ["SMW2571E", 1997],
        ["SLU7948S", 2493],
        ["SMW2490E", 1499],
        ["GBK6448X", 3198],
        ["SMS6666K", 6749],
        ["SKT3209K", 1798],
        ["SLB2635C", 1498],
        ["SKH5271D", 1997],
        ["SMW6510E", 1998],
        ["SMP5477L", 1998],
        ["SMX4496X", 1991],
        ["SKM879H", 1798],
        ["SLW6681G", 1395],
        ["SMX5958Z", 1595],
        ["SMX9024S", 1969],
        ["SMX7470A", 1998],
        ["SGG7800S", 1496],
        ["SMY2205C", 2199],
        ["SKP3609K", 1798],
        ["SLE6020J", 1998],
        ["SMT2881B", 1332],
        ["E6983Y", 2487],
        ["SMT2259Z", 1332],
        ["SKF128D", 2493],
        ["SMT2353J", 2487],
        ["SMT2432P", 3982],
        ["SFN7070G", 1332],
        ["SMT2415P", 996],
        ["SKG6919T", 1332],
        ["SMT2568G", 996],
        ["SMT2585G", 1496],
        ["E6366D", 2487],
        ["SMT2684D", 996],
        ["SDU7879C", 1595],
        ["SMT2912X", 1595],
        ["SCN3883P", 2261],
        ["SMT3977C", 2999],
        ["SMT4214P", 2981],
        ["ED10R", 2925],
        ["SGR89S", 1332],
        ["SKZ6716L", 1991],
        ["SFZ6616B", 1998],
        ["SGE182Z", 1991],
        ["SMT5262T", 1797],
        ["SMA7500T", 1496],
        ["SMT5249H", 2487],
        ["SMT5238P", 1797],
        ["SKM222C", 2995],
        ["SMA2299H", 1496],
        ["SFG6336J", 1991],
        ["SMT5389L", 1991],
        ["SMT5294B", 1797],
        ["SGV279P", 1332],
        ["SKJ4618J", 1991],
        ["SLK6665S", 1496],
        ["SLS8787T", 2925],
        ["SGK1248E", 2487],
        ["SGB80A", 1797],
        ["SKB578J", 1496],
        ["SKW1030E", 1797],
        ["SMT5955C", 1496],
        ["SMT3321U", 1332],
        ["SKW8844C", 1496],
        ["SFZ53P", 2493],
        ["SGE6969G", 1991],
        ["SMS1500M", 1991],
        ["SMT6290E", 1496],
        ["SLL6288S", 2925],
        ["SGG6288C", 2925],
        ["SMT6343L", 1595],
        ["SMT6286T", 1496],
        ["SMT6625Z", 1496],
        ["SMT6729H", 1595],
        ["SMU2310C", 1496],
        ["SMT6873Z", 1496],
        ["SMT7041B", 1496],
        ["SMT6930S", 1496],
        ["SGN504G", 2487],
        ["SMT7098M", 1496],
        ["SMT7224R", 1595],
        ["SLV7277P", 2493],
        ["SMT7536R", 1496],
        ["SMR2988M", 2493],
        ["SMT7384L", 1595],
        ["SMT7540C", 1496],
        ["SDW3336T", 1595],
        ["SDU448J", 1595],
        ["SMT7690C", 2493],
        ["EE55G", 1496],
        ["SLF9799X", 1497],
        ["SFJ4R", 2894],
        ["SMT7978Z", 1595],
        ["SMT8017R", 1595],
        ["SMT8194L", 1797],
        ["SJY6543K", 2925],
        ["SMT8404G", 1496],
        ["SDF7070Y", 1797],
        ["SMT5888M", 1991],
        ["SMT8516S", 1497],
        ["SMU1603P", 1496],
        ["SCG8008D", 1797],
        ["SDJ823S", 2487],
        ["SJY7813A", 1496],
        ["SMU314E", 1496],
        ["SMU114R", 1332],
        ["SMU1192H", 1496],
        ["SMU1243U", 1797],
        ["SGR42B", 1991],
        ["SMU2377K", 1496],
        ["SMU2692A", 1496],
        ["SJY6838K", 1991],
        ["SMU3310X", 2493],
        ["SME24B", 2999],
        ["SMU4032S", 996],
        ["SMU3881P", 1595],
        ["SBF5G", 1991],
        ["SMU4630R", 996],
        ["SMU4518H", 2999],
        ["SMU4524P", 1991],
        ["SMU4695D", 2999],
        ["SMU5107D", 996],
        ["SMU5385S", 1496],
        ["SMU5665J", 1496],
        ["SMU5562Y", 1991],
        ["SMU5651Z", 2493],
        ["SDD2522D", 1991],
        ["SMU5866X", 2693],
        ["SMU5971A", 1496],
        ["SKD908E", 1991],
        ["SLB118S", 2451],
        ["SMU6423J", 1496],
        ["SGZ1314T", 1595],
        ["SMU7054J", 1595],
        ["SFG7557G", 2487],
        ["SLP4358R", 2487],
        ["SLE88D", 1991],
        ["SMU8160D", 1595],
        ["SKJ7487A", 1991],
        ["SDP6015L", 2487],
        ["SCN96P", 2487],
        ["SMU8299M", 2493],
        ["SMU8403D", 2487],
        ["SJY81R", 2487],
        ["SBP3888C", 2487],
        ["SJU2862L", 2487],
        ["SMU8977P", 2487],
        ["SMU9151Z", 2493],
        ["SLT333S", 3982],
        ["SKG6468C", 1080],
        ["SMU3132T", 1496],
        ["SMT1516M", 1999],
        ["SGR6503Z", 1995],
        ["SMT5649L", 1496],
        ["SMS8469A", 1999],
        ["EQ222C", 1991],
        ["SLT333S", 3982],
        ["SKS8702A", 999],
    ];

    private $masterlist = [
        ["C03147", "SMT2881B"],
        ["C03184", "E6983Y"],
        ["C03185", "SMT2259Z"],
        ["C03186", "SMT2239G"],
        ["C03187", "SMT6006K"],
        ["C03188", "SFF5784K"],
        ["C03189", "SKF128D"],
        ["C03190", "SMT2353J"],
        ["C03191", "SMT2432P"],
        ["C03192", "SFN7070G"],
        ["C03193", "SMT2415P"],
        ["C03194", "SKG6919T"],
        ["C03195", "SMT2535B"],
        ["C03196", "SMT2568G"],
        ["C03197", "SMT2587B"],
        ["C03198", "SMT2585G"],
        ["C03199", "SMT2526C"],
        ["C03200", "E6366D"],
        ["C03201", "SJW757K"],
        ["C03202", "SMS9009M"],
        ["C03203", "SDV8877X"],
        ["C03204", "SMT2523K"],
        ["C03205", "SMT2537X"],
        ["C03206", "SMT2757C"],
        ["C03207", "SJE50K"],
        ["C03208", "SMT2684D"],
        ["C03209", "SMT2627U"],
        ["C03210", "SMT2728L"],
        ["C03211", "SMT2979D"],
        ["C03212", "SDU7879C"],
        ["C03213", "SMT2795S"],
        ["C03214", "SMT2829D"],
        ["C03215", "SMT2987E"],
        ["C03216", "SBF98M"],
        ["C03217", "SMT2912X"],
        ["C03218", "SCN3883P"],
        ["C03224", "SLX338H"],
        ["C03220", "SMT3977C"],
        ["C03226", "SMT4214P"],
        ["C03223", "SC19G"],
        ["C03221", "ED10R"],
        ["C03222", "SGR89S"],
        ["C03229", "SKZ6716L"],
        ["C03225", "SFZ6616B"],
        ["C03227", "SGE182Z"],
        ["C03389", "SMT4923B"],
        ["C03228", "SMT5089C"],
        ["C03230", "SMT5262T"],
        ["C03233", "SMA7500T"],
        ["C03237", "SMT5189Y"],
        ["C03238", "SMT5249H"],
        ["C03239", "SMT5238P"],
        ["C03241", "SKM222C"],
        ["C03243", "SKH6666S"],
        ["C03231", "SMA2299H"],
        ["C03232", "SKE8253C"],
        ["C03234", "SFG6336J"],
        ["C03235", "SMT5389L"],
        ["C03236", "SMT5294B"],
        ["C03240", "SGV279P"],
        ["C03242", "SLB4320Y"],
        ["C03245", "SKJ4618J"],
        ["C03244", "SLB8248Y"],
        ["C03247", "SLK6665S"],
        ["C03246", "SLS8787T"],
        ["C03248", "SGK1248E"],
        ["C03249", "SDJ55S"],
        ["C03250", "SGB80A"],
        ["C03251", "SMH899C"],
        ["C03253", "SKB578J"],
        ["C03254", "SKW1030E"],
        ["C03255", "SMT5955C"],
        ["C03256", "SMT3321U"],
        ["C03257", "SMT5866B"],
        ["C03258", "SKW8844C"],
        ["C03272", "SKW98S"],
        ["C03252", "SFZ53P"],
        ["C03259", "SGE6969G"],
        ["C03260", "SMS1500M"],
        ["C03273", "SMT2002Y"],
        ["C03261", "SMT6290E"],
        ["C03262", "SMT6316R"],
        ["C03263", "SLL6288S"],
        ["C03264", "SGG6288C"],
        ["C03265", "SMT6343L"],
        ["C03266", "SMU8885X"],
        ["C03267", "SMT6286T"],
        ["C03268", "SJY5709B"],
        ["C03269", "SMU5331Y"],
        ["C03270", "SMG4Z"],
        ["C03271", "SFV5179Z"],
        ["C03274", "SMZ1116C"],
        ["C03276", "SLD6200L"],
        ["C03278", "SMU2829Z"],
        ["C03279", "SMU6818D"],
        ["C03280", "SMT6625Z"],
        ["C03281", "SMT6547R"],
        ["C03282", "SMT6729H"],
        ["C03285", "SDM500H"],
        ["C03286", "SKV4333S"],
        ["C03287", "SFP1991C"],
        ["C03289", "SMT6580T"],
        ["C03277", "SMT6771J"],
        ["C03283", "SMU2310C"],
        ["C03284", "SMT6300K"],
        ["C03288", "SMT6873Z"],
        ["C03290", "SLM3663G"],
        ["C03291", "SGJ519K"],
        ["C03292", "SMT7129H"],
        ["C03293", "SMT7041B"],
        ["C03294", "SMT6930S"],
        ["C03295", "SMT7179M"],
        ["C03296", "SMT7115Y"],
        ["C03297", "SMT7022G"],
        ["C03298", "SGN504G"],
        ["C03299", "SMT7086Y"],
        ["C03300", "SJY1616S"],
        ["C03301", "SMT7098M"],
        ["C03337", "SMT7224R"],
        ["C03391", "SMT6927C"],
        ["C03302", "SLV7277P"],
        ["C03303", "SMT7536R"],
        ["C03304", "SBB1688A"],
        ["C03305", "SMT7326E"],
        ["C03306", "SMR2988M"],
        ["C03307", "SMT7384L"],
        ["C03308", "SMT7576A"],
        ["C03309", "SMT7424E"],
        ["C03310", "SMT7506C"],
        ["C03311", "SMT7518T"],
        ["C03312", "SMT7481P"],
        ["C03313", "SMT7540C"],
        ["C03314", "SMT7526U"],
        ["C03315", "EU6363T"],
        ["C03340", "SDW3336T"],
        ["C03341", "SDU448J"],
        ["C03316", "SMT7690C"],
        ["C03317", "EE55G"],
        ["C03318", "SJE24L"],
        ["C03319", "SLZ3003T"],
        ["C03320", "SMT7735H"],
        ["C03321", "SMT7806L"],
        ["C03322", "SKH3931X"],
        ["C03323", "SFA3343P"],
        ["C03324", "EW1100H"],
        ["C03325", "SMT7629G"],
        ["C03326", "SLF9799X"],
        ["C03327", "SK5701P"],
        ["C03328", "SMT7660P"],
        ["C03329", "SFJ4R"],
        ["C03336", "SKX4242K"],
        ["C03338", "SMT7978Z"],
        ["C03339", "SMT8017R"],
        ["C03390", "SFA21M"],
        ["C03330", "SMU50R"],
        ["C03331", "SMT8056C"],
        ["C03332", "SMT8194L"],
        ["C03333", "SJY6543K"],
        ["C03334", "SKA16U"],
        ["C03335", "SMU813G"],
        ["C03342", "SMT8358G"],
        ["C03343", "SMT8404G"],
        ["C03344", "SMU1133D"],
        ["C03347", "SMT8290S"],
        ["C03345", "SDF7070Y"],
        ["C03346", "SMT5888M"],
        ["C03348", "SGY6U"],
        ["C03349", "SMT8471L"],
        ["C03350", "SMT8516S"],
        ["C03351", "SMT8428M"],
        ["C03352", "SMT8424Z"],
        ["C03358", "SLZ919X"],
        ["C03363", "SJA80U"],
        ["C03364", "SFZ9999B"],
        ["C03365", "SKX635L"],
        ["C03367", "SKD991M"],
        ["C03368", "SMU160G"],
        ["C03353", "SJC880S"],
        ["C03354", "SMU1603P"],
        ["C03355", "SMT9341B"],
        ["C03356", "SMT9245S"],
        ["C03357", "SCG8008D"],
        ["C03359", "SKA264U"],
        ["C03360", "SKB186G"],
        ["C03361", "SMT9345L"],
        ["C03362", "SDJ823S"],
        ["C03366", "E11S"],
        ["C03370", "SDV15U"],
        ["C03369", "SJY7813A"],
        ["C03372", "SMU314E"],
        ["C03374", "SKH5229C"],
        ["C03375", "SMJ31D"],
        ["C03376", "SMU428K"],
        ["C03371", "SJP6882D"],
        ["C03373", "SMU114R"],
        ["C03377", "SBT686S"],
        ["C03378", "SMU1192H"],
        ["C03379", "SMU1243U"],
        ["C03386", "SMU3138C"],
        ["C03380", "SMU1517E"],
        ["C03381", "SGR42B"],
        ["C03382", "SMU1579B"],
        ["C03383", "SMU1748E"],
        ["C03384", "SMU2268S"],
        ["C03385", "SMU1660Z"],
        ["C03387", "SMU1663R"],
        ["C03388", "SMU100E"],
        ["C03392", "SMU4477S"],
        ["C03393", "SMU2377K"],
        ["C03394", "SMU2279K"],
        ["C03395", "SMU2316L"],
        ["C03396", "SMU2352G"],
        ["C03397", "SDB8833C"],
        ["C03398", "SGN26U"],
        ["C03399", "SMU2575E"],
        ["C03401", "SMU2615Z"],
        ["C03402", "SMU2723U"],
        ["C03403", "SMU2692A"],
        ["C03400", "SJY6838K"],
        ["C03404", "SMU2857S"],
        ["C03405", "SMU3014B"],
        ["C03498", "SKG801M"],
        ["C03499", "SMU3369C"],
        ["C03501", "SMU3310X"],
        ["C03502", "SMU3164B"],
        ["C03504", "SGV5235H"],
        ["C03507", "SKZ2000U"],
        ["C03415", "SCF18H"],
        ["C03416", "SME24B"],
        ["C03406", "SMU3977Y"],
        ["C03407", "SMU4032S"],
        ["C03413", "SMU3997P"],
        ["C03414", "SBJ99P"],
        ["C03505", "SMU3881P"],
        ["C03408", "SKV316T"],
        ["C03409", "SJY7309P"],
        ["C03410", "SBF5G"],
        ["C03411", "GBK7558E"],
        ["C03412", "SMU4175M"],
        ["C03417", "SMU220S"],
        ["C03418", "SKL613M"],
        ["C03419", "SLU5522A"],
        ["C03420", "SMU4630R"],
        ["C03421", "SMU4518H"],
        ["C03425", "SMU4524P"],
        ["C03426", "SMU4481D"],
        ["C03427", "SMU4695D"],
        ["C03428", "SJF89U"],
        ["C03422", "SMU5098X"],
        ["C03423", "SMU5081T"],
        ["C03424", "SMU5107D"],
        ["C03430", "SMU5317P"],
        ["C03431", "SMU5385S"],
        ["C03432", "SMU5322Z"],
        ["C03433", "SMU5285Y"],
        ["C03434", "SMU5665J"],
        ["C03435", "SMU5562Y"],
        ["C03436", "SMU5651Z"],
        ["C03552", "SMU5893S"],
        ["C03437", "SDD2522D"],
        ["C03438", "SMU5866X"],
        ["C03439", "SMU6132Z"],
        ["C03440", "SMU5983R"],
        ["C03441", "SMU6165D"],
        ["C03442", "SMU6002R"],
        ["C03443", "SMU720R"],
        ["C03444", "SMU628A"],
        ["C03445", "SMU1008H"],
        ["C03446", "SMU6126S"],
        ["C03447", "SMU6113D"],
        ["C03448", "SMU5971A"],
        ["C03449", "SKD908E"],
        ["C03450", "SLB118S"],
        ["C03451", "SMU6341L"],
        ["C03452", "SMU6444Z"],
        ["C03453", "SMV6667S"],
        ["C03455", "SMU6423J"],
        ["C03457", "SFL2231C"],
        ["C03458", "SGZ1314T"],
        ["C03459", "SMU6318E"],
        ["C03454", "SMU6762E"],
        ["C03456", "SKP8237S"],
        ["C03460", "SLP2811L"],
        ["C03461", "SMU6664E"],
        ["C03506", "SMU7631T"],
        ["C03462", "SMU7058Z"],
        ["C03463", "SMU7069S"],
        ["C03464", "SMU7054J"],
        ["C03465", "SKG486P"],
        ["C03466", "SBQ3311U"],
        ["C03467", "SFG7557G"],
        ["C03468", "SMU7101G"],
        ["C03469", "SLP4358R"],
        ["C03470", "SMU7448G"],
        ["C03471", "SMU7411L"],
        ["C03472", "SMU7390M"],
        ["C03473", "SMU7392H"],
        ["C03474", "SMU7532X"],
        ["C03475", "SGE8899L"],
        ["C03476", "SMU7336X"],
        ["C03477", "SLE88D"],
        ["C03478", "SMU7367G"],
        ["C03479", "SMU7563G"],
        ["C03480", "SMU7839L"],
        ["C03481", "SMU7984A"],
        ["C03482", "SMU7914C"],
        ["C03483", "SMU7919P"],
        ["C03484", "SMU8160D"],
        ["C03485", "SMU8132K"],
        ["C03486", "SMU9226S"],
        ["C03487", "SMU8124J"],
        ["C03488", "SKJ7487A"],
        ["C03489", "SMU7816C"],
        ["C03490", "SMU7795D"],
        ["C03491", "SDP6015L"],
        ["C03492", "SMU7874J"],
        ["C03493", "SCN96P"],
        ["C03494", "SFE1102K"],
        ["C03495", "SMT7757U"],
        ["C03496", "SMG9989Y"],
        ["C03497", "SMU7825B"],
        ["C03511", "SFS3838J"],
        ["C03913", "SLD8R"],
        ["C03500", "SMU8299M"],
        ["C03503", "SMU8403D"],
        ["C03508", "SGV7678A"],
        ["C03509", "SJY81R"],
        ["C03510", "SBH778Z"],
        ["C03512", "SBP3888C"],
        ["C03513", "GBG1L"],
        ["C03515", "SJU2862L"],
        ["C03518", "SLG577Z"],
        ["C03519", "SMU9106E"],
        ["C03520", "SJX8006L"],
        ["C03521", "SMU9079A"],
        ["C03522", "SMU8977P"],
        ["C03514", "SMU9151Z"],
        ["C03516", "SMU7717E"],
        ["C03523", "SLT333S"],
        ["C03524", "SKG6468C"],
        ["C03551", "SMU3132T"],
        ["C03517", "SKU3222R"],
        ["C03525", "SMU9578B"],
        ["C03526", "GBK5701X"],
        ["C03527", "SKF9P"],
        ["C03535", "SMU2898Z"],
        ["C03528", "SMT6288M"],
        ["C03529", "SMU9794T"],
        ["C03530", "GW3838B"],
        ["C03532", "SMU9973T"],
        ["C03533", "SJF3550J"],
        ["C03534", "SMV198T"],
        ["C03553", "SMU9882Y"],
        ["C03531", "SMV378R"],
        ["C03536", "SKK4440Z"],
        ["C03537", "SMU6123A"],
        ["C03538", "SMV675H"],
        ["C03539", "SMV676E"],
        ["C03540", "SMV832X"],
        ["C03541", "SMV742Y"],
        ["C03546", "GBK5947D"],
        ["C03547", "SMC339D"],
        ["C03542", "GP4Y"],
        ["C03543", "SMV1453A"],
        ["C03544", "SLU1868D"],
        ["C03545", "SMQ8821B"],
        ["C03548", "SMV1439S"],
        ["C03549", "SDV8258H"],
        ["C03550", "SGE35R"],
        ["C03668", "SLW118G"],
        ["C03554", "SDR1368R"],
        ["C03555", "GBK6071Z"],
        ["C03556", "SMV2151P"],
        ["C03557", "SFJ28Y"],
        ["C03559", "SMS551Y"],
        ["C03558", "SCP3Y"],
        ["C03560", "SMV2459C"],
        ["C03561", "SMV2960X"],
        ["C03562", "SMV2890P"],
        ["C03563", "SMV2951Y"],
        ["C03564", "SMV2860B"],
        ["C03565", "SBD1888D"],
        ["C03566", "SMV3115M"],
        ["C03567", "SMV3027J"],
        ["C03568", "SMV3102A"],
        ["C03569", "SMV3089E"],
        ["C03570", "SMV3014X"],
        ["C03571", "SMV3097G"],
        ["C03572", "SMV3013Z"],
        ["C03573", "SJT9321R"],
        ["C03574", "EH999S"],
        ["C03575", "SMV3251C"],
        ["C03576", "SMV3276G"],
        ["C03577", "SMV3230M"],
        ["C03578", "SJN8168E"],
        ["C03579", "SLS838H"],
        ["C03580", "SMV3026L"],
        ["C03581", "SMV3123P"],
        ["C03582", "SJG9898L"],
        ["C03583", "SMV3543K"],
        ["C03584", "SMR415M"],
        ["C03585", "SMV69H"],
        ["C03586", "SLN30Y"],
        ["C03587", "SKL831A"],
        ["C03661", "EV6811L"],
        ["C03588", "SMV4425L"],
        ["C03589", "SMV4323Y"],
        ["C03590", "SKR5202B"],
        ["C03591", "SMV6771Z"],
        ["C03592", "SJP2772P"],
        ["C03593", "SMV4364D"],
        ["C03594", "SMV4363G"],
        ["C03595", "SMU6118R"],
        ["C03596", "SBQ118G"],
        ["C03597", "SMV4704G"],
        ["C03598", "SLA5794L"],
        ["C03599", "SJS885U"],
        ["C03839", "SCL88Z"],
        ["C03600", "SMV5270J"],
        ["C03601", "SMV8138P"],
        ["C03602", "SFM223J"],
        ["C03603", "SMV8140G"],
        ["C03604", "SDU5252Z"],
        ["C03605", "SLR933X"],
        ["C03606", "SKV8283B"],
        ["C03607", "SMV5101P"],
        ["C03609", "SMV5332P"],
        ["C03610", "SMU2368L"],
        ["C03611", "SBE6J"],
        ["C03608", "SMV5527U"],
        ["C03612", "SMV5523E"],
        ["C03613", "SMV5554R"],
        ["C03614", "SMV5645L"],
        ["C03615", "SGN9961K"],
        ["C03616", "SGR9255U"],
        ["C03617", "SJU112G"],
        ["C03618", "SGK355K"],
        ["C03619", "SDY3928A"],
        ["C03620", "SMV5816K"],
        ["C03623", "SMV6027P"],
        ["C03621", "SMP2215X"],
        ["C03622", "SMV6066B"],
        ["C03624", "SMV6250K"],
        ["C03625", "SMV6445R"],
        ["C03626", "SJW5788M"],
        ["C03631", "SMZ1475R"],
        ["C03627", "SMV6513C"],
        ["C03628", "SMV6523Z"],
        ["C03629", "SMV6488S"],
        ["C03630", "SDR8638R"],
        ["C03632", "SMV6869B"],
        ["C03633", "SMV8322Z"],
        ["C03634", "SMV6765S"],
        ["C03635", "SMV6819X"],
        ["C03636", "SMV7137Z"],
        ["C03637", "SMV7162A"],
        ["C03638", "GBE8D"],
        ["C03639", "SDT3811D"],
        ["C03640", "SMV7240H"],
        ["C03642", "SMV7163Y"],
        ["C03643", "SMV7080C"],
        ["C03644", "SMW7628T"],
        ["C03645", "SMU148T"],
        ["C03646", "SME7772D"],
        ["C03647", "SMV7114P"],
        ["C03669", "SMA2112M"],
        ["C03641", "SMH3533M"],
        ["C03648", "SFM7528J"],
        ["C03649", "SMV7930A"],
        ["C03650", "SMV7917P"],
        ["C03651", "SMV7852S"],
        ["C03652", "SMV7973B"],
        ["C03653", "SMV7840B"],
        ["C03654", "SMV162X"],
        ["C03655", "SDF8812S"],
        ["C03656", "SMV7981C"],
        ["C03662", "SMV8282E"],
        ["C03663", "SKU234B"],
        ["C03664", "SLR2888E"],
        ["C03657", "SMV8840U"],
        ["C03658", "SMV8768X"],
        ["C03665", "SBU3737D"],
        ["C03670", "SMV8987E"],
        ["C03659", "SMN2S"],
        ["C03660", "SMV9423J"],
        ["C03666", "SMV9340P"],
        ["C03692", "EM3636T"],
        ["C03667", "SMW209R"],
        ["C03671", "SMS2424T"],
        ["C03672", "SMV9841K"],
        ["C03673", "SMW8217P"],
        ["C03674", "SMV9773Z"],
        ["C03675", "SMU7979R"],
        ["C03676", "SMW426E"],
        ["C03677", "SMV36C"],
        ["C03678", "SMW855A"],
        ["C03679", "SMW745J"],
        ["C03680", "SBR3838L"],
        ["C03681", "SMW498Y"],
        ["C03682", "SMW346C"],
        ["C03693", "SMW428A"],
        ["C03683", "GBK7192B"],
        ["C03694", "SFA2006U"],
        ["C03684", "SMW1224P"],
        ["C03685", "SKQ2230U"],
        ["C03686", "SMV8830Z"],
        ["C03695", "SMW1226J"],
        ["C03696", "SMW1235H"],
        ["C03687", "SMW1640X"],
        ["C03688", "SGY8P"],
        ["C03689", "SMW1568Y"],
        ["C03697", "SMW1641T"],
        ["C03690", "SMW1895C"],
        ["C03691", "SMW1877E"],
        ["C03698", "SGH8233S"],
        ["C03711", "SMW3900H"],
        ["C03712", "SJL118M"],
        ["C03713", "SFY1638B"],
        ["C03714", "SMW1816G"],
        ["C03715", "GBK7372Z"],
        ["C03699", "SMW2168L"],
        ["C03700", "SMW2032U"],
        ["C03701", "SMW2207H"],
        ["C03702", "SMP9595C"],
        ["C03703", "SMW2182U"],
        ["C03704", "SMW2304K"],
        ["C03705", "SKR2209C"],
        ["C03706", "SLF7737M"],
        ["C03707", "SMW2572C"],
        ["C03708", "SMW2552K"],
        ["C03709", "SMV7722J"],
        ["C03710", "SBH8885U"],
        ["C03716", "SMJ616S"],
        ["C03717", "SMW2778B"],
        ["C03718", "SMW2220T"],
        ["C03719", "SKH9418Y"],
        ["C03720", "SMW2798T"],
        ["C03721", "SMU6111J"],
        ["C03722", "SMW3047X"],
        ["C03723", "SKD727K"],
        ["C03724", "SMW3065T"],
        ["C03725", "SGC9296E"],
        ["C03726", "SMW3134C"],
        ["C03727", "SMW3427H"],
        ["C03728", "SMW3637T"],
        ["C03729", "SMW3569G"],
        ["C03730", "SMW3537Z"],
        ["C03731", "SMW3563Y"],
        ["C03732", "SLA2465G"],
        ["C03733", "SCY9998U"],
        ["C03734", "SMW3848B"],
        ["C03735", "SMW3897J"],
        ["C03736", "SMW4175C"],
        ["C03737", "SMW3962D"],
        ["C03738", "SKK9595C"],
        ["C03739", "SMW4011S"],
        ["C03740", "SMW4043A"],
        ["C03741", "SMW3949T"],
        ["C03742", "SMX111H"],
        ["C03743", "SMW4144S"],
        ["C03756", "SMW3991U"],
        ["C03744", "SMT121A"],
        ["C03745", "SMW4115B"],
        ["C03746", "SMV9229D"],
        ["C03747", "SJZ996X"],
        ["C03748", "SFL9998C"],
        ["C03749", "SMW4157E"],
        ["C03750", "SMW4130G"],
        ["C03751", "SMW4255E"],
        ["C03752", "SKM3778X"],
        ["C03753", "SMX15C"],
        ["C03754", "SMW4185Z"],
        ["C03755", "SMW4229G"],
        ["C03757", "SGA6193Z"],
        ["C03758", "SMW4463X"],
        ["C03759", "SMW4452C"],
        ["C03760", "SLK8158B"],
        ["C03761", "SMV6118K"],
        ["C03762", "SMW4634U"],
        ["C03763", "SMW4597T"],
        ["C03764", "SMW5528L"],
        ["C03765", "SMW4795M"],
        ["C03766", "SMW4812Y"],
        ["C03767", "SJT7176G"],
        ["C03768", "SLF228L"],
        ["C03769", "SFE6609H"],
        ["C03793", "SMX3588X"],
        ["C03770", "SMW4966L"],
        ["C03771", "SMW4835G"],
        ["C03772", "SMW4885L"],
        ["C03773", "SBJ5885D"],
        ["C03774", "SMW5061R"],
        ["C03775", "SMW5049C"],
        ["C03776", "SMW5030E"],
        ["C03777", "SJS800P"],
        ["C03778", "SMW5008X"],
        ["C03779", "SKN8883L"],
        ["C03780", "SMW5153J"],
        ["C03781", "GT50S"],
        ["C03782", "SKF8885Z"],
        ["C03783", "SMW5272Z"],
        ["C03784", "SMW5264Y"],
        ["C03785", "SMW5181C"],
        ["C03786", "SMW5240R"],
        ["C03787", "SMW5250L"],
        ["C03788", "SLS7555C"],
        ["C03789", "SMW5327A"],
        ["C03790", "SGY8132K"],
        ["C03791", "SLD1668D"],
        ["C03792", "EK33M"],
        ["C03794", "SMW5781X"],
        ["C03795", "SMX5151J"],
        ["C03796", "SMW5754A"],
        ["C03797", "SMW5718E"],
        ["C03798", "SMW5833E"],
        ["C03799", "SMX1396X"],
        ["C03800", "SMW5761D"],
        ["C03801", "SMW5652K"],
        ["C03802", "SMW5766R"],
        ["C03803", "SMW5670H"],
        ["C03804", "SMW5719C"],
        ["C03805", "SGJ88L"],
        ["C03806", "SMV8318L"],
        ["C03807", "SJR102B"],
        ["C03808", "SLE6037L"],
        ["C03809", "SMU9969G"],
        ["C03810", "SMW6292J"],
        ["C03811", "SMW6285E"],
        ["C03812", "SMW6389P"],
        ["C03813", "SDX75K"],
        ["C03814", "Q7S"],
        ["C03815", "SCP1189M"],
        ["C03816", "SMW6565Y"],
        ["C03817", "SMU815B"],
        ["C03818", "SMW6805E"],
        ["C03819", "SDY3388H"],
        ["C03820", "SJN9751P"],
        ["C03821", "SMW6904C"],
        ["C03822", "SMW7037Z"],
        ["C03823", "SMW6965B"],
        ["C03824", "SMW7030S"],
        ["C03825", "SJY99R"],
        ["C03852", "SDP23C"],
        ["C03853", "SLM1993S"],
        ["C03826", "SMW7100Z"],
        ["C03827", "SMW7192H"],
        ["C03828", "GBK8010L"],
        ["C03829", "SMW7105K"],
        ["C03830", "SMW7229L"],
        ["C03831", "GY6U"],
        ["C03832", "SMW7195A"],
        ["C03833", "SMW7152Z"],
        ["C03834", "SKM932L"],
        ["C03840", "SGH201L"],
        ["C03835", "SLM6616Z"],
        ["C03836", "SMW7315X"],
        ["C03837", "SMW7092M"],
        ["C03838", "SMW7144Y"],
        ["C03841", "SMN805S"],
        ["C03429", "SMU4553E"],
        ["C03842", "SMW6664U"],
        ["C03843", "SJG9X"],
        ["C03844", "SMW7507J"],
        ["C03845", "SMU310R"],
        ["C03846", "SMW7632E"],
        ["C03847", "SMW7562Z"],
        ["C03848", "SMW7659D"],
        ["C03849", "SMW7865A"],
        ["C03850", "SMW7751U"],
        ["C03851", "SMW7689S"],
        ["C03854", "SMW7810H"],
        ["C03855", "SKV3459U"],
        ["C03856", "SMW7817P"],
        ["C03857", "SDK8893G"],
        ["C03858", "SJK5399R"],
        ["C03859", "SMW1648A"],
        ["C03860", "SMW7877R"],
        ["C03861", "SMT75A"],
        ["C03862", "SMW7918G"],
        ["C03863", "SMW7892X"],
        ["C03864", "SMW7843M"],
        ["C03865", "SCN8587"],
        ["C03866", "SKQ7373H"],
        ["C03867", "SJS2908A"],
        ["C03868", "SMX336B"],
        ["C03869", "SMW7989B"],
        ["C03870", "SMW8043Z"],
        ["C03871", "SMW8051A"],
        ["C03872", "SMW9678K"],
        ["C03873", "SMW8046R"],
        ["C03874", "SMV1282B"],
        ["C03875", "SJA4396H"],
        ["C03876", "SMW7972Z"],
        ["C03887", "SMW8105D"],
        ["C03877", "SMW8285S"],
        ["C03878", "SMW8259T"],
        ["C03879", "SW555U"],
        ["C03880", "SMW8221B"],
        ["C03881", "SMW8263E"],
        ["C03882", "SMW8571R"],
        ["C03883", "ER3003T"],
        ["C03884", "SCL9C"],
        ["C03885", "SFR8800P"],
        ["C03886", "EW10R"],
        ["C03917", "SMW8483L"],
        ["C03918", "SJY8017A"],
        ["C03890", "SMW8950E"],
        ["C03891", "SMW8782Z"],
        ["C03892", "SMW8931K"],
        ["C03893", "SMW8892P"],
        ["C03894", "SMW8915H"],
        ["C03895", "SKB5588Y"],
        ["C03896", "SMW6858C"],
        ["C03901", "SCE9P"],
        ["C03945", "SMX5520C"],
        ["C03888", "SMW9109L"],
        ["C03889", "SMW9281X"],
        ["C03897", "SMW9101H"],
        ["C03898", "SJD1091R"],
        ["C03899", "SMW9287E"],
        ["C03900", "SMW9162G"],
        ["C03912", "SMW9140U"],
        ["C03902", "SKD3380G"],
        ["C03903", "SMW9351C"],
        ["C03904", "SKD323S"],
        ["C03905", "EU25J"],
        ["C03906", "SMX8333G"],
        ["C03907", "GBK8481K"],
        ["C03908", "SKH15K"],
        ["C03909", "SMW9344Z"],
        ["C03910", "SMW3663S"],
        ["C03911", "SMW9524X"],
        ["C03914", "SKA27M"],
        ["C03915", "SMW9732L"],
        ["C03916", "SMX230Y"],
        ["C03919", "SKD855Z"],
        ["C03920", "SMX119L"],
        ["C03921", "SMW17C"],
        ["C03922", "SMX329Y"],
        ["C03923", "SLX4826P"],
        ["C03924", "SMX424E"],
        ["C03925", "SJN885X"],
        ["C03926", "SMX8484D"],
        ["C03927", "SMX578U"],
        ["C03928", "SJY1888Z"],
        ["C03946", "SMX593A"],
        ["C03929", "SMX879B"],
        ["C03930", "SMX3338B"],
        ["C03931", "SKN4269L"],
        ["C03932", "SMX806L"],
        ["C03933", "SFY6832Z"],
        ["C03934", "SMS8800B"],
        ["C03935", "SGN8208J"],
        ["C03936", "SMX4439L"],
        ["C03937", "SLE18G"],
        ["C03938", "SMX884K"],
        ["C03939", "SGF5055U"],
        ["C03940", "SLP328D"],
        ["C03941", "SMX1007U"],
        ["C03942", "SKN1400T"],
        ["C03943", "SMX1023Y"],
        ["C03944", "SGR8021J"],
        ["C03947", "SJW98D"],
        ["C03948", "SMX1458B"],
        ["C03949", "SMN1211Z"],
        ["C03950", "SMQ2B"],
        ["C03951", "SMX1914B"],
        ["C03952", "SMX1878Y"],
        ["C03953", "SKF31Z"],
        ["UC00213", "SMR1376K"],
        ["UC00216", "SKG7588H"],
        ["UC00221", "SMS1093X"],
        ["UC00218", "SMS7943D"],
        ["UC00219", "SKM5937R"],
        ["UC00220", "SMQ6110H"],
        ["UC00222", "SKP6592D"],
        ["UC00223", "SMT1516M"],
        ["UC00224", "SGR6503Z"],
        ["UC00225", "SMS8684U"],
        ["UC00226", "SKX2054A"],
        ["UC00227", "SLA9345Z"],
        ["UC00228", "SMQ4575M"],
        ["UC00229", "SJR7168R"],
        ["UC00230", "SMT6071X"],
        ["UC00231", "SGL9696L"],
        ["UC00232", "SBM1868P"],
        ["UC00234", "SMT5649L"],
        ["UC00233", "SMT5062D"],
        ["UC00235", "SMS8469A"],
        ["UC00243", "SJZ1551P"],
        ["UC00237", "SMT6450K"],
        ["UC00238", "SJY3079D"],
        ["UC00236", "SMT5142G"],
        ["UC00242", "SMU8297T"],
        ["UC00239", "SLM2738G"],
        ["UC00240", "SKS8702A"],
        ["UC00241", "EZ338E"],
        ["UC00244", "SJE5999R"],
        ["UC00245", "SCU9981M"],
        ["UC00246", "SJY3662U"],
        ["UC00247", "SLW1112R"],
        ["UC00248", "SMC8800K"],
        ["UC00249", "SMM6888T"],
        ["UC00250", "SMT5778Y"],
        ["UC00251", "SMU1710M"],
        ["UC00252", "SBL2888E"],
        ["UC00253", "SMT227D"],
        ["UC00254", "SMT9872G"],
        ["UC00255", "SMS8362Z"],
        ["UC00256", "SMG3211Y"],
        ["UC00257", "SMC4569E"],
        ["UC00258", "SGD8638D"],
        ["UC00259", "SMU9776X"],
        ["UC00260", "SMU8741C"],
        ["UC00261", "SMT9846H"],
        ["UC00262", "SMQ5018Z"],
        ["UC00263", "SKQ9642T"],
        ["UC00264", "SLJ5327E"],
        ["UC00265", "SMU8166M"],
        ["UC00268", "SKT148Y"],
        ["UC00266", "SLF7888K"],
        ["UC00267", "SLA8533D"],
        ["UC00269", "SJY9533U"],
        ["UC00270", "SFW555Z"],
        ["UC00274", "SMV178B"],
        ["UC00275", "SMU9409G"],
        ["UC00271", "SMV3455G"],
        ["UC00272", "SKN1140T"],
        ["UC00273", "GBK5559R"],
        ["UC00276", "SFC1133G"],
        ["UC00277", "SMV5831R"],
        ["UC00279", "SKJ9231H"],
        ["UC00278", "SLP2917P"],
        ["UC00280", "SMV4709T"],
        ["UC00282", "SMV5465P"],
        ["UC00288", "SFS2668P"],
        ["UC00285", "SKT4066Z"],
        ["UC00281", "SMV7286B"],
        ["UC00283", "SLE2664C"],
        ["UC00284", "SML9475L"],
        ["UC00286", "SLU8876J"],
        ["UC00294", "SKP4232G"],
        ["UC00287", "SLT2669K"],
        ["UC00289", "SKJ2495J"],
        ["UC00290", "SCM1221T"],
        ["UC00291", "SMW7115G"],
        ["UC00292", "SMV8789K"],
        ["UC00295", "SLU5565B"],
        ["UC00293", "SLM78R"],
        ["UC00296", "SMW1759M"],
        ["UC00297", "SKD4233S"],
        ["UC00298", "SMW6826U"],
        ["UC00299", "SMW6813G"],
        ["UC00300", "SLQ4833E"],
        ["C03954", "SCY1E"],
        ["C03955", "SMX2246P"],
        ["C03956", "SLM4444R"],
        ["C03957", "SMX2561D"],
        ["C03958", "SKJ3881X"],
        ["C03959", "SMX2527D"],
        ["C03960", "SMX2550K"],
        ["C03961", "SMX2504U"],
        ["C03962", "SMX2594J"],
        ["C03963", "SMX2495L"],
        ["C03964", "SBB3888B"],
        ["C03965", "SMX2483X"],
        ["C03966", "SKB9818H"],
        ["C03967", "SMY2950J"],
        ["C03968", "SMX2794Z"],
        ["C03969", "SGS3377K"],
        ["C03970", "SMX3058J"],
        ["C03971", "SMY3368J"],
        ["C03972", "SMX3199K"],
        ["C03973", "SMX3155L"],
        ["C03974", "SJD3C"],
        ["C03975", "SLE8380G"],
        ["C03976", "SMR8558G"],
        ["C03977", "SMY8118G"],
        ["C03978", "SMX3382Y"],
        ["C03979", "SLS3130X"],
        ["C03980", "SMY898R"],
        ["C03981", "ET1212J"],
        ["C03982", "SGS880T"],
        ["C03983", "SMX3488B"],
        ["C03984", "SMX3525C"],
        ["C03985", "SMX3569B"],
        ["C03986", "SMX3629L"],
        ["C03987", "SMX3533D"],
        ["C03988", "SMX3480Y"],
        ["C03989", "SMX3485J"],
        ["C03990", "SGM5555G"],
        ["C03991", "SBF1104Y"],
        ["C03992", "SMX3956S"],
        ["C03993", "SBC2829Z"],
        ["C03994", "SMW8421R"],
        ["C03995", "SGW8080L"],
        ["C03996", "SMX4150T"],
        ["C03997", "SKC3525L"],
        ["C03998", "SMX4121C"],
        ["C03999", "SMX4167X"],
        ["C04000", "SMX4408B"],
        ["C04001", "SMX4489S"],
        ["C04002", "SMX4479X"],
        ["C04003", "SMX4436U"],
        ["C04004", "SMX4390S"],
        ["C04005", "SKA1017K"],
        ["C04006", "SMA6888G"],
        ["C04007", "SGS448J"],
        ["C04008", "EP72Z"],
        ["C04009", "SLZ9389R"],
        ["C04010", "SLL9988K"],
        ["C04011", "SBJ4745G"],
        ["C04012", "SMX4610H"],
        ["C04013", "SMT2200S"],
        ["C04014", "SMU9929Y"],
        ["C04015", "SMX8428S"],
        ["C04016", "SKU1161X"],
        ["C04017", "SJF1514C"],
        ["C04018", "SMX4842E"],
        ["C04019", "SMX866M"],
        ["C04020", "EF280S"],
        ["C04021", "SJB668U"],
        ["C04022", "SMX4916B"],
        ["C04023", "SMX4882P"],
        ["C04024", "SMX5331G"],
        ["C04025", "SGP3147Z"],
        ["C04026", "SMX5150L"],
        ["C04027", "SMX5192R"],
        ["C04028", "SFU233M"],
        ["C04029", "SMX5459Y"],
        ["C04030", "SMX5491C"],
        ["C04031", "SJR1609A"],
        ["C04032", "SMY1512Y"],
        ["C04033", "SLA1898Y"],
        ["C04034", "SMX5779Z"],
        ["C04035", "SMX5696D"],
        ["C04036", "SMY112A"],
        ["C04037", "SKJ9119A"],
        ["C04038", "SMY12E"],
        ["C04039", "SJY2221R"],
        ["C04040", "SJN5155L"],
        ["C04041", "SMX5930C"],
        ["C04042", "SFD101A"],
        ["C04043", "SLG9331G"],
        ["C04044", "SMX6039U"],
        ["C04045", "SMX6009G"],
        ["C04046", "SMX5942T"],
        ["C04047", "SMX6008J"],
        ["C04048", "SJZ8113A"],
        ["C04049", "EQ222C"],
        ["C04050", "EF8886S"],
        ["C04051", "EL2223M"],
        ["C04052", "SMX6289P"],
        ["C04053", "SKJ6899B"],
        ["C04054", "SMX6256J"],
        ["C04055", "SLC5830E"],
        ["C04056", "SLS9928Y"],
        ["C04057", "SLT1987Z"],
        ["C04058", "SMY6779L"],
        ["C04059", "SMX6560E"],
        ["C04060", "SKB8891B"],
        ["C04061", "SFU506B"],
        ["C04062", "SMX6744S"],
        ["C04063", "SMX6864D"],
        ["C04064", "SMX6833T"],
        ["C04065", "SMX6796S"],
        ["C04066", "SMX7202H"],
        ["C04067", "SKK6680J"],
        ["C04068", "SJW8310M"],
        ["C04069", "SMX7184B"],
        ["C04070", "SMX7105E"],
        ["C04071", "SMX7506G"],
        ["C04072", "SMX7505J"],
        ["C04073", "SFY2896R"],
        ["C04074", "SMY7373S"],
        ["C04075", "SMX7464T"],
        ["C04076", "SGK8515M"],
        ["C04077", "SCG7888L"],
        ["C04078", "ED28R"],
        ["C04079", "SMX7881Y"],
        ["C04080", "SMX7920T"],
        ["C04081", "SMX7876M"],
        ["C04082", "SLK1109P"],
        ["C04083", "SMX7930P"],
        ["C04084", "SMX7957M"],
        ["C04085", "SMX8023B"],
        ["C04086", "SMX8190B"],
        ["C04087", "SJK140C"],
        ["C04088", "SCW111G"],
        ["C04089", "SMX7945Y"],
        ["C04090", "SMX8219D"],
        ["C04091", "SMX8382P"],
        ["C04092", "SMX8352B"],
        ["C04093", "SMX8403M"],
        ["C04094", "SMX8386D"],
        ["C04095", "SFE1A"],
        ["C04096", "SBW8900E"],
        ["C04097", "SGH3659E"],
        ["C04098", "EE7272A"],
        ["C04099", "SMX8607S"],
        ["C04100", "SMX8551T"],
        ["C04101", "SMX8691Y"],
        ["C04102", "SJR1972A"],
        ["C04103", "SFE9119K"],
        ["C04104", "SGJ7373S"],
        ["C04105", "SKD990R"],
        ["C04106", "SKZ6838T"],
        ["C04107", "SMX9017M"],
        ["C04108", "SMY929K"],
        ["C04109", "SMX8976B"],
        ["C04110", "SGH2274M"],
        ["C04111", "ER888B"],
        ["C04112", "SMX9052K"],
        ["C04113", "SLL91G"],
        ["C04115", "SKE2420B"],
        ["C04114", "SMX9215H"],
        ["C04116", "SMX9341B"],
        ["C04117", "SMX9223J"],
        ["C04118", "SLP9689C"],
        ["C04119", "SGC51D"],
        ["C04120", "SMX177T"],
        ["C04121", "SMX9253Y"],
        ["C04122", "SMX9321J"],
        ["C04123", "SMX9300U"],
        ["C04124", "SMX9299R"],
        ["C04125", "SMX9256P"],
        ["C04126", "SMX9281R"],
        ["C04127", "SMX9296Z"],
        ["C04128", "SMX9453L"],
        ["C04129", "SMY3336B"],
        ["C04130", "SMX9617G"],
        ["C04131", "SMX9675M"],
        ["C04132", "SMX1005A"],
        ["C04133", "SMX9606M"],
        ["C04134", "SMY8988L"],
        ["C04135", "SMX9622R"],
        ["C04136", "SBD22S"],
        ["C04137", "SKL6646D"],
        ["C04138", "SBZ2726R"],
        ["C04139", "SMM9988T"],
        ["C04140", "SMX9702T"],
        ["C04141", "SMX8829T"],
        ["C04142", "SMY240M"],
        ["C04143", "SJL3459J"],
        ["C04144", "SJF62X"],
        ["C04145", "GT99C"],
        ["C04146", "SKQ7573Y"],
        ["C04147", "SKT1600B"],
        ["C04148", "SMY347P"],
        ["C04149", "SMW6093S"],
        ["C04150", "SMY132S"],
        ["C04151", "SDD1828D"],
        ["C04152", "SMY204T"],
        ["C04153", "GBK9893Z"],
        ["C04154", "SMY538E"],
        ["C04155", "SMV7889L"],
        ["C04156", "SGV8188R"],
        ["C04157", "SMY1450S"],
        ["C04158", "SFG1608H"],
        ["C04159", "SMY1626C"],
        ["C04160", "SMY1678C"],
        ["C04161", "SMY1679A"],
        ["C04162", "SMV969K"],
        ["C04163", "SMY1851T"],
        ["C04164", "SMY1740E"],
        ["C04165", "GBE5570M"],
        ["C04166", "SDH89E"],
        ["C04167", "SMY2136T"],
        ["C04168", "SMY2088Z"],
        ["C04169", "SMY2134Z"],
        ["C04170", "SLZ14G"],
        ["C04171", "GBL896U"],
        ["C04172", "SMY2446Z"],
        ["C04173", "SMZ616H"],
        ["C04174", "SMY2536Y"],
        ["C04175", "SMY2529T"],
        ["C04176", "SDT23G"],
        ["C04177", "SMY2511T"],
        ["C04178", "SMY2540J"],
        ["C04179", "SKT2738G"],
        ["C04180", "SMA161J"],
        ["C04181", "SMY2955X"],
        ["C04182", "SMY2918D"],
        ["C04183", "SMY2982S"],
        ["C04184", "SMY2936B"],
        ["C04185", "SMY2997A"],
        ["C04186", "SMY3090J"],
        ["C04187", "SJT1488X"],
        ["C04188", "SFH7777H"],
        ["C04189", "SFK222Y"],
        ["C04190", "SFZ4177P"],
        ["C04191", "SDM43K"],
        ["C04192", "SLZ62S"],
        ["C04193", "SDM43K"],
        ["C04194", "SMY3457K"],
        ["C04195", "SMY3560U"],
        ["C04196", "SFR1001A"],
        ["C04197", "SMY3719E"],
        ["C04198", "SMY3720A"],
        ["C04199", "SMY3681D"],
        ["C04200", "GBL7799X"],
        ["C04201", "SFC1K"],
        ["C04202", "SKJ3313U"],
        ["C04203", "SMY3766T"],
        ["C04204", "SLQ2157L"],
        ["C04205", "SLT333S"],
        ["C04206", "SMY4017R"],
        ["C04207", "SLL8055J"],
        ["C04208", "SMX6818M"],
        ["C04209", "SMY4096L"],
        ["C04210", "SDS27B"],
        ["C04211", "SMZ2020M"],
        ["C04212", "SMY4086R"],
        ["C04213", "SMY7678M"],
        ["C04214", "SMY4068T"],
        ["C04215", "SMY4345T"],
        ["C04216", "SMY4624M"],
        ["C04217", "EX7199D"],
        ["C04218", "SKB8336M"],
        ["C04219", "SMY4611A"],
        ["C04220", "SMZ523S"],
        ["C04221", "SMY4800X"],
        ["C04222", "SMY4673X"],
        ["C04223", "SMY4781S"],
        ["C04224", "SMY4688D"],
        ["C04225", "SMK955H"],
        ["C04226", "SMY5021X"],
        ["C04227", "SMY4949B"],
        ["C04228", "SMY5028C"],
        ["C04229", "SMY4970M"],
        ["C04230", "GBK9866C"],
        ["C04231", "SMY5062C"],
        ["C04232", "SMY5041M"],
        ["C04233", "SMY4996P"],
        ["C04234", "SLA92L"],
        ["C04235", "SGD8730U"],
        ["C04236", "SMY5231G"],
        ["C04237", "SMY5261U"],
        ["C04238", "SMZ990B"],
        ["C04239", "SMY5232D"],
        ["C04240", "SMY5372H"],
        ["C04241", "SMY5368X"],
        ["C04242", "EU126B"],
        ["C04243", "GBL9191K"],
        ["C04244", "SMY5403C"],
        ["C04245", "SKA2008E"],
        ["C04246", "SMZ8101Z"],
        ["C04247", "SKC1688M"],
        ["C04248", "SMY5770S"],
        ["C04249", "SMY5891B"],
        ["C04250", "SMY5720L"],
        ["C04251", "SMY6064R"],
        ["C04252", "SMY6144T"],
        ["C04253", "SMY6135U"],
        ["C04254", "SMY6174G"],
        ["C04255", "SMY6190J"],
        ["C04256", "SMY6223Z"],
        ["C04257", "SJJ294Y"],
        ["C04258", "SMY6399A"],
        ["C04259", "SMV7717A"],
        ["C04260", "SMY656Y"],
        ["C04261", "SDK5111A"],
        ["C04262", "SMY6495E"],
        ["C04263", "SMZ380L"],
        ["C04264", "SLA10Z"],
        ["C04265", "SMY6557K"],
        ["C04266", "SMY6523H"],
        ["C04267", "SCY9238E"],
        ["C04268", "SGZ120S"],
        ["C04269", "SMY7087T"],
        ["C04270", "SMY7173C"],
        ["C04271", "SMY7120E"],
        ["C04272", "SMY7101K"],
        ["C04273", "SMY7104C"],
        ["C04274", "SJH6070Y"],
        ["C04275", "SMY7380X"],
        ["C04276", "SFY31H"],
        ["C04277", "SKW72R"],
        ["C04278", "SMY7396B"],
        ["C04279", "SMY7448K"],
        ["C04280", "SMY7444X"],
        ["C04281", "SMY7436U"],
        ["C04282", "SMY7467E"],
        ["C04283", "SCL4000A"],
        ["C04284", "SMZ7500L"],
        ["C04285", "GBL3043U"],
        ["C04286", "SLG990M"],
        ["C04287", "SMY7674Z"],
        ["C04288", "SMF25T"],
        ["C04289", "SMY8068S"],
        ["C04290", "SKW788J"],
        ["C04291", "SMZ2789D"],
        ["C04292", "SMC9G"],
        ["C04293", "SLB2605P"],
        ["C04294", "SJJ6041X"],
        ["C04295", "SBU1112U"],
        ["C04296", "SMY8045H"],
        ["C04297", "SDY1717E"],
        ["C04298", "SBQ1888K"],
        ["C04299", "SMZ31U"],
        ["C04300", "SMY8939D"],
        ["C04301", "SFM50R"],
        ["C04302", "SJM5109B"],
        ["C04303", "SLJ8829D"],
        ["C04304", "SMY9307X"],
        ["C04305", "SMY9335P"],
        ["C04306", "SMZ9188S"],
        ["C04307", "SGZ5885K"],
        ["C04569", "SFU21H"],
        ["C04570", "SLC8968Y"],
        ["C04571", "SNA2941A"],
        ["C04572", "SCY32R"],
        ["C04573", "SDA1380T"],
        ["C04574", "EV8698J"],
        ["C04575", "SLE1112K"],
        ["C04576", "SNA3372K"],
        ["C04577", "SFW2227H"],
        ["C04578", "SJA8991M"],
        ["C04579", "SMY9418J"],
        ["UC00301", "SLM6679S"],
        ["UC00302", "SKH526C"],
        ["UC00303", "SMW2571E"],
        ["UC00304", "SLU7948S"],
        ["UC00305", "SMW2490E"],
        ["UC00306", "GBK6448X"],
        ["UC00307", "SDT3898D"],
        ["UC00308", "SML9915J"],
        ["UC00309", "SKH526C"],
        ["UC00310", "SMW6184M"],
        ["UC00311", "SLA7615H"],
        ["UC00312", "SMW7857Z"],
        ["UC00313", "SMX695P"],
        ["UC00314", "SMX3494H"],
        ["UC00315", "SMS6666K"],
        ["UC00316", "SKT3209K"],
        ["UC00317", "SKP6982L"],
        ["UC00318", "SLB2635C"],
        ["UC00319", "SKH5271D"],
        ["UC00320", "SLW439E"],
        ["UC00321", "SMW6510E"],
        ["UC00322", "SMP5477L"],
        ["UC00323", "SMX4496X"],
        ["UC00324", "SKD8149B"],
        ["UC00325", "SMX4051X"],
        ["UC00326", "SLD6746D"],
        ["UC00327", "SMX6486L"],
        ["UC00328", "SLN788U"],
        ["UC00329", "SKM879H"],
        ["UC00330", "SJU8103E"],
        ["UC00331", "SME7943C"],
        ["UC00332", "SMX6849Z"],
        ["UC00333", "SLT6666S"],
        ["UC00334", "SLU3106G"],
        ["UC00335", "SKA6759U"],
        ["UC00336", "SMX6774E"],
        ["UC00337", "SLW6681G"],
        ["UC00338", "SLJ3355L"],
        ["UC00339", "SMX5958Z"],
        ["UC00340", "SMX9024S"],
        ["UC00341", "SMX5255T"],
        ["UC00342", "SMY6896G"],
        ["UC00343", "SMW9022C"],
        ["UC00344", "SMY3526U"],
        ["UC00345", "SKK2850L"],
        ["UC00346", "SMY520E"],
        ["UC00347", "SMY2561Z"],
        ["UC00348", "SMR4920U"],
        ["UC00349", "SKK7328X"],
        ["UC00350", "SMX7470A"],
        ["UC00351", "SGG7800S"],
        ["UC00352", "SMY2205C"],
        ["UC00353", "SLC540A"],
        ["UC00354", "SKP3609K"],
        ["UC00355", "SMY4441Z"],
        ["UC00356", "SLE6020J"],
        ["UC00357", "SMY5916P"],
        ["UC00358", "SMR793X"],
        ["UC00359", "SLA8180M"],
        ["UC00360", "SMV8821A"],
        ["UC00361", "SLZ9362S"],
        ["UC00362", "SMX7442G"]
    ];
}
