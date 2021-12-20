<?php

namespace App\Http\Controllers\Mobile\Profile;

use Throwable;
use App\Models\Customer;
use App\Models\CustomerVehicle;
use App\Models\Motor;
use App\Models\Vehicle;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Traits\AttributeModifier;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Carbon\Carbon;
use DB;

class ProfileController extends Controller
{
    use ApiResponser, AttributeModifier;

    public function show(Request $request)
    {
        $customer = Auth::user();

        return $this->success($customer);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            // 'salutation' => 'nullable|in:Mr,Mrs',
            'name' => 'required|max:255',
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'required|date_format:d/m/Y',
            'nationality' => 'required|max:255', 
            'residential' => 'required|max:255',
            'occupation' => 'required|max:255',

            'phone' => 'required|numeric',
            'email' => 'required|email',
            'address' => 'required|max:255',

            'driving_license_class' => 'nullable|max:255',
            'driving_license_validity' => 'nullable|date_format:d/m/Y',

            'vehicles' => 'nullable|array'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }
      

        try {
            DB::beginTransaction();

            $customer = Auth::user();

            $phone_exist = Customer::where('id', '!=', $customer->id)->where('phone', '+65 ' . $request->phone)->first();
            if($phone_exist != null){ 
                return response()->json(['message' => 'The phone has already been taken.'], 422);
            }

            $email_exist = Customer::where('id', '!=', $customer->id)->where('email', $request->email)->first();
            if($email_exist != null){
                return response()->json(['message' => 'The email has already been taken.'], 422);
            }
            // Upload profile image if exists
            /*
            if ($request->hasFile('profile_photo')) {
                $image      = $request->file('profile_photo');
                $fileName   = time() . '.' . $image->getClientOriginalExtension();
                $path = $request->file('profile_photo')->storePubliclyAs(
                    'customers/' . $customer->id,
                    $fileName,
                    'public'
                );

                $attr['profile_photo'] = $path;
            }*/
            if($customer->salutation != 'Company'){
                $customer->salutation = $request->gender == 'M' ? 'Mr' : 'Ms';
            }
            $customer->name = $request->name;
            $customer->gender = $request->gender;
            $customer->date_of_birth = Carbon::createFromFormat('d/m/Y', $request->date_of_birth);
            
            $nationality = null;
            foreach(Motor::NATIONALITIES as $key => $data){
                if($request->nationality == $data){
                    $nationality = $key;
                }
            }
            $customer->nationality = $nationality;

            $residential = null;
            for($i = 0 ; $i < sizeof(Motor::RESIDENTIALS); $i++){
                if($request->residential == Motor::RESIDENTIALS[$i]){
                    $residential= $i;
                }
            }
            $customer->residential = $residential;

            $occupation = 134;
            for($i = 0 ; $i < sizeof(Motor::OCCUPATIONS); $i++){
                if($request->occupation == Motor::OCCUPATIONS[$i]){
                    $occupation = $i;
                }
            }
            $customer->occupation = $occupation;

            $customer->phone = "+65 " . $request->phone;
            $customer->email = $request->email;
            $customer->address = $request->address;

            $customer->driving_license_class = $request->driving_license_class;
            $customer->driving_license_validity = Carbon::createFromFormat('d/m/Y', $request->driving_license_validity);
            $customer->save();
  
    		$vehicles_ids = CustomerVehicle::where('customer_id', $customer->id)->get();
            $vehicle_map = [];
            $current_vehicle = [];
            $updated_vehicle =[];
            if(isset($vehicles_ids) && count($vehicles_ids)>0){
            	 foreach($vehicles_ids as $vehicle){
	                $vehicle_map[$vehicle->vehicle_id] = true;
	                array_push($current_vehicle,$vehicle->vehicle_id);
	            }
            }
           
            $current_vehicle = array_unique($current_vehicle);
            	if(isset($request->vehicles) && count($request->vehicles) >0){

	            	  foreach($request->vehicles as $vehicle){
	                        $car_exist = Vehicle::where('registration_no', $vehicle['registration_no'])->first();
	                        if($car_exist != null){ 
	                        	
	                            $temp_vehicle = Vehicle::where('id', $vehicle['id'])->first();
	                          	if(isset($temp_vehicle) && is_array($temp_vehicle) && count($temp_vehicle) > 0){
	                          		$temp_vehicle->registration_no = $vehicle['registration_no'];
		                            if(isset( $vehicle['type']) &&  $vehicle['type'] != ""){
		                            	$temp_vehicle->type = $vehicle['type'];
		                            }
		                            if(isset($vehicle['make']) && $vehicle['make'] != ""){
		                            	   $temp_vehicle->make = $vehicle['make'];
		                            }
		                         	if(isset($vehicle['model']) && $vehicle['model'] != ""){
		                         		 $temp_vehicle->model = $vehicle['model'];
		                         	}
		                           	if(isset($vehicle['chassis_no']) && $vehicle['chassis_no'] != ""){
		                         		 $temp_vehicle->chassis_no = $vehicle['chassis_no'];
		                         	}
		                         	if(isset($vehicle['engine_no']) && $vehicle['engine_no'] != ""){
		                         		 $temp_vehicle->engine_no = $vehicle['engine_no'];
		                         	}
		                          	if(isset($vehicle['manufacture_year']) && $vehicle['manufacture_year'] != ""){
		                         		 $temp_vehicle->manufacture_year = $vehicle['manufacture_year'];
		                         	}
		                          	if(isset($vehicle['registration_date']) && $vehicle['registration_date'] != ""){
		                         		     $temp_vehicle->registration_date = $vehicle['format_registration_date'] != '-' ? Carbon::createFromFormat('d/m/Y', $vehicle['format_registration_date']) : null;
		                         	}
		                        
		                            $temp_vehicle->save();
		                        	array_push($updated_vehicle,$vehicle['id']);
	                          	}else{
	                          		$temp_vehicle =  new Vehicle ;
		                            $temp_vehicle->registration_no = $vehicle['registration_no'];
		                         	if(isset( $vehicle['type']) &&  $vehicle['type'] != ""){
		                            	$temp_vehicle->type = $vehicle['type'];
		                            }
		                            if(isset($vehicle['make']) && $vehicle['make'] != ""){
		                            	   $temp_vehicle->make = $vehicle['make'];
		                            }
		                         	if(isset($vehicle['model']) && $vehicle['model'] != ""){
		                         		 $temp_vehicle->model = $vehicle['model'];
		                         	}
		                           	if(isset($vehicle['chassis_no']) && $vehicle['chassis_no'] != ""){
		                         		 $temp_vehicle->chassis_no = $vehicle['chassis_no'];
		                         	}
		                         	if(isset($vehicle['engine_no']) && $vehicle['engine_no'] != ""){
		                         		 $temp_vehicle->engine_no = $vehicle['engine_no'];
		                         	}
		                          	if(isset($vehicle['manufacture_year']) && $vehicle['manufacture_year'] != ""){
		                         		 $temp_vehicle->manufacture_year = $vehicle['manufacture_year'];
		                         	}
		                          	if(isset($vehicle['registration_date']) && $vehicle['registration_date'] != ""){
		                         		     $temp_vehicle->registration_date = $vehicle['format_registration_date'] != '-' ? Carbon::createFromFormat('d/m/Y', $vehicle['format_registration_date']) : null;
		                         	}
		                            $temp_vehicle->save();
		                            $vehicle[$key] = $temp_vehicle->id;
		                            $customer_vehicle = new CustomerVehicle;
		                            $customer_vehicle->customer_id = $customer->id;
		                            $customer_vehicle->vehicle_id = $temp_vehicle->id;
		                            $customer_vehicle->save();
		                            array_push($updated_vehicle,$temp_vehicle->id);
	                          	}
	                          

	                        }else{
	                        

	                            $temp_vehicle =  new Vehicle ;
	                            $temp_vehicle->registration_no = $vehicle['registration_no'];
	                         	if(isset( $vehicle['type']) &&  $vehicle['type'] != ""){
	                            	$temp_vehicle->type = $vehicle['type'];
	                            }
	                            if(isset($vehicle['make']) && $vehicle['make'] != ""){
	                            	   $temp_vehicle->make = $vehicle['make'];
	                            }
	                         	if(isset($vehicle['model']) && $vehicle['model'] != ""){
	                         		 $temp_vehicle->model = $vehicle['model'];
	                         	}
	                           	if(isset($vehicle['chassis_no']) && $vehicle['chassis_no'] != ""){
	                         		 $temp_vehicle->chassis_no = $vehicle['chassis_no'];
	                         	}
	                         	if(isset($vehicle['engine_no']) && $vehicle['engine_no'] != ""){
	                         		 $temp_vehicle->engine_no = $vehicle['engine_no'];
	                         	}
	                          	if(isset($vehicle['manufacture_year']) && $vehicle['manufacture_year'] != ""){
	                         		 $temp_vehicle->manufacture_year = $vehicle['manufacture_year'];
	                         	}
	                          	if(isset($vehicle['registration_date']) && $vehicle['registration_date'] != ""){
	                         		     $temp_vehicle->registration_date = $vehicle['format_registration_date'] != '-' ? Carbon::createFromFormat('d/m/Y', $vehicle['format_registration_date']) : null;
	                         	}
	                            $temp_vehicle->save();
	                            $vehicle[$key] = $temp_vehicle->id;
	                            $customer_vehicle = new CustomerVehicle;
	                            $customer_vehicle->customer_id = $customer->id;
	                            $customer_vehicle->vehicle_id = $temp_vehicle->id;
	                            $customer_vehicle->save();
	                            array_push($updated_vehicle,$temp_vehicle->id);
	                        }
	                    
	            }
            }
          
            if(isset($current_vehicle) && count($current_vehicle)>0){
        		foreach ($current_vehicle as $key => $value) {
	         		if(!in_array($value, $updated_vehicle)){
	         			CustomerVehicle::where('id', $value)->where('customer_id', $customer->id)->delete();
	         			Vehicle::where('id', $value)->delete();
	         		}
	         	}
            }
		         
         	
            
           
           
            DB::commit();
            return $this->success($customer);
        } catch (Throwable $e) {
            Log::error([
                'ProfileController:update',
                $request->all(),
                $e->getMessage()
            ]);
            DB::rollback();
            return $this->error($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

}
