<?php

namespace App\Http\Controllers\Mobile\Vehicle;

use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\CustomerVehicle;
use Illuminate\Support\Str;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Log;
use Validator;

class VehicleAccessController extends Controller
{
    use ApiResponser;

    public function index(Request $request, Vehicle $vehicle)
    {
        $customer = Auth::user();

        $vehicles = $customer->vehicles()->where('registration_no', $vehicle->registration_no)->get();

        // Get all access based on the vehicles
        $owners = [];
        $granted = [];
        $inserted = [];
        $first = true;
        $is_owner = false;

        foreach($vehicles as $vehicle){
            $accesses = $vehicle->customers;

            // Check either the the owner of the vehicles or giving access to another customer
            foreach ($accesses as $access) {
                if(!isset($inserted[$access->id])){
                    if ($first) {
                        $is_owner = $access->id == $customer->id ? true : $is_owner;
                        array_push($owners, $access);
                    } else {
                        array_push($granted, $access);
                    }
                    $first = false;
                    $inserted[$access->id] = true;
                }
            }
        }

        return $this->success([
            'owners' => $owners,
            'users' => $granted,
            'is_owner' => $is_owner
        ]);
    }

    public function store(Request $request, Vehicle $vehicle)
    {
        // Check authorization
        $valid = Validator::make($request->all(), [
            'nric' => 'required|exists:customers,nric_uen'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        $customer = Auth::user();
        $vehicles = $customer->vehicles()->where('registration_no', $vehicle->registration_no)->get();
        $create_customer = Customer::where('nric_uen', $request->nric)->first();
        if($customer->id == $create_customer->id){
            return response()->json(['message' => 'Cannot create yourself.'], 422);
        }

        $is_owner = false;
        $first = true;
        foreach($vehicles as $vehicle){
            $accesses = $vehicle->customers;
            foreach ($accesses as $access) {
                // Only owner can destroy
                if($first){
                    $is_owner = $access->id == $customer->id;
                    $first = false;
                }

                if($is_owner){
                    CustomerVehicle::firstOrCreate([
                        'customer_id' => $create_customer->id,
                        'vehicle_id' => $vehicle->id,
                        'granted_by' => $customer->id
                    ]);
                } else {
                    return response()->json(['message' => 'Unauthorized'], 401);
                }
            }
        }

        return $this->success(['message'=>'Access has been created.']);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        // Check authorization
        $this->authorize('update', $vehicle);

        $attr = $request->validate([
            'name' => 'required',
            'nric' => 'required|exists:customers,nric_uen',
            'phone' => 'required'
        ]);

        $customer = Customer::where('nric_uen', $attr['nric'])->first();

        // If the relations doesnt exists, return error
        if (!$customer->vehicles->contains($vehicle->id)) {
            return $this->error(
                'Selected nric doesnt have the access',
                Response::HTTP_BAD_REQUEST
            );
        }

        $customer->update([
            'name' => $attr['name'],
            'phone' => $attr['phone']
        ]);

        $customer->vehicles()->attach($vehicle, [
            'granted_by' => Auth::user()->id
        ]);

        return $this->success($customer->fresh()->vehicles);
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        $valid = Validator::make($request->all(), [
            'nric' => 'required|exists:customers,nric_uen'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        $customer = Auth::user();
        $vehicles = $customer->vehicles()->where('registration_no', $vehicle->registration_no)->get();
        $delete_customer = Customer::where('nric_uen', $request->nric)->first();
        if($customer->id == $delete_customer->id){
            return response()->json(['message' => 'Cannot delete yourself.'], 422);
        }
        $is_owner = false;
        $first = true;
        foreach($vehicles as $vehicle){
            $accesses = $vehicle->customers;
            foreach ($accesses as $access) {
                // Only owner can destroy
                if($first){
                    $is_owner = $access->id == $customer->id;
                    $first = false;
                }

                if($is_owner){
                    CustomerVehicle::where('customer_id', $delete_customer->id)->where('vehicle_id', $vehicle->id)->delete();
                } else {
                    return response()->json(['message'=>'Unauthorized'], 401);
                }
            }
        }

        return $this->success(['message'=>'Access has been revoked.']);
    }
}
