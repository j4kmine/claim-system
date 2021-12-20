<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\ServicingActionLog;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\ServicingSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ServicingAppointment;
use App\Models\WarrantyPrice;
use App\Services\CheckServicingSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Symfony\Component\HttpFoundation\Response;

class ServicingController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $attr = $request->validate([
            'workshop_id' => 'nullable|exists:companies,id',
            'group_by' => 'nullable|in:appointment_date',
            'desc' => 'nullable',
            'asc' => 'nullable',
        ]);

        $length = $request->input('length');
        // $page = $request->input('page');
        // if ($request->session()->has('page')){
        //     $request->merge([
        //         'page' => $request->session()->get('page')
        //     ]);
        // }else{
        //     $request->session()->put('page', $page);
        // }
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        $searchValue = $request->input('search');
        $servicing = Service::eloquentQuery($sortBy, $orderBy);


        /**
         * Order by status
         * or filter by status
         */
        $servicing = $servicing->filterBy($request->all());

        $servicing = $servicing
            ->with('workshop:id,name')
            ->with('appointments')
            ->with('service_type:id,name,color')
            ->with('customer:id,name,phone')
            ->with('vehicle:id,registration_no,make');
        $servicing = $servicing->where(function ($query) use ($searchValue) {
            $query->orWhere('services.appointment_date', 'like', '%' . $searchValue . '%');
            $query->orWhere('companies.name', 'like', '%' . $searchValue . '%');
            $query->orWhere('service_types.name', 'like', '%' . $searchValue . '%');
            $query->orWhere('customers.name', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.registration_no', 'like', '%' . $searchValue . '%');
            $query->orWhere('vehicles.make', 'like', '%' . $searchValue . '%');
            $query->orWhere('services.status', 'like', '%' . $searchValue . '%');
        });
        // Filter by Workshop ID
        if (isset($attr['workshop_id']))
            $servicing = $servicing->where('services.workshop_id', $attr['workshop_id']);

        // Filter by owned Workshop ID
        if (Auth::user()->company != null && Auth::user()->category == 'workshop')
            $servicing = $servicing->where('services.workshop_id', Auth::user()->company->id);

        if ($request->appointment_date) {
            $servicing = $servicing->where('services.appointment_date', 'like', '%' . date("Y-m-d", strtotime($request->appointment_date)) . '%');
        }
        if ($request->service_type_id) {
            $servicing = $servicing->whereServiceTypeId($request->service_type_id);
        }

        if ($request->workshop) {
            $servicing = $servicing->where('services.workshop_id', $request->workshop);
        }

        if ($request->is_calendar) {
            $servicing = $servicing->get();
        } else {
            $servicing = $servicing->paginate($length);
        }
        // Group by appointment date (for calendar use)
        if (isset($attr['group_by']))
            $servicing = $servicing->groupBy(function ($item) {
                return $item->appointment_date->format('Y-m-d');
            });


        return new DataTableCollectionResource($servicing);
    }

    public function show(Request $request, Service $service)
    {
        $servicing = Service::with('workshop:id,name')
            ->with('appointments')
            ->with('service_type:id,name')
            ->with('servicing_reports.documents')
            ->with('servicing_reports.invoices')
            ->with('customer:id,name,phone,nric_uen,email')
            ->with('vehicle:id,registration_no,make,model')
            ->find($service->id);

        return $this->success($servicing);
    }

    public function update(Request $request, Service $service)
    {
        $attr = $request->validate([
            'status' => 'required|in:' . implode(',', Service::STATUSES)
        ]);

        $service->update($attr);

        return $this->success($service);
    }

    public function store(Request $request)
    {
        $attr = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'workshop_id' => 'required|exists:companies,id',
            'service_type_id' => 'required|exists:service_types,id',
            'customer_name' => 'required',
            'nric' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'vehicle_number' => 'required',
            'vehicle_make' => 'required',
            'vehicle_model' => 'required',
            'remarks' => 'nullable',
        ]);

        $customer = Customer::where('nric_uen', $attr['nric'])->first();

        if (!$customer) {
            $customer = Customer::create([
                'password' => bcrypt('carfren!234'),
                'status' => 'active',
                'nric_uen' => $attr['nric'],
                'name' => $attr['customer_name'],
                'phone' => $attr['phone'],
                'email' => $attr['email']
            ]);
        }

        $vehicle = Vehicle::where('registration_no', $attr['vehicle_number'])->first();
        if (!$vehicle) {
            $vehicle = Vehicle::create([
                'registration_no' => $attr['vehicle_number'],
                'make' => $attr['vehicle_make'],
                'model' => $attr['vehicle_model']
            ]);
        }

        $customer->vehicles()->attach($vehicle);

        $company = Company::find($attr['workshop_id']);
        $appointment = Carbon::createFromFormat(
            'Y-m-d H:i',
            $attr['date'] . ' ' . $attr['time']
        );

        // Check if servicing slot is available
        $check = new CheckServicingSlot();
        $is_web = true;
        [$result, $message] = $check->handle($company, $appointment, $is_web);
        if (!$result) {
            $data['message'] = $message;
            return $this->errors($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slot = ServicingSlot::where('workshop_id', $company->id)
            ->where('day', $appointment->format('l'))
            ->where('status', 'active')
            ->first();

        /**
         * Store the record
         */
        try {
            DB::beginTransaction();

            $service = Service::create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'servicing_slot_id' => $slot->id,
                'workshop_id' => $company->id,
                'service_type_id' => $attr['service_type_id'],
                'appointment_date' => $appointment->format('Y-m-d H:i'),
                'remarks' => $attr['remarks'],
                'status' => 'upcoming'
            ]);

            ServicingAppointment::create([
                'servicing_slot_id' => $slot->id,
                'service_id' => $service->id,
                'appointment_date' => $appointment->format('Y-m-d'),
                'time_start' => $appointment->format('H:i'),
                'interval' => $slot->interval
            ]);

            ServicingActionLog::create([
                'service_id' => $service->id,
                'log' => 'Service ' . $service->ref_no . ' with status ' . unslugify($service->status) . ' created by ' . Auth::user()->name . '.',
                'status' => $service->status,
                'user_id' => Auth::user()->id
            ]);

            DB::commit();
            return $this->success($service, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            DB::rollback();
            Log::error([
                'ServicingController.store',
                $e->getMessage(),
                $request->all()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function carModel()
    {
        $models = WarrantyPrice::pluck('model')->all();

        return $this->success($models);
    }

    public function carMake()
    {
        $models = WarrantyPrice::pluck('make')->all();

        return $this->success($models);
    }
}
