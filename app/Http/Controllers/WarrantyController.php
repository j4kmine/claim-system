<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Storage;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\CustomerVehicle;
use App\Models\CustomerWarranty;
use App\Models\Proposer;
use App\Models\Warranty;
use App\Models\WarrantyDocument;
use App\Models\WarrantyActionLog;
use App\Models\WarrantyPrice;
use App\Imports\PricesImport;
use App\Exports\WarrantyExport;
use App\Exports\PricesExport;
use Auth;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Illuminate\Support\Str;
use mikehaertl\pdftk\Pdf;
use Carbon\Carbon;
use Log;
use Excel;
use DomPDF;
use Illuminate\Support\Facades\DB;

class WarrantyController extends Controller
{

    private function permissionQuery($query)
    {
        if (Auth::user()->category == 'dealer') {
            $query = $query->where('warranties.dealer_id', Auth::user()->company_id);
        } else if (Auth::user()->category == 'insurer') {
            $query = $query->where('warranties.insurer_id', Auth::user()->company_id);
        }
        return $query;
    }

    public function activeWarranties(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Warranty::eloquentQuery($orderBy, $orderByDir, null, [
            "vehicle", "dealer", "proposer", "insurer"
        ])
            ->where('warranties.status', '!=', 'draft')
            ->where('warranties.status', '!=', 'archive');



        $query = $this->permissionQuery($query);
        $query = $this->filterQuery($query, $searchValue);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    private function filterQuery($query, $searchValue)
    {
        $query = $query->where(function ($q) use ($searchValue) {
            $q->orWhere('ref_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('ci_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.registration_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('proposers.name', 'like', '%' . $searchValue . '%');
            $q->orWhere('proposers.phone', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.make', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.model', 'like', '%' . $searchValue . '%');
            $q->orWhere('companies.name', 'like', '%' . $searchValue . '%');
            $q->orWhere('warranties.status', 'like', '%' . $searchValue . '%');
            $q->orWhere('warranties.created_at', 'like', '%' . $searchValue . '%');
        });
        return $query;
    }

    public function warranties($status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Warranty::eloquentQuery($orderBy, $orderByDir, $searchValue, [
            "vehicle", "dealer", "proposer", "insurer"
        ])->where('warranties.status', substr($status, 0, -1));

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function durationWarranties($from_date, $to_date, $status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Warranty::eloquentQuery($orderBy, $orderByDir, null, [
            "vehicle", "dealer", "proposer", "insurer"
        ])->where('warranties.status', $status);

        $query = $query->whereBetween('warranties.created_at', [$from_date, $to_date]);

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
        $query = Warranty::eloquentQuery($orderBy, $orderByDir, null, [
            "dealer", 'insurer', 'proposer'
        ]);
        $query = $query->with('vehicle.motor');

        // search
        $query = $query->where(function ($query) use ($searchValue, $request) {
            if ($request->vehicle_type == 'preowned') {
                $query->orWhereHas('vehicle.motor', function ($motor) use ($searchValue) {
                    $motor->orWhere('policy_no', 'like', '%' . $searchValue . '%');
                });
                $query->orWhere('companies.code', 'like', '%' . $searchValue . '%');
            }

            $query->where('proposers.name', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.registration_no', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.make', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.model', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.engine_no', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.chassis_no', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.capacity', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.manufacture_year', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.registration_date', 'like', '%' . $searchValue . '%');
            $query->orWhere('companies.name', 'like', '%' . $searchValue . '%');
            $query->orWhere('companies.address', 'like', '%' . $searchValue . '%');
            $query->orWhere('warranties.ci_no', 'like', '%' . $searchValue . '%');
            $query->orWhere('warranties.created_at', 'like', '%' . $searchValue . '%');
            $query->orWhere('warranties.expiry_date', 'like', '%' . $searchValue . '%');
            $query->orWhere('warranties.mileage_coverage', 'like', '%' . $searchValue . '%');
            $query->orWhere('warranties.max_claim', 'like', '%' . $searchValue . '%');
            $query->orWhere('warranties.warranty_duration', 'like', '%' . $searchValue . '%');
            $query->orWhere('warranties.mileage', 'like', '%' . $searchValue . '%');
        });

        if ($request->vehicle_type != "") {
            $query = $query->where("vehicles.type", $request->vehicle_type);
        }
        if ($request->type != '') {
            if ($request->fromDate != '') {
                $query = $query->where('warranties.' . $request->type, '>=', $request->fromDate);
            }
            if ($request->toDate != '') {
                $query = $query->where('warranties.' . $request->type, '<=', $request->toDate);
            }
        }
        if ($request->status != '') {
            $query = $query->where('warranties.status', $request->status);
        }

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);
        return new DataTableCollectionResource($data);
    }

    public function exportReports(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'vehicleType' => 'nullable',
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date',
            'type' => 'required|in:created_at,verified_at,completed_at'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        return Excel::download(new WarrantyExport($request->vehicleType, $request->type, $request->fromDate, $request->toDate, $request->status), 'reports.xlsx');
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

        $count = DB::table('warranties')
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
                if ($temp->status == 'draft' || $temp->status == 'pending_acceptance') {
                    $action += $temp->total;
                }
            }
        }
        return response()->json(["count" => $count, "action" => $action], 200);
    }

    public function create(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'registration_no' => 'required|max:255',
            'chassis_no' => 'required|max:255',
            'engine_no' => 'required|max:255',
            'make' => 'required|max:255',
            'model' => 'required|max:255',
            'mileage' => 'nullable|integer',
            'capacity' => 'required|integer',
            'category' => 'nullable|max:255',
            'nric_uen' => 'required|max:255',
            'package' => 'max:255',
            'remarks' => 'nullable|max:10000',
            'manufacture_year' => 'required|integer',
            'registration_date' => 'required|date',
            'type' => 'required|in:new,preowned',
            'fuel' => 'required|in:hybrid,non_hybrid',
            'price' => 'nullable|numeric',
            'extended' => 'required|boolean',
            'max_claim' => 'nullable|integer',
            'mileage_coverage' => 'nullable|integer',
            'warranty_duration' => 'required|numeric',
            'start_date' => 'required|date',
            'name' => 'required|max:255',
            'salutation' => 'required|in:Mr,Ms,Mrs,Mdm,Dr,Company',
            'address' => 'required|max:1000',
            'email' => 'required|email',
            'phone' => 'required|max:255',
            'documents' => 'required|array',
            'insurer_id' => 'nullable|exists:companies,id',
            'proposer_id' => 'nullable|exists:proposers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'selected_warranty' => 'nullable|integer',
            'id' => 'exists:warranties,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        if ($request->price != null && $request->signature == null) {
            return response()->json(['message' => 'The signature field is required.'], 422);
        }

        if ($request->type == 'preowned' && !isset($request->mileage)) {
            return response()->json(['message' => 'The mileage field is required.'], 422);
        }

        $log = false;
        $assessment = $request->type == 'new';
        foreach ($request->documents as $document) {
            if ($document['type'] == 'log') {
                $log = true;
            }
            if ($document['type'] == 'assessment') {
                $assessment = true;
            }
        }

        if (!$log) {
            return response()->json(['message' => 'The log card field is required.'], 422);
        }

        if (!$assessment) {
            return response()->json(['message' => 'The assessment report field is required.'], 422);
        }

        if (isset($request->id)) {
            $warranty = Warranty::where('id', $request->id)->first();
            if ($warranty->status != 'draft') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            $warranty = new Warranty;
        }

        try {
            DB::transaction(function () use ($request, $warranty) {
                if (isset($request->id)) {
                    // Edit Must Be Draft
                    $vehicle = Vehicle::where('id', $warranty->vehicle_id)->first();
                    $proposer = Proposer::where('id', $warranty->proposer_id)->first();
                    WarrantyDocument::where('warranty_id', $warranty->id)->delete();
                } else {
                    // Create
                    $warranty->ref_no = Str::random(10);
                    $vehicle = new Vehicle;
                    $proposer = new Proposer;
                }
                /*
                    $customer = Customer::where('nric_uen', $request->nric_uen)->first();
                    if($customer == null){
                        $customer = new Customer;
                        $customer->status = 'inactive';
                        $customer->password = bcrypt('carfren!234');
                    }

                    $customer->nric_uen = $request->nric_uen;
                    $customer->salutation = $request->salutation;
                    $customer->name = $request->name;
                    $customer->address = $request->address;
                    $customer->email = $request->email;
                    $customer->phone = $request->phone;
                    $customer->save();
                */

                $proposer->nric_uen = $request->nric_uen;
                $proposer->salutation = $request->salutation;
                $proposer->name = $request->name;
                $proposer->address = $request->address;
                $proposer->email = $request->email;
                $proposer->phone = $request->phone;
                $proposer->save();

                $vehicle->registration_no = $request->registration_no;
                $vehicle->chassis_no = $request->chassis_no;
                $vehicle->engine_no = $request->engine_no;
                $vehicle->make = $request->make;
                $vehicle->model = $request->model;
                $vehicle->mileage = $request->mileage;
                $vehicle->manufacture_year = $request->manufacture_year;
                $vehicle->registration_date = $request->registration_date;
                $vehicle->nric_uen = $request->nric_uen;
                $vehicle->capacity = $request->capacity;
                $vehicle->category = $request->category;
                $vehicle->type = $request->type;
                $vehicle->fuel = $request->fuel;
                $vehicle->save();

                if (isset($request->price) && $request->price != null) {
                    $warranty->status = "pending_admin_review";
                } else {
                    $warranty->status = "pending_enquiry";
                }

                $warranty->dealer_id = Auth::user()->company_id;
                $warranty->creator_id = Auth::user()->id;
                $warranty->insurer_id = $request->insurer_id;
                $warranty->vehicle_id = $vehicle->id;
                $warranty->proposer_id = $proposer->id;
                if ($request->extended && $request->type == 'new') {
                    $warranty->package = 'III x CarFren+';
                    $warranty->extended = 1;
                } else {
                    $warranty->package = $request->package;
                    $warranty->extended = 0;
                }
                // $warranty->customer_id = $customer->id;
                $warranty->price = $request->price;
                $warranty->max_claim = $request->max_claim;
                $warranty->mileage = $request->mileage;
                $warranty->mileage_coverage = $request->mileage_coverage;
                $warranty->warranty_duration =  $request->warranty_duration;
                $warranty->start_date =  $request->start_date;
                $warranty->remarks = $request->remarks;
                $warranty->save();

                $customer = Customer::where('nric_uen', $vehicle->nric_uen)->first();
                if ($customer != null) {
                    // TODO:: Add for all the sub owners
                    $customer_vehicle = new CustomerVehicle;
                    $customer_vehicle->customer_id = $customer->id;
                    $customer_vehicle->vehicle_id = $vehicle->id;
                    $customer_vehicle->save();

                    $customer_warranty = new CustomerWarranty;
                    $customer_warranty->customer_id = $customer->id;
                    $customer_warranty->warranty_id = $warranty->id;
                    $customer_warranty->save();
                }

                foreach ($request->documents as $document) {
                    if ($warranty->type == 'new' && $document['type'] == 'assessment') {
                        continue;
                    }
                    WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $document['name'],
                        'url' => $document['url'],
                        'type' => $document['type']
                    ]);
                }

                if ($request->price != null) {
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
                    $imageName = Str::uuid() . '.png';

                    Storage::disk('s3')->put('warranty/signature/' . $imageName, $image);
                    $path = Storage::disk('s3')->url('warranty/signature/' . $imageName);

                    WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $imageName,
                        'url' => $path,
                        'type' => 'signature',
                    ]);
                }

                WarrantyActionLog::create([
                    'warranty_id' => $warranty->id,
                    'log' => 'Warranty ' . $warranty->ref_no . ' with status ' . unslugify($warranty->status) . ' created by ' . Auth::user()->name . '.',
                    'status' => $warranty->status,
                    'user_id' => Auth::user()->id
                ]);
                if ($warranty->status == "pending_admin_review") {
                    email($warranty, 'emails.warranties.create_review', $warranty->ref_no . ' – Warranty Submitted By ' . $warranty->dealer->name, 'support_staff', null);
                } else {
                    email($warranty, 'emails.warranties.create_enquiry', $warranty->ref_no . ' – Warranty Submitted By ' . $warranty->dealer->name, 'support_staff', null);
                }


                $token = env('DEFAULT_TOKEN');
                if (isset($customer->id) && $customer->id != "") {
                    $device = CustomerDevice::where('customer_id', $customer->id)->first();
                    if (isset($device->device_id) && $device->device_id != "") {
                        $token = $device->device_id;
                    }
                    $this->sendMessage($token, "Warranty created successfully", "Warranty created successfully");
                }
            });
            DB::commit();


            return response()->json(['message' => "Warranty created successfully.", 'id' => $warranty->id], 201);
        } catch (\Exception $e) {
            Log::debug($e);
            DB::rollback();
            if ($e->getCode() == 123) {
                return response()->json(['message' => $e->getMessage()], 422);
            } else {
                return response()->json(['message' => "Failed to create warranty."], 422);
            }
        }
    }
    private function sendMessage($deviceToken = "", $title = "", $body = "")
    {


        $data = [
            "registration_ids" => $deviceToken,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . env('SERVER_API_KEY'),
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
    }
    public function draft(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'registration_no' => 'nullable|max:255',
            'chassis_no' => 'nullable|max:255',
            'engine_no' => 'nullable|max:255',
            'make' => 'nullable|max:255',
            'model' => 'nullable|max:255',
            'mileage' => 'nullable|integer',
            'capacity' => 'nullable|integer',
            'category' => 'nullable|max:255',
            'nric_uen' => 'nullable|max:255',
            'package' => 'nullable|max:255',
            'remarks' => 'nullable|max:10000',
            'manufacture_year' => 'nullable|integer',
            'registration_date' => 'nullable|date',
            'type' => 'nullable|in:new,preowned',
            'fuel' => 'nullable|in:hybrid,non_hybrid',
            'price' => 'nullable|numeric',
            'extended' => 'nullable|boolean',
            'max_claim' => 'nullable|integer',
            'mileage_coverage' => 'nullable|integer',
            'warranty_duration' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'name' => 'nullable|max:255',
            'salutation' => 'nullable|in:Mr,Ms,Mrs,Mdm,Dr,Company',
            'address' => 'nullable|max:1000',
            'email' => 'nullable|email',
            'phone' => 'nullable|max:255',
            'documents' => 'array',
            'insurer_id' => 'nullable|exists:companies,id',
            'proposer_id' => 'nullable|exists:proposers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'signature' => 'nullable',
            'selected_warranty' => 'nullable|integer',
            'id' => 'nullable|exists:warranties,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        if (isset($request->id)) {
            $warranty = Warranty::where('id', $request->id)->first();
            if ($warranty->status != 'draft') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            $warranty = new Warranty;
        }

        try {
            DB::transaction(function () use ($request, $warranty) {
                if (isset($request->id)) {
                    // Edit Must Be Draft
                    WarrantyDocument::where('warranty_id', $warranty->id)->delete();
                    $proposer = Proposer::where('id', $warranty->proposer_id)->first();
                    $vehicle = Vehicle::where('id', $warranty->vehicle_id)->first();
                } else {
                    $warranty->ref_no = Str::random(10);
                    $proposer = new Proposer;
                    $vehicle = new Vehicle;
                }

                $proposer->nric_uen = $request->nric_uen;
                $proposer->salutation = $request->salutation;
                $proposer->name = $request->name;
                $proposer->address = $request->address;
                $proposer->email = $request->email;
                $proposer->phone = $request->phone;
                $proposer->save();

                $vehicle->registration_no = $request->registration_no;
                $vehicle->chassis_no = $request->chassis_no;
                $vehicle->engine_no = $request->engine_no;
                $vehicle->make = $request->make;
                $vehicle->model = $request->model;
                $vehicle->mileage = $request->mileage;
                $vehicle->manufacture_year = $request->manufacture_year;
                $vehicle->registration_date = $request->registration_date;
                $vehicle->nric_uen = $request->nric_uen;
                $vehicle->capacity = $request->capacity;
                $vehicle->category = $request->category;
                $vehicle->type = $request->type;
                $vehicle->fuel = $request->fuel;
                $vehicle->save();

                $warranty->dealer_id = Auth::user()->company_id;
                $warranty->creator_id = Auth::user()->id;
                $warranty->insurer_id = $request->insurer_id;
                $warranty->vehicle_id = $vehicle->id;
                $warranty->proposer_id = $proposer->id;
                $warranty->price = $request->price;
                if ($request->extended && $request->type == 'new') {
                    $warranty->package = 'III x CarFren+';
                    $warranty->extended = 1;
                } else {
                    $warranty->package = $request->package;
                    $warranty->extended = 0;
                }
                $warranty->max_claim = $request->max_claim;
                $warranty->mileage = $request->mileage;
                $warranty->mileage_coverage = $request->mileage_coverage;
                $warranty->warranty_duration =  $request->warranty_duration;
                $warranty->start_date = $request->start_date;
                $warranty->remarks = $request->remarks;
                $warranty->status = 'draft';
                $warranty->save();

                foreach ($request->documents as $document) {
                    WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $document['name'],
                        'url' => $document['url'],
                        'type' => $document['type']
                    ]);
                }
                /*
                if($request->signature != null){
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$request->signature));
                    $imageName = Str::uuid() . '.png';

                    Storage::disk('s3')->put('warranty/signature/'.$imageName, $image);
                    $path = Storage::disk('s3')->url('warranty/signature/'.$imageName);

                    WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $imageName,
                        'url' => $path,
                        'type' => 'signature',
                    ]);
                }*/

                WarrantyActionLog::create([
                    'warranty_id' => $warranty->id,
                    'log' => 'Warranty ' . $warranty->ref_no . ' drafted by ' . Auth::user()->name . '.',
                    'status' => $warranty->status,
                    'user_id' => Auth::user()->id
                ]);
            });
            DB::commit();
            return response()->json(['message' => "Warranty drafted successfully."], 200);
        } catch (\Exception $e) {
            Log::debug($e);
            DB::rollback();
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
            'id' => 'required|exists:warranties,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $warranty = Warranty::where('id', $request->id)->first();
        if ($warranty->status != 'draft') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        $warranty->status = 'archive';
        $warranty->save();

        WarrantyActionLog::create([
            'warranty_id' => $warranty->id,
            'log' => 'Warranty ' . $warranty->ref_no . ' archived by ' . Auth::user()->name . '.',
            'status' => 'archive',
            'user_id' => Auth::user()->id
        ]);

        return response()->json(['message' => "Warranty archived successfully."], 200);
    }
    // TODO :: permission with history
    public function warranty(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:warranties,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $warranty = Warranty::with(['proposer', 'vehicle', 'dealer', 'documents'])->where('id', $request->id)->first();
        return response()->json(['warranty' => $warranty], 200);
    }

    public function history($id, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        // TODO :: get logs if they have access to claims
        $query = WarrantyActionLog::eloquentQuery($orderBy, $orderByDir, $searchValue)->where('warranty_id', $id);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function quote(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:warranties,id',
            'quote_price' => 'required|numeric|not_in:0',
            'quote_max_claim' => 'required|numeric|not_in:0',
            'insurer_id' => 'required|exists:companies,id',
            'remarks' => 'max:10000'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $warranty = Warranty::where('id', $request->id)->first();
        if ($warranty->status != 'pending_enquiry') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        if ($warranty->extended) {
            // Need to remove the extra 10%
            $warranty->price = ($request->quote_price / 11) * 10;
        } else {
            $warranty->price = $request->quote_price;
        }
        $warranty->max_claim = $request->quote_max_claim;
        $warranty->insurer_id = $request->insurer_id;
        $warranty->status = 'pending_acceptance';
        $warranty->remarks = $request->remarks;
        $warranty->save();

        WarrantyActionLog::create([
            'warranty_id' => $warranty->id,
            'log' => 'Warranty ' . $warranty->ref_no . ' status changed from Pending Enquiry to Pending Proposal by ' . Auth::user()->name . '.',
            'status' => $warranty->status,
            'user_id' => Auth::user()->id
        ]);

        email($warranty, 'emails.warranties.submit_price', $warranty->ref_no . ' – Warranty Price Proposed by AllCars', null, $warranty->dealer_id);

        return response()->json(['message' => "Warranty quoted successfully."], 200);
    }

    public function dealerApprove(Request $request)
    {
        return $this->approve($request, true);
    }

    public function approve(Request $request, $dealer = false)
    {
        if ($dealer) {
            $valid = Validator::make($request->all(), [
                'id' => 'required|exists:warranties,id',
                'remarks' => 'nullable|max:10000',
                'signature' => 'required'
            ]);
        } else {
            $valid = Validator::make($request->all(), [
                'id' => 'required|exists:warranties,id',
                'remarks' => 'nullable|max:10000'
            ]);
        }

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $warranty = Warranty::with(['vehicle', 'proposer'])->where('id', $request->id)->first();
        if ($dealer) {
            if ($warranty->status != 'pending_acceptance') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            if ($warranty->status != 'pending_admin_review') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        }

        try {
            DB::transaction(function () use ($request, $warranty, $dealer) {
                if ($warranty->vehicle->type == 'new') {
                    $last_warranty = Warranty::where('status', 'completed')->where('ci_no', 'like', 'C%')->orderBy('ci_no', 'desc')->first();
                } else {
                    $last_warranty = Warranty::where('status', 'completed')->where('ci_no', 'like', 'UC%')->orderBy('ci_no', 'desc')->first();
                }
                if ($last_warranty == null) {
                    if ($warranty->vehicle->type == 'new') {
                        $warranty->ci_no = "C04308";
                    } else {
                        $warranty->ci_no = "UC00359";
                    }
                } else {
                    if ($warranty->vehicle->type == 'new') {
                        $warranty->ci_no = "C" . sprintf("%05d", (ltrim(explode("C", $last_warranty->ci_no)[1]) + 1));
                    } else {
                        $warranty->ci_no = "UC" . sprintf("%05d", (ltrim(explode("UC", $last_warranty->ci_no)[1]) + 1));
                    }
                }

                $warranty->status = 'completed';
                $warranty->remarks = $request->remarks;
                $warranty->save();

                // Create Warranty Booklets
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
                $result = $pdf->fillForm($data)->needAppearances()->saveAs($file_path);
                Storage::disk('s3')->put('warranty/warranty/' . $filename, fopen($file_path, 'r+'));
                $path = Storage::disk('s3')->url('warranty/warranty/' . $filename);
                WarrantyDocument::create([
                    'warranty_id' => $warranty->id,
                    'name' => $filename,
                    'url' => $path,
                    'type' => 'warranty'
                ]);

                // Create Form for salesperson
                $pdf = new Pdf(storage_path('app/documents/') . 'warranty-' . $warranty->vehicle->type . '-application.pdf');
                $filename = Str::uuid() . ".pdf";
                $file_path = storage_path('app/generated/') . $filename;
                $data = [
                    'agent_code' => 'AllCars Agency Pte Ltd',
                    'dealer_name' => $warranty->dealer->name,
                    'salutation' => $warranty->proposer->salutation,
                    'proposer_name' => $warranty->proposer->name,
                    'nric' => $warranty->proposer->nric_uen,
                    'address' => $warranty->proposer->address,
                    'email' => $warranty->proposer->email,
                    'mobile' => $warranty->proposer->phone,
                    'registration_no' => $warranty->vehicle->registration_no,
                    'mileage' => $warranty->mileage,
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

                if ($dealer) {
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
                    $imageName = Str::uuid() . '.png';

                    Storage::disk('s3')->put('warranty/signature/' . $imageName, $image);
                    $path = Storage::disk('s3')->url('warranty/signature/' . $imageName);

                    $signature = WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $imageName,
                        'url' => $path,
                        'type' => 'signature',
                    ]);
                } else {
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
                    $imageName = Str::uuid() . '.png';

                    Storage::disk('s3')->put('warranty/signature/' . $imageName, $image);
                    $path = Storage::disk('s3')->url('warranty/signature/' . $imageName);

                    $signature = WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $imageName,
                        'url' => $path,
                        'type' => 'signature',
                    ]);
                    $signature = WarrantyDocument::where('warranty_id', $warranty->id)->where('type', 'signature')->first();
                    $delete=WarrantyDocument::where('id','=',$signature->id)->delete();
                }
                $temp_url = Storage::disk('s3')->temporaryUrl('warranty/signature/' . basename($signature->url), Carbon::now()->addMinutes(30));
                $signature_path = storage_path('app/generated/') . str_replace(".png", ".pdf", $signature->name);
                $signature_pdf = DomPDF::loadHTML('"
                    <html>
                        <style>
                        body {
                            background: url(' . $temp_url . ');
                            background-position: 16mm 255mm;
                            background-size: 50mm 13mm;
                            background-repeat: no-repeat;
                        }
                        @page {
                            size: A4;
                            margin: 0;
                        }
                        @media print {
                            html,
                            body {
                                width: 210mm;
                                height: 297mm;
                                padding: 0;
                                margin: 0;
                            }
                        }
                        </style>
                        <body>
                        </body>
                    </html>
                "')->setPaper('a4', 'portrait')->save($signature_path);
                $result = $pdf->stamp($signature_path)->saveAs($file_path);
                $pdf = new Pdf($file_path);
                $result = $pdf->fillForm($data)->needAppearances()->saveAs($file_path);
                Storage::disk('s3')->put('warranty/form/' . $filename, fopen($file_path, 'r+'));
                $path = Storage::disk('s3')->url('warranty/form/' . $filename);
                $warranty_form = WarrantyDocument::create([
                    'warranty_id' => $warranty->id,
                    'name' => $filename,
                    'url' => $path,
                    'type' => 'form'
                ]);

                // Create CI Form for insurer
                $pdf = new Pdf(storage_path('app/documents/') . 'ci-' . $warranty->vehicle->type . '-template.pdf');
                $filename = Str::uuid() . ".pdf";
                $file_path = storage_path('app/generated/') . $filename;

                $data = [
                    'Dealer_Name' => $warranty->dealer->name,
                    'Dealer_Address' => $warranty->dealer->address,
                    'Owner_Name' => $warranty->proposer->name,
                    'Cert_No' => $warranty->ci_no,
                    'Mileage' => $warranty->mileage_coverage,
                    'Max_Claim' => $warranty->max_claim,
                    'Vehicle_No' => $warranty->vehicle->registration_no,
                    'YOM' => $warranty->vehicle->manufacture_year,
                    'Make' => $warranty->vehicle->make,
                    'Engine_Cap' => $warranty->vehicle->capacity,
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
                        'Engine_Cap' => $warranty->vehicle->capacity,
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
                    $path = Storage::disk('s3')->url('warranty/coc/' . $filename);
                    $warranty_coc = WarrantyDocument::create([
                        'warranty_id' => $warranty->id,
                        'name' => $filename,
                        'url' => $path,
                        'type' => 'coc'
                    ]);
                }

                if ($dealer) {
                    WarrantyActionLog::create([
                        'warranty_id' => $warranty->id,
                        'log' => 'Warranty ' . $warranty->ref_no . ' status changed from Pending Proposal to Completed by ' . Auth::user()->name . '.',
                        'status' => $warranty->status,
                        'user_id' => Auth::user()->id
                    ]);
                } else {
                    WarrantyActionLog::create([
                        'warranty_id' => $warranty->id,
                        'log' => 'Warranty ' . $warranty->ref_no . ' status changed from Pending Admin Review to Completed by ' . Auth::user()->name . '.',
                        'status' => $warranty->status,
                        'user_id' => Auth::user()->id
                    ]);
                }

                if ($dealer) {
                    email($warranty, 'emails.warranties.approve_warranty_to_allCars', $warranty->ref_no . ' – Warranty Approved by ' . $warranty->dealer->name, 'support_staff', null);
                } else {
                    email($warranty, 'emails.warranties.approve_warranty_to_dealer', $warranty->ref_no . ' – Warranty Approved by AllCars', null, $warranty->dealer_id);
                }

                $warranty_log = WarrantyDocument::where('warranty_id', $warranty->id)->where('type', 'log')->first();
                $log_attachment = [
                    'path' => Storage::disk('s3')->temporaryUrl(substr(parse_url($warranty_log->url)['path'], 1), Carbon::now()->addMinutes(30)),
                    'name' => 'log_card.' . pathinfo($warranty_log->name, PATHINFO_EXTENSION),
                    'mime' => mime_type(basename($warranty_log->url))
                ];

                $form_attachment = [
                    'path' => storage_path('app/generated/') . $warranty_form->name,
                    'name' => 'salesperson_form.pdf',
                    'mime' => 'application/pdf'
                ];

                $ci_attachment = [
                    'path' => storage_path('app/generated/') . $warranty_ci->name,
                    'name' => 'ci.pdf',
                    'mime' => 'application/pdf'
                ];

                $files = [$log_attachment, $form_attachment, $ci_attachment];

                $warranty_assessment = WarrantyDocument::where('warranty_id', $warranty->id)->where('type', 'assessment')->first();
                if ($warranty_assessment != null) {
                    $assessment_attachment = [
                        'path' => Storage::disk('s3')->temporaryUrl(substr(parse_url($warranty_assessment->url)['path'], 1), Carbon::now()->addMinutes(30)),
                        'name' => 'assessment_report.' . pathinfo($warranty_assessment->name, PATHINFO_EXTENSION),
                        'mime' => mime_type(basename($warranty_assessment->url))
                    ];
                    $files[] = $assessment_attachment;
                }

                email($warranty, 'emails.warranties.approve_warranty_to_insurer', $warranty->ref_no . ' – Warranty Approved by AllCars', null, $warranty->insurer_id, $files);
            });
            DB::commit();
            return response()->json(['message' => "Warranty has been approved."], 200);
        } catch (\Exception $e) {
            Log::debug($e);
            DB::rollback();
            if ($e->getCode() == 123) {
                return response()->json(['message' => $e->getMessage()], 422);
            } else {
                return response()->json(['message' =>  $e->getMessage()], 422);
            }
        }
    }

    public function dealerReject(Request $request)
    {
        return $this->reject($request, true);
    }

    // TODO:: Check permission if can touch claim
    public function reject(Request $request, $dealer = false)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:warranties,id',
            'remarks' => 'max:10000'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $warranty = Warranty::where('id', $request->id)->first();
        $old_status = $warranty->status;
        if ($warranty->status != 'pending_admin_review' && $warranty->status != 'pending_acceptance' && $warranty->status != 'pending_enquiry') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        if ($dealer) {
            if ($warranty->dealer_id != Auth::user()->company_id) {
                return response()->json(['message' => 'No access to specified warranty.'], 422);
            }
        }
        $warranty->remarks = $request->remarks;
        $warranty->status = 'draft';
        $warranty->save();

        WarrantyActionLog::create([
            'warranty_id' => $warranty->id,
            'log' => 'Warranty ' . $warranty->ref_no . ' status changed from ' . unslugify($old_status) . ' to Draft by ' . Auth::user()->name . '.',
            'status' => $warranty->status,
            'user_id' => Auth::user()->id
        ]);

        if ($old_status == 'pending_admin_review') {
            email($warranty, 'emails.warranties.admin_reject', $warranty->ref_no . ' – Warranty Rejected By AllCars', null, $warranty->dealer_id);
        } else if ($old_status == 'pending_acceptance') {
            email($warranty, 'emails.warranties.admin_reject', $warranty->ref_no . ' – Warranty Rejected By Dealer', 'support_staff', null);
        }
        return response()->json(['message' => 'Warranty has been rejected.'], 200);
    }

    public function makes()
    {
        $makes = WarrantyPrice::groupBy('make')->select('make', DB::raw('count(*) as total'))->get();
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

        $models = WarrantyPrice::where('make', $request->make)->groupBy('model')->select('model', DB::raw('count(*) as total'))->get();
        return response()->json(['models' => $models], 200);
    }

    public function searchPrices(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'make' => 'required',
            'model' => 'required',
            'type' => 'required|in:new,preowned',
            'fuel' => 'required|in:hybrid,non_hybrid'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $prices = WarrantyPrice::where('make', $request->make)
            ->where('model', $request->model)
            ->where('type', $request->type)
            ->where('fuel', $request->fuel)
            ->get();

        return response()->json(['prices' => $prices], 200);
    }

    public function price(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:warranty_prices,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $price = WarrantyPrice::where('id', $request->id)->first();
        return response()->json(['price' => $price], 200);
    }

    public function createPrice(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'make' => 'required',
            'model' => 'required',
            'category' => 'required',
            'capacity' => 'required|integer',
            'type' => 'required|in:new,preowned',
            'fuel' => 'required|in:hybrid,non_hybrid',
            'price' => 'required|numeric',
            'max_claim' => 'required|integer',
            'mileage_coverage' => 'required|integer',
            'warranty_duration' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $price = new WarrantyPrice;
        $price->make = $request->make;
        $price->model = $request->model;
        $price->category = $request->category;
        $price->capacity = $request->capacity;
        $price->type = $request->type;
        $price->fuel = $request->fuel;
        $price->price = $request->price;
        $price->max_claim = $request->max_claim;
        $price->mileage_coverage = $request->mileage_coverage;
        $price->warranty_duration = $request->warranty_duration;
        $price->status = $request->status;
        $price->save();

        return response()->json(['message' => 'Successfully created price.'], 201);
    }

    public function editPrice(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:warranty_prices,id',
            'make' => 'required',
            'model' => 'required',
            'category' => 'required',
            'capacity' => 'required|integer',
            'type' => 'required|in:new,preowned',
            'fuel' => 'required|in:hybrid,non_hybrid',
            'price' => 'required|numeric',
            'max_claim' => 'required|integer',
            'mileage_coverage' => 'required|integer',
            'warranty_duration' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $price = WarrantyPrice::where('id', $request->id)->first();
        $price->make = $request->make;
        $price->model = $request->model;
        $price->category = $request->category;
        $price->capacity = $request->capacity;
        $price->type = $request->type;
        $price->fuel = $request->fuel;
        $price->price = $request->price;
        $price->max_claim = $request->max_claim;
        $price->mileage_coverage = $request->mileage_coverage;
        $price->warranty_duration = $request->warranty_duration;
        $price->status = $request->status;
        $price->save();

        return response()->json(['message' => 'Successfully edited price.'], 200);
    }

    public function deletePrices(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'ids' => 'required|array',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        $delete = [];
        foreach ($request->ids as $key => $value) {
            if ($value) {
                $delete[] = $key;
            }
        }
        WarrantyPrice::whereIn('id', $delete)->delete();
        return response()->json(['message' => 'Successfully deleted price.'], 200);
    }

    public function prices(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $data = WarrantyPrice::eloquentQuery($orderBy, $orderByDir, $searchValue)->paginate($length);
        return new DataTableCollectionResource($data);
    }

    public function importPrices(Request $request)
    {
        if ($request->file == null || $request->file == 'null' || $request->file == 'undefined') {
            return response()->json(['message' => 'File is invalid.'], 422);
        }
        //ini_set('memory_limit', '2048M');
        WarrantyPrice::truncate();
        Excel::import(new PricesImport, $request->file('file')->store('temp'));
        return response()->json(['message' => 'Warranty prices has been imported.'], 200);
    }

    public function exportPrices(Request $request)
    {
        return Excel::download(new PricesExport, 'prices.xlsx');
    }
}
