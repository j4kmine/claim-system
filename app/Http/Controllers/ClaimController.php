<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Storage;
use App\Models\ClaimDocument;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Company;
use App\Models\Claim;
use App\Models\Report;
use App\Models\SurveyorReport;
use App\Models\ClaimItem;
use App\Models\ClaimActionLog;
use App\Models\Customer;
use App\Models\Insurer;
use App\Models\CustomerVehicle;
use App\Exports\ReportsExport;
use Auth;
use DB;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Log;
use Excel;

class ClaimController extends Controller
{

    private function permissionQuery($query)
    {
        if (Auth::user()->category == 'workshop') {
            $query = $query->where('claims.workshop_id', Auth::user()->company_id);
        } elseif (Auth::user()->category == 'surveyor') {
            $insurer = Insurer::where('surveyor_id', Auth::user()->company_id)->first();
            $query = $query->where('claims.insurer_id', $insurer->insurer_id);
        } elseif (Auth::user()->category == 'insurer') {
            $query = $query->where('claims.insurer_id', Auth::user()->company_id);
        }
        return $query;
    }

    public function activeClaims(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Claim::eloquentQuery($orderBy, $orderByDir, $searchValue, [
            "vehicle", "workshop", "insurerExtend", "items"
        ])
            ->where('claims.status', '!=', 'draft')
            ->where('claims.status', '!=', 'archive');

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function claims($status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        /* if($request->status == 'active'){
            $query = Claim::eloquentQuery($orderBy, $orderByDir, $searchValue,[
                "vehicles"
            ])
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'archive');
        } else {*/
        $query = Claim::eloquentQuery($orderBy, $orderByDir, $searchValue, [
            "vehicle", "workshop", "insurerExtend", "items"
        ])->where('claims.status', substr($status, 0, -1));
        //}

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function durationClaims($from_date, $to_date, $status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        /*if($request->status == 'active'){
            $query = Claim::eloquentQuery($orderBy, $orderByDir, $searchValue,[
                "vehicles",
            ])
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'archive');
        } else {*/

        $query = Claim::eloquentQuery($orderBy, $orderByDir, null, [
            "vehicle", "workshop", "insurerExtend", "items"
        ])->where('claims.status', $status);
        //}

        $query = $query->whereBetween('claims.created_at', [$from_date, $to_date]);

        $query = $query->where(function ($q) use ($searchValue) {
            $q->orWhere('ref_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.registration_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('companies.name', 'like', '%' . $searchValue . '%');
            $q->orWhere('policy_certificate_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('total_claim_amount', 'like', '%' . $searchValue . '%');
        });

        if ($status == 'approved') {
            $query = $query->orWhere('claims.status', 'repair_in_progress');
        }

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function reports(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        if ($request->role == 'surveyor') {
            $query = SurveyorReport::eloquentQuery($orderBy, $orderByDir, $searchValue, [
                "vehicle", "company"
            ]);
        } else {
            $query = Report::eloquentQuery($orderBy, $orderByDir, $searchValue, [
                "vehicle", "company", "items"
            ]);
        }
        if ($request->type != '') {
            if ($request->fromDate != '') {
                $query = $query->where('claims.' . $request->type, '>=', $request->fromDate);
            }
            if ($request->toDate != '') {
                $query = $query->where('claims.' . $request->type, '<=', $request->toDate);
            }
        }
        if ($request->status != '') {
            $query = $query->where('claims.status', $request->status);
        }
        if ($searchValue != '') {
            $replace = str_replace(" ", "_", $searchValue);
            $query = $query->where('claims.status', 'like', '%' . $replace . '%');
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
            'role' => 'required|in:surveyor,rest',
            'type' => 'required|in:created_at,approved_at,repaired_at'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        return Excel::download(new ReportsExport($request->role, $request->type, $request->fromDate, $request->toDate, $request->status), 'reports.xlsx');
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

        $count = DB::table('claims')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status');

        $count = $count->whereBetween('created_at', [$request->from_date, $request->to_date]);

        $cout = $this->permissionQuery($count);
        $count = $count->get();
        $action = 0;
        $user = Auth::user();
        foreach ($count as $temp) {
            if ($user->category == 'all_cars') {
                $action += $temp->total;
            } else if ($user->category == 'workshop') {
                if ($temp->status == 'draft' || $temp->status == 'repair_in_progress'  || $temp->status == 'approved' || $temp->status == 'pending_payment') {
                    $action += $temp->total;
                }
            } else if ($user->category == 'insurer') {
                if ($temp->status == 'insurer_review') {
                    $action += $temp->total;
                }
            } else if ($user->category == 'surveyor') {
                if ($temp->status == 'surveyor_review') {
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
            'make' => 'required|max:255',
            'model' => 'required|max:255',
            'mileage' => 'required|integer',
            'nric_uen' => 'required|max:255',
            'policy_name' => 'required|max:255',
            'policy_certificate_no' => 'required|max:255',
            'policy_coverage_from' => 'required|date',
            'policy_coverage_to' => 'required|date',
            'policy_nric_uen' => 'required|max:255',
            'date_of_notification' => 'required|date',
            'date_of_loss' => 'required|date',
            'cause_of_damage' => 'required|max:10000',
            'items' => 'required|array',
            'insurer_id' => 'required|exists:companies,id',
            'remarks' => 'nullable|max:10000',
            'documents' => 'required|array',
            'id' => 'exists:claims,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        if (isset($request->id)) {
            // Edit Must Be Draft
            $claim = Claim::where('id', $request->id)->first();
            if ($claim->status != 'draft') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            $claim = new Claim;
        }

        try {
            DB::transaction(function () use ($request, $claim) {

                if (isset($request->id)) {
                    $vehicle = Vehicle::where('id', $claim->vehicle_id)->first();
                    ClaimDocument::where('claim_id', $claim->id)->delete();
                    ClaimItem::where('claim_id', $claim->id)->delete();
                } else {
                    $claim->ref_no = Str::random(10);
                    $vehicle = new Vehicle;
                }

                $vehicle->chassis_no = $request->chassis_no;
                $vehicle->registration_no = $request->registration_no;
                $vehicle->make = $request->make;
                $vehicle->model = $request->model;
                $vehicle->mileage = $request->mileage;
                $vehicle->nric_uen = $request->nric_uen;
                $vehicle->save();

                $customer = Customer::where('nric_uen', $vehicle->nric_uen)->first();
                if ($customer != null) {
                    $vehicle_driver = new CustomerVehicle;
                    $vehicle_driver->customer_id = $customer->id;
                    $vehicle_driver->vehicle_id = $vehicle->id;
                    $vehicle_driver->save();
                }

                //if($request->total_claim_amount < 2000 && $claim->above_2k == 0){
                $claim->status = "allCars_review";
                //} else {
                //    $claim->status = "surveyor_review";
                //    $claim->above_2k = 1;
                //}

                // TODO:: determine insurer's company ID through cert
                //$insurer = Company::where('type', 'insurer')->first();
                $claim->insurer_id = $request->insurer_id;
                $claim->creator_id = Auth::user()->id;
                $claim->vehicle_id = $vehicle->id;
                $claim->workshop_id = Auth::user()->company_id;
                $claim->policy_name = $request->policy_name;
                $claim->policy_certificate_no = $request->policy_certificate_no;
                $claim->policy_coverage_from = $request->policy_coverage_from;
                $claim->policy_coverage_to = $request->policy_coverage_to;
                $claim->policy_nric_uen = $request->policy_nric_uen;
                $claim->mileage = $request->mileage;
                $claim->date_of_notification = $request->date_of_notification;
                $claim->date_of_loss = $request->date_of_loss;
                $claim->cause_of_damage = $request->cause_of_damage;
                $claim->total_claim_amount = 0;
                $claim->remarks = $request->remarks;
                $claim->save();

                $total_claim_amount = 0;
                foreach ($request->items as $item) {
                    ClaimItem::create([
                        'claim_id' => $claim->id,
                        'item_id' => $item['item_id'],
                        'item' => $item['item'],
                        'amount' => $item['amount'],
                        'recommended' => $item['recommended'],
                        'type' => $item['type']
                    ]);
                    $total_claim_amount += $item['amount'];
                }
                $claim->total_claim_amount = $total_claim_amount;
                $claim->save();

                foreach ($request->documents as $document) {
                    ClaimDocument::create([
                        'claim_id' => $claim->id,
                        'name' => $document['name'],
                        'url' => $document['url'],
                        'type' => $document['type']
                    ]);
                }

                ClaimActionLog::create([
                    'claim_id' => $claim->id,
                    'log' => 'Claim ' . $claim->ref_no . ' with status ' . unslugify($claim->status) . ' created by ' . Auth::user()->name . '.',
                    'status' => $claim->status,
                    'user_id' => Auth::user()->id
                ]);
                if ($claim->status == "allCars_review") {
                    // ALL CARS
                    email($claim, 'emails.claims.create', $claim->ref_no . ' – Claim Submitted By ' . $claim->workshop->name, null, null);
                } else {
                    // SURVEYOR TO MATCH CERT NO
                    $insurer = Insurer::where('insurer_id', $claim->insurer_id)->first();
                    email($claim, 'emails.claims.create', $claim->ref_no . ' – Claim Submitted By ' . $claim->workshop->name, null, $insurer->surveyor_id);
                }
            });
            DB::commit();
            return response()->json(['message' => "Claim created successfully."], 201);
        } catch (\Exception $e) {
            Log::debug($e);
            DB::rollback();
            return response()->json(['message' => "Failed to create claim."], 422);
        }
    }

    public function archive(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'draft') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        $claim->status = 'archive';
        $claim->save();

        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' archived by ' . Auth::user()->name . '.',
            'status' => 'archive',
            'user_id' => Auth::user()->id
        ]);

        return response()->json(['message' => "Claim archived successfully."], 200);
    }

    public function draft(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'registration_no' => 'nullable|max:255',
            'chassis_no' => 'nullable|max:255',
            'make' => 'nullable|max:255',
            'model' => 'nullable|max:255',
            'mileage' => 'nullable|integer',
            'nric_uen' => 'nullable|max:255',
            'policy_name' => 'nullable|max:255',
            'policy_certificate_no' => 'nullable|max:255',
            'policy_coverage_from' => 'nullable|date',
            'policy_coverage_to' => 'nullable|date',
            'policy_nric_uen' => 'nullable|max:255',
            'date_of_notification' => 'nullable|date',
            'date_of_loss' => 'nullable|date',
            'cause_of_damage' => 'nullable|max:10000',
            'insurer_id' => 'nullable|exists:companies,id',
            'items' => 'array',
            'documents' => 'array',
            'remarks' => 'nullable|max:10000',
            'id' => 'nullable|exists:claims,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        if (isset($request->id)) {
            $claim = Claim::where('id', $request->id)->first();
            if ($claim->status != 'draft') {
                return response()->json(['message' => 'Wrong status.'], 422);
            }
        } else {
            $claim = new Claim;
        }

        try {
            DB::transaction(function () use ($request, $claim) {
                if (isset($request->id)) {
                    $vehicle = Vehicle::where('id', $claim->vehicle_id)->first();
                    ClaimDocument::where('claim_id', $claim->id)->delete();
                    ClaimItem::where('claim_id', $claim->id)->delete();
                } else {
                    $claim = new Claim;
                    $claim->ref_no = Str::random(10);
                    $vehicle = new Vehicle;
                }

                $vehicle->chassis_no = $request->chassis_no;
                $vehicle->registration_no = $request->registration_no;
                $vehicle->make = $request->make;
                $vehicle->model = $request->model;
                $vehicle->mileage = $request->mileage;
                $vehicle->nric_uen = $request->nric_uen;
                $vehicle->save();

                $claim->insurer_id = $request->insurer_id;
                $claim->creator_id = Auth::user()->id;
                $claim->vehicle_id = $vehicle->id;
                $claim->workshop_id = Auth::user()->company_id;
                $claim->policy_name = $request->policy_name;
                $claim->policy_certificate_no = $request->policy_certificate_no;
                $claim->policy_coverage_from = $request->policy_coverage_from;
                $claim->policy_coverage_to = $request->policy_coverage_to;
                $claim->policy_nric_uen = $request->policy_nric_uen;
                $claim->date_of_notification = $request->date_of_notification;
                $claim->date_of_loss = $request->date_of_loss;
                $claim->mileage = $request->mileage;
                $claim->cause_of_damage = $request->cause_of_damage;
                $claim->total_claim_amount = 0;
                $claim->remarks = $request->remarks;
                $claim->status = 'draft';
                $claim->save();

                $total_claim_amount = 0;
                foreach ($request->items as $item) {
                    ClaimItem::create([
                        'claim_id' => $claim->id,
                        'item_id' => $item['item_id'],
                        'item' => $item['item'],
                        'amount' => $item['amount'],
                        'recommended' => $item['recommended'],
                        'type' => $item['type']
                    ]);
                    $total_claim_amount += $item['amount'];
                }
                $claim->total_claim_amount = $total_claim_amount;
                $claim->save();

                foreach ($request->documents as $document) {
                    ClaimDocument::create([
                        'claim_id' => $claim->id,
                        'name' => $document['name'],
                        'url' => $document['url'],
                        'type' => $document['type']
                    ]);
                }

                ClaimActionLog::create([
                    'claim_id' => $claim->id,
                    'log' => 'Claim ' . $claim->ref_no . ' drafted by ' . Auth::user()->name . '.',
                    'status' => $claim->status,
                    'user_id' => Auth::user()->id
                ]);
            });
            DB::commit();
            return response()->json(['message' => "Claim drafted successfully."], 200);
        } catch (\Exception $e) {
            Log::debug($e);
            DB::rollback();
            if ($e->getCode() == 123) {
                return response()->json(['message' => $e->getMessage()], 422);
            } else {
                return response()->json(['message' => "Failed to draft claim."], 422);
            }
        }
    }

    public function claim(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::with(['vehicle', 'workshop', 'documents', 'items', "insurerExtend"])->where('id', $request->id)->first();

        return response()->json(['claim' => $claim], 200);
    }

    public function history($id, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        // TODO :: get logs if they have access to claims
        $query = ClaimActionLog::eloquentQuery($orderBy, $orderByDir, $searchValue)->where('claim_id', $id);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    private function updateRecommended($claim, $request)
    {
        $amount = 0;
        foreach ($request->items as $item) {
            $row = ClaimItem::where('id', $item['id'])->update([
                'recommended' => $item['recommended'],
            ]);
            if ($row == 1) {
                ClaimActionLog::create([
                    'claim_id' => $claim->id,
                    'log' => 'Claim Item ' . $item['item'] . ' $' . $item['recommended'] . ' recommended by ' . Auth::user()->name . '.',
                    'user_id' => Auth::user()->id
                ]);
            }
            $amount += $item['recommended'];
        }

        $claim->remarks = $request->remarks;
        $claim->save();
    }

    public function approve(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'allcars_remarks' => 'nullable|max:10000',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'allCars_review') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }
        $claim->status = 'approved';
        $claim->allcars_remarks = $request->allcars_remarks;
        $claim->approved_at = Carbon::now();
        $claim->save();

        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from AllCars review to Approved by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.approve_claim_to_workshop', $claim->ref_no . ' – Claim Approved, Please Proceed With Repairs', null, $claim->workshop_id);
        email($claim, 'emails.claims.approve_claim_to_insurer', $claim->ref_no . ' – Claim Approved', null, $claim->insurer_id);
        return response()->json(['message' => 'Claim has been approved.'], 200);
    }

    // TODO:: Check permission if can touch claim
    public function reject(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'items' => 'required|array',
            'remarks' => 'max:10000',
            'allcars_remarks' => 'nullable|max:10000',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'allCars_review') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        foreach ($request->items as $item) {
            if (!is_numeric($item['recommended'])) {
                return response()->json(['message' => 'Recommended amount must be numeric.'], 422);
            }
        }
        $claim->allcars_remarks = $request->allcars_remarks;
        $claim->status = 'draft';
        $claim->save();

        $this->updateRecommended($claim, $request);
        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from AllCars Review to Draft by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.reject_claim', $claim->ref_no . ' – Claim Amendment Recommendations', null, $claim->workshop_id);
        return response()->json(['message' => 'Claim has been rejected.'], 200);
    }

    public function sendInsurer(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'items' => 'required|array',
            'allcars_remarks' => 'nullable|max:10000',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'allCars_review') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        foreach ($request->items as $item) {
            if (!is_numeric($item['recommended'])) {
                return response()->json(['message' => 'Recommended amount must be numeric.'], 422);
            }
        }
        $claim->allcars_remarks = $request->allcars_remarks;
        $claim->status = 'insurer_review';
        $claim->save();

        $this->updateRecommended($claim, $request);
        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from AllCars Review to Insurer Review by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.approval_from_insurer', $claim->ref_no . ' – Please Review Claim', null, $claim->insurer_id);
        return response()->json(['message' => 'Claim has been submitted for insurer approval.'], 200);
    }

    // TODO:: Check permission if can touch claim
    public function surveyorApprove(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'surveyor_review') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }
        $claim->status = 'insurer_approval';
        $claim->approved_at = Carbon::now();
        $claim->surveyor_review_count = $claim->surveyor_review_count + 1;
        $claim->save();

        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from Surveyor Review to Insurer Approval by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        //email($claim, 'emails.claims.surveyor_approve', $claim->ref_no.' – Claim Approved By '.$claim->insurer->details->surveyor->name, null, null);
        email($claim, 'emails.claims.surveyor_approve', $claim->ref_no . ' – Claim Approved By ' . $claim->insurer->details->surveyor->name, null, $claim->insurer_id);
        //email($claim, 'emails.claims.approve_claim_to_workshop', $claim->ref_no.' – Claim Approved, Please Proceed With Repairs', null, $claim->workshop_id);
        return response()->json(['message' => 'Claim has been approved.'], 200);
    }

    public function surveyorReject(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'items' => 'required|array',
            'remarks' => 'max:10000'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'surveyor_review') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        foreach ($request->items as $item) {
            if (!is_numeric($item['recommended'])) {
                return response()->json(['message' => 'Recommended amount must be numeric.'], 422);
            }
        }
        $claim->status = 'draft';
        $claim->save();

        $this->updateRecommended($claim, $request);

        $claim->surveyor_review_count = $claim->surveyor_review_count + 1;
        $claim->save();
        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from Surveyor Review to Draft by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.surveyor_reject', $claim->ref_no . ' – Amendment Recommendations By ' . $claim->insurer->details->surveyor->name, null, $claim->workshop_id);
        return response()->json(['message' => 'Claim has been rejected.'], 200);
    }

    // TODO:: Check permission if can touch claim
    public function insurerApprove(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'insurer_review' && $claim->status != 'insurer_approval') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }
        $old_status = $claim->status;
        $claim->status = 'approved';
        $claim->save();

        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from ' . unslugify($claim->status) . ' to Approved by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.insurer_approve', $claim->ref_no . ' – Claim Approved By ' . $claim->insurer->name, null, null);
        email($claim, 'emails.claims.approve_claim_to_workshop', $claim->ref_no . ' – Claim Approved, Please Proceed With Repairs', null, $claim->workshop_id);

        return response()->json(['message' => 'Claim has been approved.'], 200);
    }

    public function insurerReject(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'items' => 'required|array',
            'remarks' => 'max:10000'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'insurer_review') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        foreach ($request->items as $item) {
            if (!is_numeric($item['recommended'])) {
                return response()->json(['message' => 'Recommended amount must be numeric.'], 422);
            }
        }
        $claim->status = 'draft';
        $claim->save();

        $this->updateRecommended($claim, $request);
        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from Insurer Review to Draft by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.insurer_reject', $claim->ref_no . ' – Amendment Recommendations By ' . $claim->insurer->name, null, $claim->workshop_id);
        return response()->json(['message' => 'Claim has been rejected.'], 200);
    }

    public function sendSurveyor(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'items' => 'required|array',
            'remarks' => 'max:10000'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'insurer_review') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        foreach ($request->items as $item) {
            if (!is_numeric($item['recommended'])) {
                return response()->json(['message' => 'Recommended amount must be numeric.'], 422);
            }
        }
        $claim->status = 'surveyor_review';
        $claim->save();

        $this->updateRecommended($claim, $request);
        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from Insurer Review to Surveyor Review by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        // 1 insurer can only have 1 surveyor...
        $insurer = Insurer::where('insurer_id', $claim->insurer_id)->first();
        email($claim, 'emails.claims.approval_from_surveyor', $claim->ref_no . ' – Please Review Claim', null, $insurer->surveyor_id);
        return response()->json(['message' => 'Claim has been submitted for surveyor approval.'], 200);
    }

    public function workshopApprove(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'documents' => 'required|array'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'approved' && $claim->status != 'repair_in_progress') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        ClaimDocument::where('claim_id', $request->id)
            ->where(function ($query) {
                $query->orWhere('type', '=', 'supplier');
                $query->orWhere('type', '=', 'note');
                $query->orWhere('type', '=', 'tax');
            })
            ->delete();

        foreach ($request->documents as $document) {
            ClaimDocument::create([
                'claim_id' => $request->id,
                'name' => $document['name'],
                // 'url' => Storage::disk('s3')->url($document['url']),
                'url' => $document['url'],
                'type' => $document['type']
            ]);
        }
        $claim->status = 'doc_verification';
        $claim->repaired_at = Carbon::now();
        $claim->save();

        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from Approved to Doc Verification by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.workshop_approve', $claim->ref_no . ' – Repair Works Completed By ' . $claim->workshop->name, null, null);
        return response()->json(['message' => 'Claim has been completed.'], 200);
    }

    public function workshopReject(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'approved' || $claim->status != 'repair_in_progress') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }
        $claim->status = 'draft';
        $claim->save();

        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from Approved to Draft by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.workshop_reject', $claim->ref_no . ' – ' . $claim->workshop->name . ' Has Cancelled Claim', null, null);
        return response()->json(['message' => 'Claim has been rejected.'], 200);
    }

    public function approveDocuments(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'insurer_ref_no' => 'required|unique:claims',
            'allcars_remarks' => 'nullable|max:10000',
            'remarks' => 'nullable|max:10000',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'doc_verification') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }

        $claim->insurer_ref_no = $request->insurer_ref_no;
        $claim->remarks = $request->remarks;
        $claim->allcars_remarks = $request->allcars_remarks;
        $claim->status = 'pending_payment';
        $claim->save();

        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from Doc Verification to Pending Payment by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.approve_documents', $claim->ref_no . ' – Claim Has Been Completed', null, $claim->insurer_id);
        return response()->json(['message' => 'Claim has been approved.'], 200);
    }

    public function rejectDocuments(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'insurer_ref_no' => 'nullable|unique:claims',
            'allcars_remarks' => 'nullable|max:10000',
            'remarks' => 'nullable|max:10000',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'doc_verification') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }
        $claim->insurer_ref_no = $request->insurer_ref_no;
        $claim->allcars_remarks = $request->allcars_remarks;
        $claim->remarks = $request->remarks;
        $claim->status = 'approved';
        $claim->save();

        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => 'Claim ' . $claim->ref_no . ' status changed from Doc Verification to Approved by ' . Auth::user()->name . '.',
            'status' => $claim->status,
            'user_id' => Auth::user()->id
        ]);

        email($claim, 'emails.claims.reject_documents', $claim->ref_no . ' – Please Revise Claim Completion Documents', null, $claim->workshop_id);
        return response()->json(['message' => 'Claim has been rejected.'], 200);
    }

    public function completed(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:claims,id',
            'allcars_to_workshop_payment' => 'in:0,1',
            'remarks' => 'nullable|max:10000',
            'allcars_remarks' => 'nullable|max:10000',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $claim = Claim::where('id', $request->id)->first();
        if ($claim->status != 'pending_payment') {
            return response()->json(['message' => 'Wrong status.'], 422);
        }
        if (Auth::user()->category != 'all_cars') {
            $claim->allcars_to_workshop_payment = $request->allcars_to_workshop_payment;
            if ($claim->allcars_to_workshop_payment == 1) {
                $log = 'Claim ' . $claim->ref_no . ' Claim payment to workshop set to received payment by ' . Auth::user()->name . '.';
                email($claim, 'emails.claims.workshop_payment', $claim->ref_no . ' – Pending Payment Acknowledged By ' . $claim->workshop->name, null, null);
            } else {
                $log = 'Claim ' . $claim->ref_no . ' Claim payment to workshop set to pending payment by ' . Auth::user()->name . '.';
            }
        }
        ClaimActionLog::create([
            'claim_id' => $claim->id,
            'log' => $log,
            'user_id' => Auth::user()->id
        ]);
        if ($claim->allcars_to_workshop_payment == 1) {
            $claim->status = 'completed';
            ClaimActionLog::create([
                'claim_id' => $claim->id,
                'log' => 'Claim ' . $claim->ref_no . ' status changed from Pending Payment to Completed by ' . Auth::user()->name . '.',
                'status' => $claim->status,
                'user_id' => Auth::user()->id
            ]);
        }
        $claim->remarks = $request->remarks;
        $claim->save();

        //email($claim, 'emails.claims.complete', 'support_staff', $claim->workshop_id);
        return response()->json(['message' => 'Claim has been updated.'], 200);
    }
}
