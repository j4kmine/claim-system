<?php

namespace App\Http\Controllers\Mobile\Warranty;

use App\Activities\BuyWarranty;
use App\Activities\EnquiryWarranty;
use Throwable;
use App\Models\Driver;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\CustomerVehicle;
use App\Models\CustomerWarranty;
use App\Models\Warranty;
use App\Models\Proposer;
use App\Models\Package;
use Illuminate\Support\Str;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\WarrantyPrice;
use App\Models\WarrantyDocument;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Log;
use Storage;
use Illuminate\Support\Collection;

class WarrantyController extends Controller
{
    use ApiResponser;

    public function packages(Request $request)
    {   
        $packages = Package::get();
        return $this->success($packages);
    }

    public function index(Request $request)
    {
        $customer = Auth::user();
        $query = $customer->warranties()->with(['vehicle', 'proposer', 'insurer:id,code'])->orderBy('warranties.id', 'desc');
        if($request->status == 'pending'){
            $query = $query->where('warranties.status', '!=', 'draft')->where('warranties.status', '!=', 'completed');
        }
        if($request->limit != null){
            $query = $query->limit($request->limit);
        }
        return $this->success($query->get());
    }

    public function show(Request $request, Warranty $warranty)
    {
        $customer = Auth::user();
        $warranty = $customer->warranties()->with(['vehicle', 'proposer', 'documents', 'insurer:id,code'])->where('warranties.id', $warranty->id)->first();
        return $this->success($warranty);
    }

    /**
     * Buy Warranty,
     * with a vehicle model and with specific price
     */
    public function buy(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'price_id' => 'required|exists:warranty_prices,id',
            'registration_no' => 'required|regex:/^[A-Z]{1,5}\d{1,5}[A-Z0-9]$/',
            // 'make' => 'required',
            // 'model' => 'required',
            // 'type' => 'required|in:new,preowned',
            // 'fuel' => 'required|in:hybrid,non_hybrid',
            'mileage' => 'nullable',
            'manufacture_year' => 'required',
            'registration_date' => 'required|date_format:d/m/Y',
            'chassis_no' => 'required',
            'engine_no' => 'required',
            'start_date' => 'required|date_format:d/m/Y',
            'log_cards' => 'required|array',
            'assessment_reports' => 'nullable|array'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        // If the car is pre owned, then need mileage and assesment report
        if ($request->type == 'preowned') {
            if(!isset($request->mileage)){
                return response()->json(['message' => 'The mileage field is required.'], 422);
            }
            if(!isset($request->assessment_reports)){
                return response()->json(['message' => 'The assessment reports field is required.'], 422);
            }
        }

        try {
            DB::beginTransaction();

            $customer = Auth::user();

            $warrantyPrice = WarrantyPrice::find($request->price_id);
            $car_exist = Vehicle::where('registration_no', $request->registration_no)->first();
            if($car_exist == null){ 
                $vehicle = new Vehicle;
            }else{
                $vehicle = Vehicle::where('registration_no', $request->registration_no)->first();
            }
            $vehicle->registration_no = $request->registration_no;
            $vehicle->make = $warrantyPrice->make;
            $vehicle->model = $warrantyPrice->model;
            $vehicle->category = $warrantyPrice->category;
            $vehicle->capacity = $warrantyPrice->capacity;
            $vehicle->type = $warrantyPrice->type;
            $vehicle->fuel = $warrantyPrice->fuel;
            $vehicle->mileage = $request->mileage;
            $vehicle->manufacture_year = $request->manufacture_year;
            $vehicle->registration_date = Carbon::createFromFormat('d/m/Y', $request->registration_date);
            $vehicle->chassis_no = $request->chassis_no;
            $vehicle->engine_no = $request->engine_no;
            $vehicle->nric_uen = $customer->nric_uen;
            $vehicle->save();

            $proposer = new Proposer;
            $proposer->nric_uen = $customer->nric_uen;
            $proposer->salutation = $customer->salutation;
            $proposer->name = $customer->name;
            $proposer->address = $customer->address;
            $proposer->email = $customer->email;
            $proposer->phone = $customer->phone;
            $proposer->save();

            $warranty = Warranty::create([
                'vehicle_id' => $vehicle->id,
                'proposer_id' => $proposer->id,
                'insurer_id' => $warrantyPrice->insurer_id,
                'creator_id' => $customer->id, 
                'customer_id' => $customer->id,
                'ref_no' => Str::random(10),
                'dealer_id' => 23,
                'start_date' => Carbon::createFromFormat('d/m/Y', $request->start_date),
                'warranty_duration' => $warrantyPrice->warranty_duration,
                'package' => $warrantyPrice->package,
                'submitted_at' => Carbon::now(),
                'dealer_id' => 23,
                'max_claim' => $warrantyPrice->max_claim,
                'price' => $warrantyPrice->price,
                'mileage' => $request->mileage,
                'mileage_coverage' => $warrantyPrice->mileage_coverage,
                'quote_valid_till' => Carbon::now()->addDays(14),
                'status' => 'pending_acceptance',
            ]);

            // Upload Log Card File
            if($request->log_cards != null){
                foreach($request->log_cards as $log_card){
                    $ext = strtolower($log_card->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $log_card->store('warranty/log', 's3');
                    $document = WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $fileName,
                        'type' => 'log',
                        'url' => Storage::disk('s3')->url($path)
                    ]);
                }
            }

            // Upload Assesment Report File
            if($request->assessment_reports != null){
                foreach($request->assessment_reports as $assessment_report){
                    $ext = strtolower($assessment_report->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $assessment_report->store('warranty/assessment', 's3');
                    $document = WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $fileName,
                        'type' => 'assessment',
                        'url' => Storage::disk('s3')->url($path)
                    ]);
                }
            }

            $customer_vehicle = new CustomerVehicle;
            $customer_vehicle->customer_id = $customer->id;
            $customer_vehicle->vehicle_id = $vehicle->id;
            $customer_vehicle->save();

            $customer_warranty = new CustomerWarranty;
            $customer_warranty->customer_id = $customer->id;
            $customer_warranty->warranty_id = $warranty->id;
            $customer_warranty->save();

            DB::commit();

            $customer->notify(new BuyWarranty($warranty));
            $warranty = $customer->warranties()->with(['vehicle', 'proposer', 'insurer:id,code'])->where('warranties.id', $warranty->id)->first();
            return $this->success($warranty, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::debug($e->getMessage());
            DB::rollback();
            return response()->json(['message' => 'An error has occured.'], 422);
        }
    }

    /**
     * Enquiry a warranty,
     * with a vehicle model but without specific price
     */
    public function enquiry(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'registration_no' => 'required|regex:/^[A-Z]{1,5}\d{1,5}[A-Z0-9]$/',
            'make' => 'required',
            'model' => 'required',
            'type' => 'required|in:new,preowned',
            'fuel' => 'required|in:hybrid,non_hybrid',
            'mileage' => 'nullable',
            'manufacture_year' => 'required',
            'capacity' => 'required|numeric|not_in:0',
            'registration_date' => 'required|date_format:d/m/Y',
            'chassis_no' => 'required',
            'engine_no' => 'required',
            'start_date' => 'required|date_format:d/m/Y',
            //mimes:jpg,jpeg,png,pdf|max:100000
            'log_cards' => 'required|array',
            'assessment_reports' => 'nullable'
        ]);
        
        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        // If the car is pre owned, then need mileage and assesment report
        if ($request->type == 'preowned') {
            if(!isset($request->mileage)){
                return response()->json(['message' => 'The mileage field is required.'], 422);
            }
            if(!isset($request->assessment_reports)){
                return response()->json(['message' => 'The assessment reports field is required.'], 422);
            }
        }

        try {
            DB::beginTransaction();

            $customer = Auth::user();
            $package = Package::where('id', $request->package_id)->first();
            $car_exist = Vehicle::where('registration_no', $request->registration_no)->first();
            if($car_exist == null){ 
                $vehicle = new Vehicle;
            }else{
                $vehicle = Vehicle::where('registration_no', $request->registration_no)->first();
            }
            $vehicle->registration_no = $request->registration_no;
            $vehicle->make = $request->make;
            $vehicle->model = $request->model;
            $vehicle->type = $request->type;
            $vehicle->fuel = $request->fuel;
            $vehicle->mileage = $request->mileage;
            $vehicle->manufacture_year = $request->manufacture_year;
            $vehicle->registration_date = Carbon::createFromFormat('d/m/Y', $request->registration_date);
            $vehicle->chassis_no = $request->chassis_no;
            $vehicle->engine_no = $request->engine_no;
            $vehicle->nric_uen = $customer->nric_uen;
            $vehicle->save();

            $proposer = new Proposer;
            $proposer->nric_uen = $customer->nric_uen;
            $proposer->salutation = $customer->salutation;
            $proposer->name = $customer->name;
            $proposer->address = $customer->address;
            $proposer->email = $customer->email;
            $proposer->phone = $customer->phone;
            $proposer->save();

            // TBC when more insurers
            $insurer = Company::where('code', 'I-III002')->first();
            $warranty = Warranty::create([
                'vehicle_id' => $vehicle->id,
                'proposer_id' => $proposer->id,
                'insurer_id' => $insurer->id,
                'creator_id' => $customer->id, 
                'customer_id' => $customer->id,
                'dealer_id' => 23,
                'ref_no' => Str::random(10),
                'dealer_id' => 23,
                'start_date' => Carbon::createFromFormat('d/m/Y', $request->start_date),
                'warranty_duration' => $package->duration,
                'package' => $package->name,
                'submitted_at' => Carbon::now(),
                'mileage' => $request->mileage,
                'mileage_coverage' => $package->mileage_coverage,
                'quote_valid_till' => Carbon::now()->addDays(14),
                'status' => 'pending_enquiry',
            ]);

            // Upload Log Card File
            if($request->log_cards != null){
                foreach($request->log_cards as $log_card){
                    $fileName = time() . '.' . $log_card->getClientOriginalExtension();
                    $path = $log_card->store('warranty/log', 's3');
                    $document = WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $fileName,
                        'type' => 'log',
                        'url' => Storage::disk('s3')->url($path)
                    ]);
                }
            }

            // Upload Assesment Report File
            if($request->assessment_reports != null){
                foreach($request->assessment_reports as $assessment_report){
                    $fileName = time() . '.' . $assessment_report->getClientOriginalExtension();
                    $path = $assessment_report->store('warranty/assessment', 's3');
                    $document = WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $fileName,
                        'type' => 'assessment',
                        'url' => Storage::disk('s3')->url($path)
                    ]);
                }
            }

            $customer_vehicle = new CustomerVehicle;
            $customer_vehicle->customer_id = $customer->id;
            $customer_vehicle->vehicle_id = $vehicle->id;
            $customer_vehicle->save();

            $customer_warranty = new CustomerWarranty;
            $customer_warranty->customer_id = $customer->id;
            $customer_warranty->warranty_id = $warranty->id;
            $customer_warranty->save();
        
            DB::commit();

            $customer->notify(new EnquiryWarranty($warranty));
            $warranty = $customer->warranties()->with(['vehicle', 'proposer', 'insurer:id,code'])->where('warranties.id', $warranty->id)->first();
            return $this->success($warranty, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::debug($e->getMessage());
            DB::rollback();
            return response()->json(['message' => 'An error has occured.'], 422);
        }
    }
}
