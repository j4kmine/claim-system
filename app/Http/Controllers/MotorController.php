<?php

namespace App\Http\Controllers;

use App\Exports\MotorExport;
use Illuminate\Http\Request;
use Validator;
use Storage;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerVehicle;
use App\Models\Driver;
use App\Models\Proposer;
use App\Models\Motor;
use App\Models\MotorDriver;
use App\Models\MotorDocument;
use App\Models\MotorActionLog;
use App\Exports\ReportsExport;
use GuzzleHttp\Exception\BadResponseException;
use App\Jobs\GenerateDocument;
use App\Models\CustomerMotor;
use Auth;
use DB;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Illuminate\Support\Str;
use mikehaertl\pdftk\Pdf;
use Carbon\Carbon;
use Log;
use Excel;
use DomPDF;
use Constant;



class MotorController extends Controller
{

    private function permissionQuery($query)
    {
        if (Auth::user()->category == 'dealer') {
            $query = $query->where('motors.dealer_id', Auth::user()->company_id);
        } else if (Auth::user()->category == 'insurer') {
            $query = $query->where('motors.insurer_id', Auth::user()->company_id);
        }
        
        return $query;
    }

    public function activeMotors(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Motor::eloquentQuery($orderBy, $orderByDir, null, [
            "vehicle", "dealer", "proposer"
        ])
            ->where('motors.status', '!=', 'draft')
            ->where('motors.status', '!=', 'archive');

        $query = $this->permissionQuery($query);
        $query = $this->filterQuery($query, $searchValue);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    private function filterQuery($query, $searchValue)
    {
        $query = $query->where(function ($q) use ($searchValue) {
            $q->orWhere('ref_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.registration_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('proposers.name', 'like', '%' . $searchValue . '%');
            $q->orWhere('proposers.phone', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.make', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.model', 'like', '%' . $searchValue . '%');
            $q->orWhere('companies.name', 'like', '%' . $searchValue . '%');
            $q->orWhere('motors.status', 'like', '%' . $searchValue . '%');
            $q->orWhere('motors.created_at', 'like', '%' . $searchValue . '%');
        });
        return $query;
    }

    public function motors($status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        $query = Motor::eloquentQuery($orderBy, $orderByDir, $searchValue, [
            "vehicle", "dealer", "proposer"
        ])->where('motors.status', substr($status, 0, -1));

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function durationMotors($from_date, $to_date, $status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Motor::eloquentQuery($orderBy, $orderByDir, null, [
            "vehicle", "dealer", "proposer"
        ])->where('motors.status', $status);

        $query = $query->whereBetween('motors.created_at', [$from_date, $to_date]);

        $query = $this->permissionQuery($query);
        $query = $this->filterQuery($query, $searchValue);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function reports(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        $query = Motor::eloquentQuery($orderBy, $orderByDir, null, [
            'vehicle', 'proposer', 'dealer'
        ]);
        $query = $query->with('driver', 'insurer');

        // search filtering
        $query = $query->where(function ($query) use ($searchValue) {
            $query->whereHas('driver', function ($driver) use ($searchValue) {
                $driver->orWhere('occupations', 'like', '%' . $searchValue . '%');
                $driver->orWhere('name', 'like', '%' . $searchValue . '%');
                $driver->orWhere('contact_number', 'like', '%' . $searchValue . '%');
                $driver->orWhere('email', 'like', '%' . $searchValue . '%');
                $driver->orWhere('ncd', 'like', '%' . $searchValue . '%');
                $driver->orWhere('dob', 'like', '%' . $searchValue . '%');
            });
            $query->where('vehicles.registration_no', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.chassis_no', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.capacity', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.type', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.tax_expiry_date', 'like', '%' . $searchValue . '%');
            $query->orWhere('companies.name', 'like', '%' . $searchValue . '%');
            $query->orWhere('motors.created_at', 'like', '%' . $searchValue . '%');
            $query->orWhere('motors.policy_no', 'like', '%' . $searchValue . '%');
            $query->orWhere('motors.expiry_date', 'like', '%' . $searchValue . '%');
            $query->orWhere('motors.price', 'like', '%' . $searchValue . '%');
            $query->orWhere('motors.start_date', 'like', '%' . $searchValue . '%');
        });

        if ($request->type != '') {
            if ($request->fromDate != '') {
                $query = $query->where('motors.' . $request->type, '>=', $request->fromDate);
            }
            if ($request->toDate != '') {
                $query = $query->where('motors.' . $request->type, '<=', $request->toDate);
            }
        }
        if ($request->status != '') {
            $query = $query->where('motors.status', 'like', '%' . $request->status . '%');
        }

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);
        return new DataTableCollectionResource($data);
    }

    public function exportReports(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date',
            'type' => 'required|in:created_at,verified_at,completed_at'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        return Excel::download(new MotorExport($request->type, $request->fromDate, $request->toDate, $request->status), 'reports.xlsx');
    }

    public function count(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date'   => 'required|date'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $count = DB::table('motors')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status');

        $count = $count->whereBetween('created_at', [$request->from_date, $request->to_date]);

        $cout = $this->permissionQuery($count);
        $count = $count->get();
        $action = 0;
        $user = Auth::user();
        foreach ($count as $temp) {
            if ($user->category == 'all_cars') {
                if ($temp->status == 'pending_enquiry' || $temp->status == 'pending_admin_review') {
                    $action += $temp->total;
                }
            } else if ($user->category == 'dealer') {
                if ($temp->status == 'draft' || $temp->status == 'pending_acceptance' || $temp->status == 'pending_log_card') {
                    $action += $temp->total;
                }
            }
        }
        return response()->json(["count" => $count, "action" => $action], 200);
    }

    public function create(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'usage' => 'required|in:phv,private',
            'point' => 'nullable|numeric',
            'policyholder_driving' => 'required|boolean',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date',
            'remarks' => 'nullable|max:10000',

            'registration_no' => 'nullable|max:255',
            'type' => 'required|in:new,preowned',
            'model' => 'required|max:255',
            'make' => 'required|max:255',
            'engine_no' => 'required|max:255',
            'chassis_no' => 'required|max:255',
            'body_type' => 'required|max:255',
            'off_peak' => 'required|boolean',
            'modified' => 'required|boolean',
            'modification_remarks' => 'nullable|max:10000',
            'manufacture_year' => 'required|numeric',
            'capacity' => 'required|numeric',
            'seating_capacity' => 'required|numeric',

            'salutation' => 'required|in:Mr,Ms,Mrs,Mdm',
            'nric_type' => 'required|in:1,2,3,4,5',
            'nric_uen' => 'required|max:255',
            'name' => 'required|max:255',
            'nationality' => 'required|max:5',
            'residential' => 'required|in:1,2,3',
            'gender' => 'required|in:M,F',
            'occupation' => 'required|numeric',
            'date_of_birth' => 'required|date',
            'address' => 'required|max:50',
            'postal_code' => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'required|max:255',

            'main_nric_type' => 'required|in:1,2,3,4,5',
            'main_nric_uen' => 'required|max:255',
            'main_name' => 'required|max:255',
            'main_nationality' => 'required|max:5',
            'main_residential' => 'required|in:1,2,3',
            'main_gender' => 'required|in:M,F',
            'main_occupation' => 'required|in:Indoor,Outdoor',
            'main_date_of_birth' => 'required|date',
            'main_date_of_license' => 'required|date',
            'main_no_of_accidents' => 'required|numeric',
            'main_total_claim' => 'required|numeric',
            'main_ncd' => 'required|in:0,10,20,30,40,50',
            'main_serious_offence' => 'required|boolean',
            'main_physical_disable' => 'required|boolean',
            'main_refused' => 'required|boolean',
            'main_terminated' => 'required|boolean',

            'drivers' => 'nullable|array',
            'documents' => 'nullable|array',
            'driver_id' => 'nullable|exists:drivers,id',
            'proposer_id' => 'nullable|exists:proposers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'id' => 'exists:motors,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        // https://uat.iii.com.sg/restlet/v1/pa/referral/policy Status: "BLOCK"
        /*

        Messages: [{Message: "Driving experience is less than 1 year, send to underwriting."},…]
                    0: {Message: "Driving experience is less than 1 year, send to underwriting."}
                    1: {Message: "It is a modificated car."}
                    2: {Message: "Named driver is not within 27 years and 65 years."}
        */

        if ($request->point != null && $request->signature == null) {
            return response()->json(['message' => 'The signature field is required.'], 422);
        }

        if ($request->modified && $request->modification_remarks == null) {
            return response()->json(['message' => 'The modification remarks field is required.'], 422);
        }

        if ($request->type == 'preowned' && $request->registration_no == null) {
            return response()->json(['message' => 'The registration no field is required.'], 422);
        }

        //checking driving_license documents
        $driving_license = collect($request->documents)->where('type', 'driving_license')->count();

        if ($driving_license < 2) return response()->json(['message' => 'The driving license field is required.'], 422);

        $log = $request->type == 'new';
        foreach ($request->documents as $document) {
            if ($document['type'] == 'log') {
                $log = true;
            }
        }

        if (!$log) {
            return response()->json(['message' => 'The log card field is required.'], 422);
        }

        if (isset($request->id)) {
            $motor = Motor::where('id', $request->id)->first();
            if ($motor->status != 'draft') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            $motor = new Motor;
        }

        try {
            DB::transaction(function () use ($request, $motor) {
                if (isset($request->id)) {
                    // Edit Must Be Draft
                    $vehicle = Vehicle::where('id', $motor->vehicle_id)->first();
                    $proposer = Proposer::where('id', $motor->proposer_id)->first();
                    $driver = Driver::where('id', $motor->driver_id)->first();
                    MotorDocument::where('motor_id', $motor->id)->delete();
                    MotorDriver::where('motor_id', $motor->id)->delete();
                } else {
                    // Create
                    $motor->ref_no = Str::random(10);
                    $vehicle = new Vehicle;
                    $proposer = new Proposer;
                    $driver = new Driver;
                }


                $proposer->nric_type = $request->nric_type;
                $proposer->nric_uen = $request->nric_uen;
                $proposer->salutation = $request->salutation;
                $proposer->name = $request->name;
                $proposer->nationality = $request->nationality;
                $proposer->residential = $request->residential;
                $proposer->gender = $request->gender;
                $proposer->address = $request->address;
                $proposer->occupation = $request->occupation;
                $proposer->date_of_birth = $request->date_of_birth;
                $proposer->email = $request->email;
                $proposer->phone = $request->phone;
                $proposer->postal_code = $request->postal_code;
                $proposer->save();

                $driver->nric_type = $request->main_nric_type;
                $driver->nric = $request->main_nric_uen;
                $driver->name = $request->main_name;
                $driver->nationality = $request->main_nationality;
                $driver->residential = $request->main_residential;
                $driver->gender = $request->main_gender;
                $driver->occupations = $request->main_occupation;
                $driver->dob = $request->main_date_of_birth;
                $driver->license_pass_date = $request->main_date_of_license;
                $driver->no_of_accidents = $request->main_no_of_accidents;
                $driver->total_claim = $request->main_total_claim;
                $driver->ncd = $request->main_ncd;
                $driver->serious_offence = $request->main_serious_offence;
                $driver->physical_disable = $request->main_physical_disable;
                $driver->refused = $request->main_refused;
                $driver->terminated = $request->main_terminated;
                $driver->save();

                $vehicle->registration_no = $request->registration_no;
                $vehicle->type = $request->type;
                $vehicle->model = $request->model;
                $vehicle->make = $request->make;
                $vehicle->engine_no = $request->engine_no;
                $vehicle->chassis_no = $request->chassis_no;
                $vehicle->nric_uen = $request->nric_uen;
                $vehicle->off_peak = $request->off_peak;
                $vehicle->body_type = $request->body_type;
                $vehicle->modified = $request->modified;
                $vehicle->modification_remarks = $request->modification_remarks;
                $vehicle->manufacture_year = $request->manufacture_year;
                $vehicle->capacity = $request->capacity;
                $vehicle->seating_capacity = $request->seating_capacity;
                $vehicle->save();

                $motor->insurer_id = Company::where('type', 'insurer')->first()->id;
                $motor->dealer_id = Auth::user()->company_id;
                $motor->creator_id = Auth::user()->id;
                $motor->vehicle_id = $vehicle->id;
                $motor->proposer_id = $proposer->id;
                $motor->driver_id = $driver->id;

                $motor->usage = $request->usage;
                $motor->point = $request->point;
                $motor->policyholder_driving = $request->policyholder_driving;
                $motor->start_date = $request->start_date;
                $motor->expiry_date = $request->expiry_date;
                $motor->remarks = $request->remarks;
                if (isset($request->point) && $request->point != null) {
                    // CALL API
                    $this->apiQuote($motor, $proposer, $driver, $vehicle, $request->drivers);
                } else {
                    $motor->status = "pending_enquiry";
                }
                $motor->save();

                $customer = Customer::where('nric_uen', $vehicle->nric_uen)->first();
                if ($customer != null) {
                    $customer_vehicle = new CustomerVehicle;
                    $customer_vehicle->customer_id = $customer->id;
                    $customer_vehicle->vehicle_id = $vehicle->id;
                    $customer_vehicle->save();

                    $customer_motor = new CustomerMotor;
                    $customer_motor->customer_id = $customer->id;
                    $customer_motor->motor_id = $motor->id;
                    $customer_motor->save();
                }

                $motor_driver = new MotorDriver;
                $motor_driver->motor_id = $motor->id;
                $motor_driver->driver_id = $driver->id;
                $motor_driver->save();

                foreach ($request->drivers as $additional_driver) {
                    if (!isset($additional_driver['id'])) {
                        $additional_driver_model = new Driver;
                    } else {
                        $additional_driver_model = Driver::where('id', $additional_driver['id'])->first();
                    }

                    $additional_driver_model->name = $additional_driver['name'];
                    $additional_driver_model->nric_type = $additional_driver['nric_type'];
                    $additional_driver_model->nric = $additional_driver['nric_uen'];
                    $additional_driver_model->nationality = $additional_driver['nationality'];
                    $additional_driver_model->residential = $additional_driver['residential'];
                    $additional_driver_model->gender = $additional_driver['gender'];
                    $additional_driver_model->dob = $additional_driver['date_of_birth'];
                    $additional_driver_model->license_pass_date = $additional_driver['date_of_license'];
                    $additional_driver_model->no_of_accidents = $additional_driver['no_of_accidents'];
                    $additional_driver_model->total_claim = $additional_driver['total_claim'];
                    $additional_driver_model->save();
                    $motor_driver = new MotorDriver;
                    $motor_driver->motor_id = $motor->id;
                    $motor_driver->driver_id = $additional_driver_model->id;
                    $motor_driver->save();
                }

                foreach ($request->documents as $document) {
                    if ($motor->type == 'new' && $document['type'] == 'log') {
                        continue;
                    }

                    MotorDocument::create([
                        'motor_id' => $motor->id,
                        'name' => $document['name'],
                        'url' => $document['url'],
                        'type' => $document['type']
                    ]);
                }

                if ($request->point != null) {
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
                    $imageName = Str::uuid() . '.png';

                    Storage::disk('s3')->put('motor/signature/' . $imageName, $image);
                    $path = Storage::disk('s3')->url('motor/signature/' . $imageName);

                    MotorDocument::create([
                        'motor_id' => $motor->id,
                        'name' => $imageName,
                        'url' => $path,
                        'type' => 'signature',
                    ]);
                }

                MotorActionLog::create([
                    'motor_id' => $motor->id,
                    'log' => 'Motor ' . $motor->ref_no . ' with status ' . unslugify($motor->status) . ' created by ' . Auth::user()->name . '.',
                    'status' => $motor->status,
                    'user_id' => Auth::user()->id
                ]);

                if ($motor->status == "pending_admin_review") {
                    email($motor, 'emails.motors.create_review', $motor->ref_no . ' – Motor Submitted By ' . $motor->dealer->name, 'support_staff', null);
                } else {
                    email($motor, 'emails.motors.create_enquiry', $motor->ref_no . ' – Motor Submitted By ' . $motor->dealer->name, 'support_staff', null);
                }
            });
            return response()->json(['message' => "Motor created successfully.", 'id' => $motor->id], 201);
        } catch (\Exception $e) {
            Log::debug($e);
            if ($e->getCode() == 123) {
                return response()->json(['message' => $e->getMessage()], 422);
            } else {
                return response()->json(['message' => "Failed to create motor."], 422);
            }
        }
    }

    public function draft(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'usage' => 'nullable|in:phv,private',
            'point' => 'nullable|numeric',
            'policyholder_driving' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|max:10000',

            'registration_no' => 'nullable|max:255',
            'type' => 'nullable|in:new,preowned',
            'model' => 'nullable|max:255',
            'make' => 'nullable|max:255',
            'engine_no' => 'nullable|max:255',
            'chassis_no' => 'nullable|max:255',
            'body_type' => 'nullable|max:255',
            'off_peak' => 'nullable|boolean',
            'modified' => 'nullable|boolean',
            'modification_remarks' => 'nullable|max:10000',
            'manufacture_year' => 'nullable|numeric',
            'capacity' => 'nullable|numeric',
            'seating_capacity' => 'nullable|numeric',

            'salutation' => 'nullable|in:Mr,Ms,Mrs,Mdm',
            'nric_type' => 'nullable|in:1,2,3,4,5',
            'nric_uen' => 'nullable|max:255',
            'name' => 'nullable|max:255',
            'nationality' => 'nullable|max:5',
            'residential' => 'nullable|in:1,2,3',
            'gender' => 'nullable|in:M,F',
            'occupation' => 'nullable|numeric',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|max:50',
            'postal_code' => 'nullable|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|max:255',

            'main_nric_type' => 'nullable|in:1,2,3,4,5',
            'main_nric_uen' => 'nullable|max:255',
            'main_name' => 'nullable|max:255',
            'main_nationality' => 'nullable|max:5',
            'main_residential' => 'nullable|in:1,2,3',
            'main_gender' => 'nullable|in:M,F',
            'main_occupation' => 'nullable|in:Indoor,Outdoor',
            'main_date_of_birth' => 'nullable|date',
            'main_date_of_license' => 'nullable|date',
            'main_no_of_accidents' => 'nullable|numeric',
            'main_total_claim' => 'nullable|numeric',
            'main_ncd' => 'nullable|in:0,10,20,30,40,50',
            'main_serious_offence' => 'nullable|boolean',
            'main_physical_disable' => 'nullable|boolean',
            'main_refused' => 'nullable|boolean',
            'main_terminated' => 'nullable|boolean',

            'drivers' => 'nullable|array',
            'documents' => 'nullable|array',
            'driver_id' => 'nullable|exists:drivers,id',
            'proposer_id' => 'nullable|exists:proposers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'id' => 'nullable|exists:motors,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        if (isset($request->id)) {
            $motor = Motor::where('id', $request->id)->first();
            if ($motor->status != 'draft') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            $motor = new Motor;
        }

        try {
            DB::transaction(function () use ($request, $motor) {
                if (isset($request->id)) {
                    // Edit Must Be Draft
                    $vehicle = Vehicle::where('id', $motor->vehicle_id)->first();
                    $proposer = Proposer::where('id', $motor->proposer_id)->first();
                    $driver = Driver::where('id', $motor->driver_id)->first();
                    MotorDocument::where('motor_id', $motor->id)->delete();
                    MotorDriver::where('motor_id', $motor->id)->delete();
                } else {
                    // Create
                    $motor->ref_no = Str::random(10);
                    $vehicle = new Vehicle;
                    $proposer = new Proposer;
                    $driver = new Driver;
                }

                $proposer->nric_type = $request->nric_type;
                $proposer->nric_uen = $request->nric_uen;
                $proposer->salutation = $request->salutation;
                $proposer->name = $request->name;
                $proposer->nationality = $request->nationality;
                $proposer->residential = $request->residential;
                $proposer->gender = $request->gender;
                $proposer->address = $request->address;
                $proposer->occupation = $request->occupation;
                $proposer->date_of_birth = $request->date_of_birth;
                $proposer->email = $request->email;
                $proposer->phone = $request->phone;
                $proposer->postal_code = $request->postal_code;
                $proposer->save();

                $driver->nric_type = $request->main_nric_type;
                $driver->nric = $request->main_nric_uen;
                $driver->name = $request->main_name;
                $driver->nationality = $request->main_nationality;
                $driver->residential = $request->main_residential;
                $driver->gender = $request->main_gender;
                $driver->occupations = $request->main_occupation;
                $driver->dob = $request->main_date_of_birth;
                $driver->license_pass_date = $request->main_date_of_license;
                $driver->no_of_accidents = $request->main_no_of_accidents;
                $driver->total_claim = $request->main_total_claim;
                $driver->ncd = $request->main_ncd;
                $driver->serious_offence = $request->main_serious_offence;
                $driver->physical_disable = $request->main_physical_disable;
                $driver->refused = $request->main_refused;
                $driver->terminated = $request->main_terminated;
                $driver->save();

                $vehicle->registration_no = $request->registration_no;
                $vehicle->type = $request->type;
                $vehicle->model = $request->model;
                $vehicle->make = $request->make;
                $vehicle->nric_uen = $request->nric_uen;
                $vehicle->engine_no = $request->engine_no;
                $vehicle->chassis_no = $request->chassis_no;
                $vehicle->off_peak = $request->off_peak;
                $vehicle->body_type = $request->body_type;
                $vehicle->modified = $request->modified;
                $vehicle->modification_remarks = $request->modification_remarks;
                $vehicle->manufacture_year = $request->manufacture_year;
                $vehicle->capacity = $request->capacity;
                $vehicle->seating_capacity = $request->seating_capacity;
                $vehicle->save();

                $motor->insurer_id = Company::where('type', 'insurer')->first()->id;
                $motor->dealer_id = Auth::user()->company_id;
                $motor->creator_id = Auth::user()->id;
                $motor->vehicle_id = $vehicle->id;
                $motor->proposer_id = $proposer->id;
                $motor->driver_id = $driver->id;

                $motor->usage = $request->usage;
                $motor->point = $request->point;
                $motor->policyholder_driving = $request->policyholder_driving;
                $motor->start_date = $request->start_date;
                $motor->expiry_date = $request->expiry_date;
                $motor->remarks = $request->remarks;
                $motor->status = 'draft';
                $motor->save();

                $motor_driver = new MotorDriver;
                $motor_driver->motor_id = $motor->id;
                $motor_driver->driver_id = $driver->id;
                $motor_driver->save();

                foreach ($request->drivers as $additional_driver) {
                    if (!isset($additional_driver['id'])) {
                        $additional_driver_model = new Driver;
                    } else {
                        $additional_driver_model = Driver::where('id', $additional_driver['id'])->first();
                    }

                    $additional_driver_model->name = $additional_driver['name'];
                    $additional_driver_model->nric_type = $additional_driver['nric_type'];
                    $additional_driver_model->nric = $additional_driver['nric_uen'];
                    $additional_driver_model->nationality = $additional_driver['nationality'];
                    $additional_driver_model->residential = $additional_driver['residential'];
                    $additional_driver_model->gender = $additional_driver['gender'];
                    $additional_driver_model->dob = $additional_driver['date_of_birth'];
                    $additional_driver_model->license_pass_date = $additional_driver['date_of_license'];
                    $additional_driver_model->no_of_accidents = $additional_driver['no_of_accidents'];
                    $additional_driver_model->total_claim = $additional_driver['total_claim'];
                    $additional_driver_model->save();
                    $motor_driver = new MotorDriver;
                    $motor_driver->motor_id = $motor->id;
                    $motor_driver->driver_id = $additional_driver_model->id;
                    $motor_driver->save();
                }

                foreach ($request->documents as $document) {
                    if ($motor->type == 'new' && $document['type'] == 'log') {
                        continue;
                    }

                    MotorDocument::create([
                        'motor_id' => $motor->id,
                        'name' => $document['name'],
                        'url' => $document['url'],
                        'type' => $document['type']
                    ]);
                }

                if ($request->point != null) {
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
                    $imageName = Str::uuid() . '.png';

                    Storage::disk('s3')->put('motor/signature/' . $imageName, $image);
                    $path = Storage::disk('s3')->url('motor/signature/' . $imageName);

                    MotorDocument::create([
                        'motor_id' => $motor->id,
                        'name' => $imageName,
                        'url' => $path,
                        'type' => 'signature',
                    ]);
                }

                MotorActionLog::create([
                    'motor_id' => $motor->id,
                    'log' => 'Motor ' . $motor->ref_no . ' with status ' . unslugify($motor->status) . ' drafted by ' . Auth::user()->name . '.',
                    'status' => $motor->status,
                    'user_id' => Auth::user()->id
                ]);
            });
            return response()->json(['message' => "Motor drafted successfully."], 200);
        } catch (\Exception $e) {
            Log::debug($e);
            if ($e->getCode() == 123) {
                return response()->json(['message' => $e->getMessage()], 422);
            } else {
                return response()->json(['message' => "Failed to draft warranty."], 422);
            }
        }
    }

    public function archive(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:motors,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $motor = Motor::where('id', $request->id)->first();
        if ($motor->status != 'draft') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        $motor->status = 'archive';
        $motor->save();

        MotorActionLog::create([
            'motor_id' => $motor->id,
            'log' => 'Motor ' . $motor->ref_no . ' archived by ' . Auth::user()->name . '.',
            'status' => 'archive',
            'user_id' => Auth::user()->id
        ]);

        return response()->json(['message' => "Motor archived successfully."], 200);
    }
    // TODO :: permission with history
    public function motor(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:motors,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        $motor = Motor::with(['vehicle', 'dealer', 'documents', 'proposer', 'driver', 'drivers'])->where('id', $request->id)->first();
        return response()->json(['motor' => $motor], 200);
    }

    public function history($id, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        // TODO :: get logs if they have access to claims
        $query = MotorActionLog::eloquentQuery($orderBy, $orderByDir, $searchValue)->where('motor_id', $id);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function quote(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:motors,id',
            'quote_price' => 'required|numeric|not_in:0',
            'remarks' => 'max:10000'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $motor = Motor::where('id', $request->id)->first();
        if ($motor->status != 'pending_enquiry') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        $motor->price = $request->quote_price;
        $motor->status = 'pending_acceptance';
        $motor->remarks = $request->remarks;
        $motor->save();

        MotorActionLog::create([
            'motor_id' => $motor->id,
            'log' => 'Motor ' . $motor->ref_no . ' status changed from Pending Enquiry to Pending Acceptance by ' . Auth::user()->name . '.',
            'status' => $motor->status,
            'user_id' => Auth::user()->id
        ]);

        email($motor, 'emails.motors.submit_price', $motor->ref_no . ' – Motor Price Proposed by AllCars', null, $motor->dealer_id);

        return response()->json(['message' => "Motor quoted successfully."], 200);
    }

    public function dealerApprove(Request $request)
    {
        return $this->approve($request, true);
    }

    public function approve(Request $request, $dealer = false)
    {
        if ($dealer) {
            $valid = Validator::make($request->all(), [
                'id' => 'required|exists:motors,id',
                'remarks' => 'nullable|max:10000',
                'signature' => 'required'
            ]);
        } else {
            $valid = Validator::make($request->all(), [
                'id' => 'required|exists:motors,id',
                'remarks' => 'nullable|max:10000',
                'documents' => 'nullable|array'
            ]);
        }

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $motor = Motor::with(['vehicle'])->where('id', $request->id)->first();
        if ($dealer) {
            if ($motor->status != 'pending_acceptance') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            if ($motor->status != 'pending_admin_review') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
            if (sizeof($request->documents) <= 0 && $motor->vehicle->type == 'new') {
                return response()->json(['message' => 'Cover note is required.'], 422);
            }
        }

        try {
            DB::transaction(function () use ($request, $motor, $dealer) {

                $old_status = $motor->status;
                if ($dealer) {
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
                    $imageName = Str::uuid() . '.png';

                    Storage::disk('s3')->put('warranty/signature/' . $imageName, $image);
                    $path = Storage::disk('s3')->url('warranty/signature/' . $imageName);

                    $signature = MotorDocument::create([
                        'motor_id' => $motor->id,
                        'name' => $imageName,
                        'url' => $path,
                        'type' => 'signature',
                    ]);

                    $motor->status = 'pending_admin_review';
                } else if ($motor->vehicle->type == 'preowned') {
                    if ($motor->price == null) {
                        $motor->status = 'pending_ci';
                    } else {
                        // Call API
                        $this->generateDocs($motor);
                        $motor->status = 'completed';
                    }
                } else {
                    MotorDocument::where('motor_id', $motor->id)->where('type', 'note')->delete();
                    foreach ($request->documents as $document) {
                        MotorDocument::create([
                            'motor_id' => $motor->id,
                            'name' => $document['name'],
                            'url' => $document['url'],
                            'type' => $document['type']
                        ]);
                    }
                    $motor->status = 'pending_log_card';
                }
                $motor->remarks = $request->remarks;
                $motor->save();

                MotorActionLog::create([
                    'motor_id' => $motor->id,
                    'log' => 'Motor ' . $motor->ref_no . ' status changed from ' . unslugify($old_status) . ' to ' . unslugify($motor->status) . ' by ' . Auth::user()->name . '.',
                    'status' => $motor->status,
                    'user_id' => Auth::user()->id
                ]);

                if ($dealer) {
                    email($motor, 'emails.motors.approve_motor_to_allCars', $motor->ref_no . ' – Motor Approved by ' . $motor->dealer->name, 'support_staff', null);
                } else {
                    email($motor, 'emails.motors.approve_motor_to_dealer', $motor->ref_no . ' – Motor Approved by AllCars', null, $motor->dealer_id);
                }
                if ($motor->status == 'completed') {
                    email($motor, 'emails.motors.approve_motor_to_insurer', $motor->ref_no . ' – Motor Approved by AllCars', null, $motor->insurer_id);
                }
            });
            return response()->json(['message' => "Motor has been approved."], 200);
        } catch (\Exception $e) {
            Log::debug($e);
            if ($e->getCode() == 123) {
                return response()->json(['message' => $e->getMessage()], 422);
            } else {
                return response()->json(['message' => "Failed to approve warranty."], 422);
            }
        }
    }

    private function apiQuote($motor, $proposer, $driver, $vehicle, $additional_drivers)
    {
        // try {
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
        $arr["PolicyLobList"][0]["PolicyDriverList"][0]["DriverRelationshipToApplicantCd"] = $motor->policyholder_driving ? "Y" : "N";
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

        $i = 2;
        foreach ($additional_drivers as $additional_driver) {
            $seq = sprintf("%03d", $i);
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]["BusinessObjectId"] = 949634;
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]["@type"] = "PolicyDriver-DRIVER";
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['SequenceNumber'] = $seq;
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['FullName'] = $additional_driver['name'];
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['Citizenship'] = $additional_driver['nationality'];
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['Gender'] = $additional_driver['gender'];
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['IDReferenceType'] = $additional_driver['nric_type'];
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['IDReferenceNo'] = $additional_driver['nric_uen'];
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['CitizenshipType'] = $additional_driver['residential'];
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['NumLosses'] = $additional_driver['no_of_accidents'];
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['BirthDate'] = $additional_driver['date_of_birth'] . "T00:00:00";
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['LicensedDt'] = $additional_driver['date_of_license'] . "T00:00:00";
            $arr['PolicyLobList'][0]['PolicyDriverList'][$i - 1]['TotalPaidLossAmt'] = $additional_driver['total_claim'];
            $i += 1;
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
            Log::debug($arr);
            $motor->ci_no = $arr['Model']['QuotationNo'];
            $motor->policy_no = $arr['Model']['@pk'];
            $motor->price = $arr['Model']['PayablePremium'];
            $motor->status = 'pending_acceptance';
        } catch (BadResponseException $ex) {
            $motor->status = 'pending_enquiry';
        }

    }

    private function generateDocs($motor)
    {
        GenerateDocument::dispatch($motor);
    }

    public function dealerReject(Request $request)
    {
        return $this->reject($request, true);
    }

    public function reject(Request $request, $dealer = false)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:motors,id',
            'remarks' => 'max:10000'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $motor = Motor::where('id', $request->id)->first();
        if ($dealer) {
            if ($motor->status != 'pending_acceptance') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            if ($motor->status != 'pending_admin_review') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        }
        $old_status = $motor->status;
        $motor->remarks = $request->remarks;
        $motor->status = 'draft';
        $motor->save();

        MotorActionLog::create([
            'motor_id' => $motor->id,
            'log' => 'Motor ' . $motor->ref_no . ' status changed from ' . unslugify($old_status) . ' to Draft by ' . Auth::user()->name . '.',
            'status' => $motor->status,
            'user_id' => Auth::user()->id
        ]);

        if ($old_status == 'pending_admin_review') {
            email($motor, 'emails.motors.admin_reject', $motor->ref_no . ' – Motor Rejected By AllCars', null, $motor->dealer_id);
        }
        return response()->json(['message' => 'Motor has been rejected.'], 200);
    }

    public function submitLog(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:motors,id',
            'registration_no' => 'required|max:255',
            'remarks' => 'nullable|max:10000',
            'documents' => 'required|array'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $motor = Motor::where('id', $request->id)->first();
        $old_status = $motor->status;
        if ($motor->status != 'pending_log_card') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        MotorDocument::where('motor_id', $motor->id)->where('type', 'log')->delete();
        foreach ($request->documents as $document) {
            MotorDocument::create([
                'motor_id' => $motor->id,
                'name' => $document['name'],
                'url' => $document['url'],
                'type' => $document['type']
            ]);
        }

        $vehicle = Vehicle::where('id', $motor->vehicle_id)->first();
        $vehicle->registration_no = $request->registration_no;
        $vehicle->save();
        $motor->remarks = $request->remarks;
        if ($motor->price != null) {
            $this->generateDocs($motor);
            $motor->status = 'completed';
        } else {
            $motor->status = 'pending_ci';
        }
        $motor->save();

        MotorActionLog::create([
            'motor_id' => $motor->id,
            'log' => 'Motor ' . $motor->ref_no . ' status changed from ' . unslugify($old_status) . ' to ' . unslugify($motor->status) . ' by ' . Auth::user()->name . '.',
            'status' => $motor->status,
            'user_id' => Auth::user()->id
        ]);

        if ($motor->status == 'completed') {
            email($motor, 'emails.motors.approve_motor_to_dealer', $motor->ref_no . ' – Motor Approved by AllCars', null, $motor->dealer_id);
            email($motor, 'emails.motors.approve_motor_to_insurer', $motor->ref_no . ' – Motor Approved by AllCars', null, $motor->insurer_id);
        } else {
            email($motor, 'emails.motors.submit_log_card', $motor->ref_no . ' – Motor Log Card Submitted by ' . $motor->dealer->name, 'support_staff', null);
        }

        return response()->json(['message' => 'Motor has been submitted.'], 200);
    }

    public function submitCI(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:motors,id',
            'remarks' => 'nullable|max:10000',
            'documents' => 'required|array'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $motor = Motor::where('id', $request->id)->first();
        $old_status = $motor->status;
        if ($motor->status != 'pending_ci') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        MotorDocument::where('motor_id', $motor->id)->where('type', 'ci')->delete();
        foreach ($request->documents as $document) {
            MotorDocument::create([
                'motor_id' => $motor->id,
                'name' => $document['name'],
                'url' => $document['url'],
                'type' => $document['type']
            ]);
        }

        $motor->remarks = $request->remarks;
        $motor->status = 'completed';
        $motor->save();

        MotorActionLog::create([
            'motor_id' => $motor->id,
            'log' => 'Motor ' . $motor->ref_no . ' status changed from ' . unslugify($old_status) . ' to Completed by ' . Auth::user()->name . '.',
            'status' => $motor->status,
            'user_id' => Auth::user()->id
        ]);

        email($motor, 'emails.motors.approve_motor_to_dealer', $motor->ref_no . ' – Motor Approved by AllCars', null, $motor->dealer_id);
        email($motor, 'emails.motors.approve_motor_to_insurer', $motor->ref_no . ' – Motor Approved by AllCars', null, $motor->insurer_id);

        return response()->json(['message' => 'Motor has been completed.'], 200);
    }

    public function makes()
    {
        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEManufacturer/949539";
        $response = insurance_api($endpoint, ['Usage' => 'Own Use', 'Shown in B2B' => 'Y']);
        $arr = json_decode($response->getBody()->getContents(), true);
        $makes = [];
        $i = 0;
        foreach ($arr as $make) {
            $makes[$i]['make'] = $make["Id"];
            $i++;
        }
        return response()->json(['makes' => $makes], 200);
    }

    public function models(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'make' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEModel/949539";
        $response = insurance_api($endpoint, ['Make' => $request->make, 'Usage' => 'Own Use', 'Shown in B2B' => 'Y']);
        $arr = json_decode($response->getBody()->getContents(), true);
        $models = [];
        $i = 0;
        foreach ($arr as $model) {
            $models[$i]['model'] = $model["Id"];
            $i++;
        }
        return response()->json(['models' => $models], 200);
    }

    public function searchCar(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'make' => 'required',
            'model' => 'required'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEVehBodyTypeCd/949539";
        $response = insurance_api($endpoint, ['Make' => $request->make, 'Model' => $request->model, 'Shown in B2B' => 'Y']);
        $body_type = json_decode($response->getBody()->getContents(), true);
        if (isset($body_type[0])) {
            $body_type = $body_type[0]['Id'];
        } else {
            $body_type = "";
        }

        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEHorsepower/949539";
        $response = insurance_api($endpoint, ['Make' => $request->make, 'Model' => $request->model, 'Shown in B2B' => 'Y']);
        $capacity = json_decode($response->getBody()->getContents(), true);
        if (isset($capacity[0])) {
            $capacity = $capacity[0]['Id'];
        } else {
            $capacity = "";
        }
        $endpoint = env("INSURANCE_URL") . "/restlet/v1/public/codetable/data/conditions/PolicyRiskVEHICLEMakeModelPoint/949539";
        $response = insurance_api($endpoint, ['Make' => $request->make, 'Model' => $request->model, 'Shown in B2B' => 'Y']);
        $point = json_decode($response->getBody()->getContents(), true);
        if (isset($point[0])) {
            $point = $point[0]['Id'];
        } else {
            $point = "";
        }

        return response()->json(['body_type' => $body_type, 'capacity' => $capacity, 'point' => $point], 200);
    }

    
}
