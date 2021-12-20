<?php

namespace App\Http\Controllers\Mobile\Vehicle;

use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Motor;
use App\Models\MotorDocument;
use App\Models\Warranty;
use App\Models\WarrantyDocument;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $customer = Auth::user();

        $vehicles = $customer->vehicles()->orderBy('created_at', 'DESC')->groupBy('registration_no')->get();

        return $this->success($vehicles);
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        $customer = Auth::user();
        
        $vehicles = $customer->vehicles()->where('registration_no', $vehicle->registration_no)->get();
        $output = $vehicles[sizeof($vehicles) - 1]->toArray();
        $output['warranty_validity'] = '-';
        $output['motor_validity'] = '-';
        $output['last_servicing'] = '-';
        foreach($vehicles as $vehicle){
            $warranty = $vehicle->warranty;
            if ($warranty != null)
                $output['warranty_validity'] = $warranty->format_start_date;
            $motor = $vehicle->motor;
            if ($motor != null)
                $output['motor_validity'] = $motor->format_start_date;
        }
 
        return $this->success($output);
    }

    public function summary(Request $request)
    {
        $customer = Auth::user();

        $ownedCars = $customer->ownedVehicles()->orderBy('created_at', 'DESC')->groupBy('registration_no')->get();
        $grantedVehicles = $customer->grantedVehicles;

        return $this->success([
            'owned' => $ownedCars,
            'granted_access' => $grantedVehicles
        ]);
    }

    public function warranties(Request $request, Vehicle $vehicle)
    {
         $customer = Auth::user();

        $merge = $customer->vehicles()->where('registration_no', $vehicle->registration_no)->first();
        $id = $merge->id;
        $doc = [];
        $motor = Warranty::where('vehicle_id', $id)->get();
        if(isset($motor) && count($motor) >0){
          foreach ($motor as $key => $value) {
            $motorDoc = WarrantyDocument::where('warranty_id', $value->id)->get()->toArray();
            for($i = 0 ; $i < sizeof($motorDoc); $i++){
                $motorDoc[$i]['policy_no'] = $value->ci_no;
                $motorDoc[$i]['start_date'] = $value->format_start_date;
                $motorDoc[$i]['end_date'] = $value->end_date;
              
            }
            $doc = array_merge($motorDoc, $doc);
       
          }

         
        }
        $return = array();
        if(isset($doc) && count($doc)>0){
            foreach ($doc as $key => $value) {
               if($value['type'] == "warranty" || $value['type'] == "ci"){
                    array_push($return, $value);
               }
            }
        }
      
 
   
        return $this->success($return);
    }

    public function motors(Request $request, Vehicle $vehicle)
    {
        $customer = Auth::user();

        $merge = $customer->vehicles()->where('registration_no', $vehicle->registration_no)->first();
        $id = $merge->id;
        $doc = [];
        $motor = Motor::where('vehicle_id', $id)->get();
        if(isset($motor) && count($motor) >0){
          foreach ($motor as $key => $value) {
            $motorDoc = MotorDocument::where('motor_id', $value->id)->get()->toArray();
            for($i = 0 ; $i < sizeof($motorDoc); $i++){
                $motorDoc[$i]['policy_no'] = $value->ci_no;
                $motorDoc[$i]['start_date'] = $value->format_start_date;
                $motorDoc[$i]['end_date'] = $value->format_expiry_date;
              
            }
            $doc = array_merge($motorDoc, $doc);
       
          }

         
        }

   
        return $this->success($doc);
    }


}
