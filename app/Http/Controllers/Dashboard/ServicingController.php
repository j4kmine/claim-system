<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Models\ServicingActionLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class ServicingController extends Controller
{
    public function count(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date'   => 'required|date'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $count = DB::table('services')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status');

        $count = $count->whereBetween('created_at', [$request->from_date, $request->to_date]);

        $cout = $this->permissionQuery($count);
        $count = $count->get();
        $action = 0;
        $user = Auth::user();
        foreach ($count as $temp) {
            if ($user->category == 'all_cars' || $user->category == 'workshop') {
                if (
                    $temp->status == 'pending_enquiry'
                    || $temp->status == 'pending_admin_review'
                    || $temp->status == 'upcoming'
                    || $temp->status == 'open'
                    || $temp->status == 'pending'
                    || $temp->status == 'completed'
                    || $temp->status == 'cancelled'
                ) {
                    $action += $temp->total;
                }
            } else if ($user->category == 'dealer') {
                if ($temp->status == 'draft' || $temp->status == 'pending_proposal') {
                    $action += $temp->total;
                }
            }
        }
        return response()->json(["count" => $count, "action" => $action], 200);
    }

    public function service(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:services,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $servicing = Service::with(
            ['workshop', 'vehicle', 'service_type', 'documents', 'customer', 'appointments', 'servicing_reports']
        )
            ->where('id', $request->id)->first();
        return response()->json(['servicing' => $servicing], 200);
    }

    public function history($id, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        // TODO :: get logs if they have access to claims
        $query = ServicingActionLog::eloquentQuery($orderBy, $orderByDir, $searchValue)->where('service_id', $id);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function services($status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Service::eloquentQuery($orderBy, $orderByDir, $searchValue, [
            "vehicle", "workshop", "customer", "service_type", "appointments"
        ])->where('services.status', $status);

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function activeServices(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        $query = Service::eloquentQuery($orderBy, $orderByDir, $searchValue, [
            "vehicle", "workshop", "customer", "service_type", "appointments"
        ])
            ->where('services.status', '!=', 'draft')
            ->where('services.status', '!=', 'archive');
        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function durationServices($from_date, $to_date, $status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Service::eloquentQuery($orderBy, $orderByDir, null, [
            "vehicle", "workshop", "customer", "service_type", "appointments"
        ])->where('services.status', $status);

        $query = $query->whereBetween('services.created_at', [$from_date, $to_date]);

        $query = $query->where(function ($q) use ($searchValue) {
            $q->orWhere('ref_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.registration_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('customers.name', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.make', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.model', 'like', '%' . $searchValue . '%');
            $q->orWhere('services.status', 'like', '%' . $searchValue . '%');
            $q->orWhere('services.created_at', 'like', '%' . $searchValue . '%');
        });

        $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    private function permissionQuery($query)
    {
        if (Auth::user()->category == 'workshop') {
            $query = $query->where('services.workshop_id', Auth::user()->company_id);
        }
        return $query;
    }
}
