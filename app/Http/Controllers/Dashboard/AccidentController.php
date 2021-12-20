<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Reports;
use Illuminate\Http\Request;
use App\Models\AccidentActionLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class AccidentController extends Controller
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

        $count = DB::table('reports')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status');

        $count = $count->whereBetween('created_at', [$request->from_date, $request->to_date]);

        // $cout = $this->permissionQuery($count);
        $count = $count->get();
        $action = 0;
        $user = Auth::user();
        foreach ($count as $temp) {
            if ($user->category == 'all_cars' || $user->category == 'workshop') {
                if (
                    $temp->status == 'pending_enquiry'
                    || $temp->status == 'pending_admin_review'
                    || $temp->status == 'pending'
                    || $temp->status == 'completed'
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

    public function accident(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:reports,id'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $accident = Reports::with(
            ['driver', 'vehicle', 'workshop', 'documents']
        )->where('id', $request->id)->first();
        return response()->json(['accident' => $accident], 200);
    }

    public function history($id, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        // TODO :: get logs if they have access to claims
        $query = AccidentActionLog::eloquentQuery($orderBy, $orderByDir, $searchValue)->where('accident_id', $id);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function accidents($status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Reports::eloquentQuery($orderBy, $orderByDir, $searchValue, [
            'driver', 'vehicle', 'workshop', 'documents', 'customer'
        ])->where('reports.status', $status);

        // $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function activeAccidents(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Reports::eloquentQuery($orderBy, $orderByDir, $searchValue, [
            'driver', 'vehicle', 'workshop', 'documents', 'customer'
        ])
            ->where('reports.status', '!=', 'draft')
            ->where('reports.status', '!=', 'archive');

        // $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }

    public function durationAccidents($from_date, $to_date, $status, Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');

        $query = Reports::eloquentQuery($orderBy, $orderByDir, null, [
            'driver', 'vehicle', 'workshop', 'documents', 'customer'
        ])->where('reports.status', $status);

        $query = $query->whereBetween('reports.created_at', [$from_date, $to_date]);

        $query = $query->where(function ($q) use ($searchValue) {
            $q->orWhere('ref_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.registration_no', 'like', '%' . $searchValue . '%');
            $q->orWhere('customers.name', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.make', 'like', '%' . $searchValue . '%');
            $q->orWhere('vehicles.model', 'like', '%' . $searchValue . '%');
            $q->orWhere('reports.status', 'like', '%' . $searchValue . '%');
            $q->orWhere('reports.created_at', 'like', '%' . $searchValue . '%');
        });

        // $query = $this->permissionQuery($query);
        $data = $query->paginate($length);

        return new DataTableCollectionResource($data);
    }
}
