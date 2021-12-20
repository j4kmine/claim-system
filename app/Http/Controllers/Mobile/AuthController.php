<?php

namespace App\Http\Controllers\Mobile;

use App\Models\Customer;
use Illuminate\Http\Request;
use Validator;
use Log;
use App\Http\Controllers\Controller;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Carbon\Carbon;
use App\Models\CustomerVehicle;
use App\Models\Vehicle;
use App\Models\Motor;
class AuthController extends Controller
{
    // For Companies
    public function register(Request $request){
        $valid = Validator::make($request->all(), [
            'is_company' => 'required|boolean',
            'name' => 'required|max:255',
            'email' => 'required|email|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/|unique:customers,email|max:255',
            'phone' => 'required|regex:/^(?:\+65)?[\s\d][689][0-9]{7}$/|unique:customers,phone',
            'nric_uen' => 'required|regex:/(^[STFG]\d{7}[A-Z]$)/|unique:customers,nric_uen', // regex:/(^[STFG]\d{7}[A-Z]$)/ TODO::company regex
            'date_of_birth' => 'required|date_format:d/m/Y'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }
        $nationality = null;
        foreach(Motor::NATIONALITIES as $key => $data){
            if($request->nationality == $data){
                $nationality = $key;
            }
        }
        $residential = null;
        for($i = 0 ; $i < sizeof(Motor::RESIDENTIALS); $i++){
            if($request->residential == Motor::RESIDENTIALS[$i]){
                $residential= $i;
            }
        }
        $occupation = 134;
        for($i = 0 ; $i < sizeof(Motor::OCCUPATIONS); $i++){
            if($request->occupation == Motor::OCCUPATIONS[$i]){
                $occupation = $i;
            }
        }
        // TODO :: Add customer vehicle pivot
        if(isset($request->driving_license_validity) && $request->driving_license_validity != ""){
              $user = Customer::create([
                'salutation' => $request->is_company ? 'Company' : 'Mr',
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => Carbon::createFromFormat('d/m/Y', $request->date_of_birth),
                'nric_uen' => $request->nric_uen,
                'address' => $request->address,
                'gender' => $request->gender,
                'nationality' => $nationality,
                'residential' => $residential,
                'occupation' => $occupation,
                'driving_license_class' => $request->driving_license_class,
                'driving_license_validity' => Carbon::createFromFormat('d/m/Y',$request->driving_license_validity),
                'password' => bcrypt('carfren!234'),
                'status' => 'active'
            ]);
        }else{
              $user = Customer::create([
                'salutation' => $request->is_company ? 'Company' : 'Mr',
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => Carbon::createFromFormat('d/m/Y', $request->date_of_birth),
                'nric_uen' => $request->nric_uen,
                'address' => $request->address,
                'gender' => $request->gender,
                'nationality' => $nationality,
                'residential' => $residential,
                'occupation' => $occupation,
                'driving_license_class' => $request->driving_license_class,
                'password' => bcrypt('carfren!234'),
                'status' => 'active'
            ]);
        }
      
        if(isset($request->vehicles) && $request->vehicles != null){
        		
        	foreach ($request->vehicles as $key => $value) {
        
        		 	$vehicle = new Vehicle;
		         	$vehicle->registration_no = $value['registration_no'];
		            $vehicle->make = $value['make'];
		            $vehicle->model = $value['model'];
		            $vehicle->category = $value['category'];
		            $vehicle->capacity = $value['seating_capacity'];
		            $vehicle->type = $value['type'];
		            $vehicle->fuel = $value['fuel'];
		            $vehicle->mileage = $value['mileage'];
		            $vehicle->manufacture_year = $value['manufacture_year'];
		            if($value['registration_date'] != null){
		            	$vehicle->registration_date = Carbon::createFromFormat('d/m/Y', $value['registration_date']);
		            }
		           
		            $vehicle->chassis_no = $value['chassis_no'];
		            $vehicle->engine_no = $value['engine_no'];
		            $vehicle->nric_uen = $user->nric_uen;
		            $vehicle->save();


 					$customer_vehicle = new CustomerVehicle;
		            $customer_vehicle->customer_id = $user->id;
		            $customer_vehicle->vehicle_id = $vehicle->id;
		            $customer_vehicle->save();
        	}
        }
        $customer = Customer::where('nric_uen', $request->nric_uen)->where('phone', $request->phone)->first();
         return response()->json(['access_token' => $customer->createToken('Personal Access Token')->plainTextToken], 200);
    }


    public function login(Request $request){
        $valid = Validator::make($request->all(), [
            'firebase_token' => 'required',
            'nric_uen' => 'required|exists:customers,nric_uen',
            'phone' => 'required|exists:customers,phone',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        $auth = Firebase::auth();
        try{
            $verifiedIdToken = $auth->verifyIdToken($request->firebase_token);
            $uid = $verifiedIdToken->claims()->get('sub');
            $user = $auth->getUser($uid);
            if($user->phoneNumber != str_replace(" " , "" , $request->phone)){
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
            $customer = Customer::where('nric_uen', $request->nric_uen)->where('phone', $request->phone)->where('salutation', 'Company')->first();
            if ( !$customer || $customer->status == 'inactive' ) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            } 
        } catch(\Exception $e){
            Log::debug($e->getMessage());
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        
        return response()->json(['access_token' => $customer->createToken('Personal Access Token')->plainTextToken], 200);
    }

    public function check(Request $request){
        $valid = Validator::make($request->all(), [
            'nric_uen' => 'required',
            'phone' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }
        if(isset($request->phone) && $request->phone != null){
            $request->phone = str_replace("%20","  ", $request->phone);
            if(isset($request->phone[0]) && $request->phone[0] != "+"){
                $request->phone = "+". $request->phone;
            } 
        }
        $customer = Customer::where('nric_uen', $request->nric_uen)->where('phone', $request->phone)->where('salutation', 'Company')->first();
        if ( !$customer || $customer->status == 'inactive') {
            return response()->json(['message' => 'Wrong NRIC or phone.'], 422);
        } else {
            return response()->json(['message' => 'Authorized.'], 200);
        }
    }

}