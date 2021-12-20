<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use App\Models\Warranty;

class ExportWarranty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:warranty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export Warranty';

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

        $warranties = Warranty::with(['vehicle','proposer'])->get();
        Storage::put("output.csv", 'Ref No, Type, CI No, Package, Price, Max Claim Per Year, Mileage Coverage, Warranty Duration, Dealer, Start Date, End Date, Created At, Car Plate, Chassis No, Make, Model, Engine No, Manufacture Year, Registration Date, Capacity, Category, NRIC, Salutation, Name, Address, Email, Phone, Status, Remarks');    
        foreach($warranties as $warranty){
            Storage::append("output.csv", "");
            Storage::append("output.csv", $warranty->ref_no . ",", null);
            Storage::append("output.csv", $warranty->vehicle->type . ",", null);
            Storage::append("output.csv", $warranty->ci_no . ",", null);
            Storage::append("output.csv", $warranty->package . ",", null);
            Storage::append("output.csv", $warranty->price . ",", null);
            Storage::append("output.csv", $warranty->max_claim . ",", null);
            Storage::append("output.csv", $warranty->mileage_coverage . ",", null);
            Storage::append("output.csv", $warranty->warranty_duration . ",", null);
            if( $warranty->dealer != null ){
                Storage::append("output.csv", $warranty->dealer->name . ",", null);
            } else {
                Storage::append("output.csv", ",", null);
            }
            Storage::append("output.csv", $warranty->start_date . ",", null);
            Storage::append("output.csv", $warranty->end_date . ",", null);
            Storage::append("output.csv", $warranty->created_at . ",", null);
            Storage::append("output.csv", $warranty->vehicle->registration_no . ",", null);
            Storage::append("output.csv", $warranty->vehicle->chassis_no . ",", null);
            Storage::append("output.csv", $warranty->vehicle->make . ",", null);
            Storage::append("output.csv", $warranty->vehicle->model . ",", null);
            Storage::append("output.csv", $warranty->vehicle->engine_no . ",", null);
            Storage::append("output.csv", $warranty->vehicle->manufacture_year . ",", null);
            Storage::append("output.csv", $warranty->vehicle->registration_date . ",", null);
            Storage::append("output.csv", $warranty->vehicle->capacity . ",", null);
            Storage::append("output.csv", $warranty->vehicle->category . ",", null);
            Storage::append("output.csv", $warranty->proposer->nric_uen . ",", null);
            Storage::append("output.csv", $warranty->proposer->salutation . ",", null);
            Storage::append("output.csv", str_replace(",","",$warranty->proposer->name) . ",", null);
            Storage::append("output.csv", str_replace(",","",$warranty->proposer->address) . ",", null);
            Storage::append("output.csv", $warranty->proposer->email . ",", null);
            Storage::append("output.csv", $warranty->proposer->phone . ",", null);
            Storage::append("output.csv", $warranty->status . ",", null);
            Storage::append("output.csv", str_replace(",","",$warranty->remarks) . ",", null);
        }
    }
}
