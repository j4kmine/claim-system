<?php

namespace App\Http\Controllers;

use App\Models\Reports;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class AccidentController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {

        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        $searchValue = $request->input('search');
        

        $reports = Reports::eloquentQuery($sortBy, $orderBy, [])
            ->with('vehicle:id,registration_no,make,model')
            ->with('customer:id,name,phone')
            ->with('workshop:id,name');
        $reports = $reports->where(function($q) use ($searchValue){
            $q->orWhere('ref_no','like','%'.$searchValue.'%');
            $q->orWhere('vehicles.registration_no','like','%'.$searchValue.'%');
            $q->orWhere('customers.name','like','%'.$searchValue.'%');
            $q->orWhere('vehicles.make','like','%'.$searchValue.'%');
            $q->orWhere('vehicles.model','like','%'.$searchValue.'%');
            $q->orWhere('companies.name','like','%'.$searchValue.'%');
            $q->orWhere('reports.status','like','%'.$searchValue.'%');
            $q->orWhere('reports.created_at','like','%'.$searchValue.'%');
        });
        $reports = $reports->paginate($length);
        return new DataTableCollectionResource($reports);
    }

    public function show(Reports $accident)
    {

        $accident = Reports::with('vehicle')
            ->with('customer')
            ->with('workshop')
            ->with('driver')
            ->with('vehicle_involves.driver')
            ->with('documents')
            ->with('reports.documents')
            ->find($accident->id);
              if($accident->details == ""){
                $accident->details = "-";
            }
              if($accident->owner_driver_relationship == ""){
                $accident->owner_driver_relationship = "-";
            }
             if($accident->certificate_number == ""){
                $accident->certificate_number = "-";
            }
            if($accident->driver_address == ""){
                $accident->driver_address = "-";
            }
             if($accident->driver_email == ""){
                $accident->driver_email = "-";
            }
              if($accident->driver_contact_no == ""){
                $accident->driver_contact_no = "-";
            }
        return $this->success($accident);
    }
}
