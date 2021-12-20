<?php

namespace App\Http\Controllers\Mobile\Motor;

use Throwable;
use App\Models\Driver;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\CustomerVehicle;
use App\Models\CustomerMotor;
use App\Models\Motor;
use App\Models\MotorDocument;
use App\Models\MotorDriver;
use App\Models\Proposer;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\BadResponseException;
use Log;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Constant;
use Storage;

class MotorController extends Controller
{
    use ApiResponser;
    
    public function makes(Request $request){
        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEManufacturer/949539";
        $response = insurance_api($endpoint, ['Usage' => 'Own Use', 'Shown in B2B' => 'Y']);
        $arr = json_decode($response->getBody()->getContents(), true);
        $makes = [];
        foreach($arr as $make){
            $makes[]['make'] = $make["Id"]; 
        }
        return $this->success($makes);   
    }

    public function models(Request $request){
        $valid = Validator::make($request->all(), [
            'make' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }
        
        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEModel/949539";
        $response = insurance_api($endpoint, ['Make' => $request->make, 'Usage' => 'Own Use', 'Shown in B2B' => 'Y']);
        $arr = json_decode($response->getBody()->getContents(), true);
        $models = [];
        $i = 0;
        foreach($arr as $model){
            $models[$i]['model'] = $model["Id"];
            $i++;
        }
        return $this->success($models);
    }

    public function searchCar(Request $request){

        
        $valid = Validator::make($request->all(), [
            'make' => 'required',
            'model' => 'required'
        ]);

        if ($valid->fails()) {
            return $this->success(['body_type' => $body_type, 'capacity' => $capacity]);
        }
        
        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEVehBodyTypeCd/949539";
        $response = insurance_api($endpoint, ['Make' => $request->make, 'Model' => $request->model, 'Shown in B2B' => 'Y']);
        $body_type = json_decode($response->getBody()->getContents(), true);
        if(isset($body_type[0])){
            $body_type = $body_type[0]['Id'];
        } else {
            $body_type = "";
        }
        
        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEHorsepower/949539";
        $response = insurance_api($endpoint, ['Make' => $request->make, 'Model' => $request->model, 'Shown in B2B' => 'Y']);
        $capacity = json_decode($response->getBody()->getContents(), true);
        if(isset($capacity[0])){
            $capacity = $capacity[0]['Id'];
        } else {
            $capacity = "";
        }
        
        return $this->success(['body_type' => $body_type, 'capacity' => $capacity]);   
    }

    
    public function enquiry(Request $request){
        $valid = Validator::make($request->all(), [
            'usage' => 'required|in:phv,private',
            'start_date' => 'required|date_format:d/m/Y',
            'expiry_date' => 'required|date_format:d/m/Y',

            'registration_no' => 'nullable|regex:/^[A-Z]{1,5}\d{1,5}[A-Z0-9]$/',
            'type' => 'required|in:new,preowned',
            'model' => 'required|max:255',
            'make' => 'required|max:255',
            'engine_no' => 'required|max:255',
            'chassis_no' => 'required|max:255',
            'body_type' => 'required|max:255',
            'off_peak' => 'required|in:true,false',
            'modified' => 'required|in:true,false',
            'manufacture_year' => 'required|numeric',
            'capacity' => 'required|numeric',
            'seating_capacity' => 'required|numeric',
            
            'no_of_accidents' => 'required|numeric',
            'total_claim' => 'required|numeric',
            'ncd' => 'required|in:0%,10%,20%,30%,40%,50%',
            'serious_offence' => 'required|in:true,false',
            'physical_disable' => 'required|in:true,false',
            'refused' => 'required|in:true,false',
            'terminated' => 'required|in:true,false',
            
            'additional_drivers' => 'nullable|array',
            'log_cards' => 'nullable|array'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

       

        try {
            
            $request->ncd = str_replace("%", "", $request->ncd);
            DB::beginTransaction();

            $customer = Auth::user();
            
            $motor = new Motor;
            $car_exist = Vehicle::where('registration_no', $request->registration_no)->first();
            if($car_exist == null){ 
                $vehicle = new Vehicle;
            }else{
                $vehicle = Vehicle::where('registration_no', $request->registration_no)->first();
            }
            $proposer = new Proposer;
            $driver = new Driver;

            $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEMakeModelPoint/949539";
            $response = insurance_api($endpoint, ['Make' => $request->make, 'Model' => $request->model, 'Shown in B2B' => 'Y']);
            $point = json_decode($response->getBody()->getContents(), true);
            if(isset($point[0])){
                $point = $point[0]['Id'];
            } else {
                $point = "";
            }

            $motor->usage = $request->usage;
            $motor->point = $point;
            $motor->dealer_id = 23;
            $motor->policyholder_driving = true;
            $motor->start_date = Carbon::createFromFormat('d/m/Y', $request->start_date);
            $motor->expiry_date = Carbon::createFromFormat('d/m/Y', $request->expiry_date);
            $motor->status = 'pending_enquiry';

            // TODO :: Consider asking in mobile app
            $proposer = new Proposer;
            $proposer->nric_type = $customer->nric_type;
            $proposer->nric_uen = $customer->nric_uen;
            $proposer->salutation = $customer->salutation;
            $proposer->name = $customer->name;
            $proposer->nationality = $customer->nationality;
            $proposer->residential = $customer->residential;
            $proposer->gender = $customer->gender;
            $proposer->address = $customer->address;
            $proposer->occupation = $customer->occupation;
            $proposer->date_of_birth = $customer->date_of_birth;
            $proposer->email = $customer->email;
            $proposer->phone = $customer->phone;
            $proposer->postal_code = $customer->postal_code;
            $proposer->save();
            
            $driver->nric_type = $customer->nric_type;
            $driver->nric = $customer->nric_uen;
            $driver->name = $customer->name;
            $driver->nationality = $customer->nationality;
            $driver->residential = $customer->residential;
            $driver->gender = $customer->gender;
            $driver->occupations = $customer->occupation_type;
            $driver->dob = $customer->date_of_birth;
            $driver->license_pass_date = $customer->driving_license_validity;
            $driver->no_of_accidents = $request->no_of_accidents;
            $driver->total_claim = $request->total_claim;
            $driver->ncd = $request->ncd;
            $driver->serious_offence = $request->serious_offence == 'true' ? 1 : 0;
            $driver->physical_disable = $request->physical_disable == 'true' ? 1 : 0;
            $driver->refused = $request->refused == 'true' ? 1 : 0;
            $driver->terminated = $request->terminated == 'true' ? 1 : 0;
            $driver->save();
            
            $vehicle->registration_no = $request->registration_no;
            $vehicle->type = $request->type;
            $vehicle->model = $request->model;
            $vehicle->make = $request->make;
            $vehicle->nric_uen = $request->nric_uen;
            $vehicle->engine_no = $request->engine_no;
            $vehicle->chassis_no = $request->chassis_no;
            $vehicle->off_peak = $request->off_peak == 'true' ? 1 : 0;
            $vehicle->body_type = $request->body_type;
            $vehicle->modified = $request->modified == 'true' ? 1 : 0;
            $vehicle->modification_remarks = $request->modification_remarks;
            $vehicle->manufacture_year = $request->manufacture_year;
            $vehicle->capacity = $request->capacity;
            $vehicle->seating_capacity = $request->seating_capacity;
            $vehicle->save();
            
            if($point != ''){
                $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/orchestration/dispatch/AP98_newbiz_Quoting";
                $arr = Constant::QUOTE_POLICY;
                $arr['EffectiveDate'] = Carbon::parse($motor->start_date)->format('Y-m-d') . "T00:00:00";
                $arr['ExpiryDate'] = Carbon::parse($motor->expiry_date)->format('Y-m-d') . "T00:00:00";
                $response = insurance_api($endpoint, $arr);
                $arr = json_decode($response->getBody()->getContents(), true);
                
                $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap88/policy/sp/camelapi/AP98_newbiz_Save";
                $arr = array_merge(Constant::SAVE_POLICY, $arr['Model']);
                $arr['PartyName'] = $proposer->name;

                $arr["PolicyCustomerList"][0]["BusinessObjectId"] = 1000000290;
                $arr["PolicyCustomerList"][0]["@type"] = "PolicyCustomer-PolicyCustomer";
                $arr["PolicyCustomerList"][0]["IsPolicyHolder"] = $motor->policyholder_driving ? "Y" : "N";    

                $arr["PolicyCustomerList"][0]["Customer"]["BusinessObjectId"] = 1000000244;
                $arr["PolicyCustomerList"][0]["Customer"]["@type"] = "PartyIndividualCustomer-PartyIndividualCustomer";
                $arr["PolicyCustomerList"][0]["Customer"]["CitizenshipType"] = $proposer->residential;
                $arr["PolicyCustomerList"][0]["Customer"]["DateOfBirth"] = $proposer->date_of_birth . "T00:00:00";
                $arr["PolicyCustomerList"][0]["Customer"]["Gender"] = $proposer->gender;
                $arr["PolicyCustomerList"][0]["Customer"]["IdNumber"] = $proposer->nric_uen;
                $arr["PolicyCustomerList"][0]["Customer"]["IdType"] = $proposer->nric_type;
                $arr["PolicyCustomerList"][0]["Customer"]["Nationality"] = $proposer->nationality;
                $arr["PolicyCustomerList"][0]["Customer"]["Occupation"] = $proposer->occupation;
                $arr["PolicyCustomerList"][0]["Customer"]["Title"] = $proposer->salutation;
                $arr["PolicyCustomerList"][0]["Customer"]["PartyName"] = $proposer->name;
                
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["BusinessObjectId"] = 949634;
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["@type"] = "PolicyDriver-DRIVER";
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["DriverRelationshipToApplicantCd"] = "Y";
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["FullName"] = $driver->name;
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["Gender"] = $driver->gender;
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["IDReferenceType"] =  $driver->nric_type;
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["Citizenship"] = $driver->nationality;
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["CitizenshipType"] = $driver->residential;
                $arr["PolicyLobList"][0]["PolicyDriverList"][0]["IDReferenceNo"] = $driver->nric;
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['QuestionText_1'] = $driver->serious_offence ? "Y" : "N";
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['QuestionText_2'] = $driver->physical_disable ? "Y" : "N";
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['QuestionText_3'] = $driver->refused ? "Y" : "N";
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['QuestionText_4'] = $driver->terminated ? "Y" : "N";
                // TODO
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['Explanation_1'] = null;
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['Explanation_2'] = null;
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['Explanation_3'] = null;
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['Explanation_4'] = null;
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['BirthDate'] = $driver->dob . "T00:00:00"; # [p]
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['LicensedDt'] = $driver->license_pass_date . "T00:00:00"; # [p]
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['OccupationType'] = $driver->occupations; # [p]
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['NumLosses'] = $driver->no_of_accidents;
                $arr['PolicyLobList'][0]['PolicyDriverList'][0]['TotalPaidLossAmt'] = $driver->total_claim;
                
                if(isset($request->additional_drivers)){
                    $i = 2;
                    foreach($request->additional_drivers as $additional_driver){
                        $seq = sprintf("%03d", $i);
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]["BusinessObjectId"] = 949634;
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]["@type"] = "PolicyDriver-DRIVER";
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['SequenceNumber'] = $seq;
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['FullName'] = $additional_driver['name'];
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['Citizenship'] = $additional_driver['nationality'];
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['Gender'] = $additional_driver['gender'];
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['IDReferenceType'] = $additional_driver['nric_type'];
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['IDReferenceNo'] = $additional_driver['nric'];  
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['CitizenshipType'] = $additional_driver['residential'];
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['NumLosses'] = $additional_driver['no_of_accidents'];
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['BirthDate'] = Carbon::createFromFormat('d/m/Y', $additional_driver['dob']) . "T00:00:00";
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['LicensedDt'] = Carbon::createFromFormat('d/m/Y', $additional_driver['license_pass_date']) . "T00:00:00";
                        $arr['PolicyLobList'][0]['PolicyDriverList'][$i-1]['TotalPaidLossAmt'] = $additional_driver['total_claim'];
                        $i += 1;
                    }
                }

                // Consider Organization
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["BusinessObjectId"] = 949598;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["@type"] = "PolicyRisk-VEHICLE";
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["NewVehInd"] = $vehicle->type == 'new' ? "Y" : "N";
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["RegistrationId"] = $vehicle->registration_no;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["ChassisSerialNumber"] = $vehicle->chassis_no;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["EngineSerialNumber"] = $vehicle->engine_no;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["MakeModelPoint"] = $motor->point;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["Manufacturer"] = $vehicle->make;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["Model"] = $vehicle->model;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["VehBodyTypeCd"] = $vehicle->body_type;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["Horsepower"] = $vehicle->capacity;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["NumPassengers"] = $vehicle->seating_capacity;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["ModelYear"] = $vehicle->manufacture_year;
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]["NCDRate"] = $driver->ncd / 100.0;

                $arr['PolicyLobList'][0]['PolicyRiskList'][0]['QuestionText_5'] = $vehicle->off_peak ? "Y" : "N";
                $arr['PolicyLobList'][0]['PolicyRiskList'][0]['QuestionText_7'] = $vehicle->modified ? "Y" : "N";
                $response = insurance_api($endpoint, $arr);
                $arr = json_decode($response->getBody()->getContents(), true);
                
                try {
                    $endpoint = env("INSURANCE_URL") . "/restlet/v1/pa/ap88/policy/sp/camelapi/AP88_newbiz_CalculatePremium";
                    $response = insurance_api($endpoint, $arr['Model']);
                    $arr = json_decode($response->getBody()->getContents(), true);
                    $motor->ci_no = $arr['Model']['QuotationNo'];
                    $motor->policy_no = $arr['Model']['@pk'];
                    $motor->price = $arr['Model']['PayablePremium'];
                    $motor->status = 'pending_acceptance';
                } catch (BadResponseException $ex) {
                    Log::debug($ex);
                }
            }
            
            $motor->ref_no = Str::random(10);
            $motor->insurer_id = Company::where('type', 'insurer')->first()->id;
            $motor->creator_id = Auth::user()->id;
            $motor->vehicle_id = $vehicle->id;
            $motor->proposer_id = $proposer->id;
            $motor->driver_id = $driver->id;
            $motor->submitted_at = Carbon::now();
            $motor->quote_valid_till = Carbon::now()->addDays(14);
            $motor->save();


            if(isset($request->additional_drivers)){
                foreach($request->additional_drivers as $additional_driver){
                   
                    $additional_driver_model = new Driver;
                    $additional_driver_model->name = $additional_driver['name'];
                    
           
                    
                    if(isset($additional_driver['nric_type']) && $additional_driver['nric_type'] != ""){
                        if( $additional_driver['nric_type'] == "passport" || $additional_driver['nric_type'] == "birth certificate" || $additional_driver['nric_type'] == "others"){
                             $additional_driver_model->nric_type = array_keys(Motor::NRIC_TYPES,ucwords($additional_driver['nric_type']))[0];
                        }else{
                            $additional_driver_model->nric_type = array_keys(Motor::NRIC_TYPES,strtoupper($additional_driver['nric_type']))[0];
                        }
                        
                    }else{
                         $additional_driver_model->nric_type = "";
                    }
                   
                    $additional_driver_model->nric = $additional_driver['nric'];
                    if(isset($additional_driver['nationality']) && $additional_driver['nationality'] != ""){
                         $additional_driver_model->nationality = array_keys(Motor::NATIONALITIES,$additional_driver['nationality'])[0];
                    }else{
                           $additional_driver_model->nationality = "";
                    }
                    if(isset($additional_driver['residential']) && $additional_driver['residential'] != ""){
                        $additional_driver_model->residential = array_keys(Motor::RESIDENTIALS,$additional_driver['residential'])[0];
                    }else{
                        $additional_driver_model->residential = "";
                    }
                    
                    $additional_driver_model->gender = $additional_driver['gender'];
                    $additional_driver_model->dob = Carbon::createFromFormat('d/m/Y', $additional_driver['dob']);
                    $additional_driver_model->license_pass_date = Carbon::createFromFormat('d/m/Y', $additional_driver['license_pass_date']);
                    $additional_driver_model->no_of_accidents = $additional_driver['no_of_accidents'];
                    $additional_driver_model->total_claim = $additional_driver['total_claim'];
                    $additional_driver_model->save();
                    $motor_driver = new MotorDriver;
                    $motor_driver->motor_id = $motor->id;
                    $motor_driver->driver_id = $additional_driver_model->id;
                    $motor_driver->save();
                }
            }
         
            $customer_vehicle = new CustomerVehicle;
            $customer_vehicle->customer_id = $customer->id;
            $customer_vehicle->vehicle_id = $vehicle->id;
            $customer_vehicle->save();

            $customer_motor = new CustomerMotor;
            $customer_motor->customer_id = $customer->id;
            $customer_motor->motor_id = $motor->id;
            $customer_motor->save();


            DB::commit();

            $motor = $customer->motors()->with(['drivers', 'vehicle', 'insurer:id,code'])->where('motors.id', $motor->id)->first();
            return $this->success($motor, Response::HTTP_CREATED);
        } catch (BadResponseException $ex) {
            Log::debug($ex->getResponse()->getBody());
            DB::rollback();
            return response()->json(['message' => "Failed to get insurance quote."], 422); 
        } catch (\Exception $ex) {
            $response = $ex->getMessage();
            Log::debug($response . " " . $ex->getLine());
            DB::rollback();
            return response()->json(['message' => "Failed to get insurance quote."], 422); 
        }
    }

    public function index(Request $request)
    {
        $customer = Auth::user();
        $query = $customer->motors()->with(['drivers', 'vehicle', 'insurer:id,code'])->orderBy('motors.id', 'desc');
        if($request->status == 'pending'){
            $query = $query->where('motors.status', '!=', 'draft')->where('motors.status', '!=', 'completed');
        }
        if($request->limit != null){
            $query = $query->limit($request->limit);
        }
        return $this->success($query->get());
    }

    public function show(Request $request, Motor $motor)
    {
        $customer = Auth::user();
        $motor = $customer->motors()->with(['drivers', 'vehicle', 'documents', 'insurer:id,code'])->where('motors.id', $motor->id)->first();
        return $this->success($motor);
    }
}
