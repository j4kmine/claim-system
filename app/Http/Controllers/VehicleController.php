<?php

namespace App\Http\Controllers;

use App\Models\CustomerVehicle;
use App\Models\Motor;
use App\Models\Vehicle;
use App\Models\MotorDriver;
use App\Models\Report;
use App\Models\Reports;
use App\Models\Service;
use App\Models\Warranty;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class VehicleController extends Controller
{
    use ApiResponser;

    public function index()
    {
        $user = Auth::user();
        $owner_vehicles = Vehicle::where('nric_uen', $user->nric_uen)->whereNotNull('registration_no')->groupby('registration_no')->get();
        $user_vehicles = Vehicle::with('customer_vehicle')->where('customer_vehicle.customer_id', $user->id)->whereNotNull('customer_vehicle.granted_by')->whereNotNull('vehicles.registration_no')->get();
         
        return response()->json(['owner_vehicles' => $owner_vehicles, 'user_vehicles' => $user_vehicles], 200);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle = Vehicle::with('customers')
            ->with('warranty')
            ->with('services')
            ->with('reports')
            ->with('claims')
            ->find($vehicle->id);

        return $this->success($vehicle);
    }

    public function vehiclesCustomer(Request $request, $customerId){
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        // DB::enableQueryLog();
        $query = Service::eloquentQuery($orderBy, $orderByDir, $searchValue)
        ->with('vehicle')
        ->whereCustomerId($customerId);
        $query->getQuery()->groups = [];

        $query = $query->orderBy('id', 'desc')->groupBy('vehicle_id')->paginate();

        return new DataTableCollectionResource($query);
    }

    public function customerVehicle(Request $request){
        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        $searchValue = $request->input('search');
        
        $vehicle = Vehicle::eloquentQuery($sortBy, $orderBy, null, []);
        $vehicle = $vehicle->with('customers','warranty','motor','services');
        $vehicle = $vehicle->paginate($length);
        return new DataTableCollectionResource($vehicle);
    }

    public function vehiclesAccess(Request $request, $vehicleId){
        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        
        $vehicle = CustomerVehicle::eloquentQuery($sortBy, $orderBy, null, [
            'customer'
        ]);
        $vehicle->whereVehicleId($vehicleId);
        $vehicle = $vehicle->paginate($length);
        return new DataTableCollectionResource($vehicle);
    }

    public function vehicleInsurance(Request $request, $vehicleId){
        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        
        $vehicle = Motor::eloquentQuery($sortBy, $orderBy, null, []);
        $vehicle->whereVehicleId($vehicleId);
        $vehicle->with('insurer');
        $vehicle = $vehicle->paginate($length);
        return new DataTableCollectionResource($vehicle);
    }

    public function vehicleWarranty(Request $request, $vehicleId){
        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        
        $vehicle = Warranty::eloquentQuery($sortBy, $orderBy, null, []);
        $vehicle->whereVehicleId($vehicleId);
        $vehicle->with('insurer');
        $vehicle = $vehicle->paginate($length);
        return new DataTableCollectionResource($vehicle);
    }

    public function vehicleServiceHistory(Request $request, $vehicleId){
        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        
        $vehicle = Service::eloquentQuery($sortBy, $orderBy, null, []);
        $vehicle->whereVehicleId($vehicleId);
        $vehicle->with('workshop');
        $vehicle = $vehicle->paginate($length);
        return new DataTableCollectionResource($vehicle);
    }

    public function vehicleReportingHistory(Request $request, $vehicleId){
        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        
        $vehicle = Reports::eloquentQuery($sortBy, $orderBy, null, []);
        $vehicle->whereVehicleId($vehicleId);
        $vehicle->with('workshop', 'vehicle');
        $vehicle = $vehicle->paginate($length);
        return new DataTableCollectionResource($vehicle);
    }

    public function vehicleClaimsHistory(Request $request, $vehicleId){
        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        
        $vehicle = Report::eloquentQuery($sortBy, $orderBy, null, []);
        $vehicle->whereVehicleId($vehicleId);
        $vehicle->with('workshop', 'vehicle', 'company');
        $vehicle = $vehicle->paginate($length);
        return new DataTableCollectionResource($vehicle);
    }
}
