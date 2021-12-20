<?php

namespace App\Http\Controllers\Mobile\Service;

use Throwable;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Service;
use App\Models\Customer;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\ServicingSlot;
use App\Activities\BookService;
use App\Traits\AttributeModifier;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ServicingAppointment;
use App\Models\ServicingReportDocument;
use App\Models\ServicingReportInvoice;
use App\Models\ServicingReport;
use App\Services\CheckServicingSlot;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Log;

class ServiceController extends Controller
{
    use ApiResponser, AttributeModifier;

    public function index(Request $request)
    {
        $customer = Auth::user();

        // Service query
        $service = $customer->services();

        
        // Need a cron job to reset upcoming to completed
        // Filter based on parameters

        $service->with('workshop')
            ->with('service_type');

        if($request->registration != null){
            $service = $service->with('vehicle')->whereHas('vehicle', function($q) use ($request) {
                $q->where('registration_no', '=', $request->registration);
            })->orderBy('services.id', 'desc');
        } else {
            $service = $service->with('vehicle')->orderBy('services.id', 'desc');
        }

        if($request->status != null){
            $service = $service->where('status', $request->status);
        }
        if($request->limit != null){
            return $this->success($service->limit($request->limit)->get());
        } else {
            return $this->success($service->get());
        }
    }

    public function show($service)
    {
        $service = Service::with('workshop')
            ->with('vehicle')
            ->with('service_type')
            ->find($service);

        $this->authorize('view', $service);

        // Service documents
        $documents = [];
        $invoices = [];
        $report = ServicingReport::where('servicing_id', $service->id)->first();
        if($report != null){
            $documents = ServicingReportDocument::where('servicing_report_id', $report->id)->get();
            $invoices = ServicingReportInvoice::where('servicing_report_id', $report->id)->get();
           
        }

        // $documents = $service->documents;

        $service = $service->toArray();
        if(isset($report->total_amount) && $report->total_amount != ""){
        	$service['total_amount'] = $report->total_amount;
        }else{
        	$service['total_amount'] = 0;
        }
      
       	
        return $this->success(array_merge($service, [
            'documents' => $documents == null ? [] : $documents,
            'invoices' => $invoices == null ? [] : $invoices
        ]));
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'workshop_id' => 'required|exists:companies,id',
            'service_type_id' => 'required|exists:service_types,id',
            'appointment_date' => 'required|date_format:d/m/Y',
            'appointment_time' => 'required|date_format:H:i',
            'remarks' => 'nullable',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        $customer = Auth::user();
        $appointment = Carbon::createFromFormat(
            'd/m/Y H:i',
            $request->appointment_date . ' ' . $request->appointment_time
        );

        // If the appointment date lesser than today, reject the request
        if ($appointment->lt(now())) {
            return response()->json(['message' => 'Appointment date must be later than today.'], 422);
        }

        /**
         * Check the servicing slots
         * 1. Check if the timeslot exists
         * 2. Check if slot per interval not fully booked
         */
        $company = Company::find($request->workshop_id);

        $check = new CheckServicingSlot(); 
        
        [$result, $message] = $check->handle($company, $appointment);

        if (!$result)
            return response()->json(['message' => 'Time slot not available.'], 422);

        $slot = ServicingSlot::where('workshop_id', $request->workshop_id)
            ->where('day', $appointment->format('l'))
            ->where('status', 'active')->first();

        try {
            DB::beginTransaction();

            $service = Service::create([
                'vehicle_id' => $request->vehicle_id,
                'workshop_id' => $request->workshop_id,
                'service_type_id' => $request->service_type_id,
                'appointment_date' => $appointment,
                'servicing_slot_id' => $slot->id,
                'remarks' => $request->remarks,
                'customer_id' => $customer->id,
                'status' => 'upcoming'
            ]);
            /*
            ServicingAppointment::create([
                'servicing_slot_id' => $slot->id,
                'service_id' => $service->id,
                'appointment_date' => $appointment->format('Y-m-d'),
                'time_start' => $attr['appointment_time'],
                'interval' => $slot->interval
            ]);
            */
            DB::commit();

            // Create activities
            Auth::user()->notify(new BookService($service));
            $service = Service::with('workshop')
                        ->with('vehicle')
                        ->with('service_type')
                        ->find($service->id);
            return $this->success($service, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            DB::rollback();
            return response()->json(['message' => 'An error has occured.'], 422);
        }
    }

    public function update(Request $request, Service $service)
    {
        $valid = Validator::make($request->all(), [
            'appointment_date' => 'required|date_format:d/m/Y',
            'appointment_time' => 'required|date_format:H:i',
            'remarks' => 'nullable',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        $customer = Auth::user();

        $this->authorize('view', $service);

        $appointment = Carbon::createFromFormat(
            'd/m/Y H:i',
            $request->appointment_date . ' ' . $request->appointment_time
        );


        if ($service->status != 'upcoming') {
            return response()->json(['message' => 'Appointment has expired.'], 422);
        }

        // If the appointment date lesser than today, reject the request
        if ($appointment->lt(now())) {
            return response()->json(['message' => 'Appointment date must be later than today.'], 422);
        }

        $company = Company::find($service->workshop_id);
        $check = new CheckServicingSlot();
        [$result, $message] = $check->handle($company, $appointment);

        if (!$result)
            return response()->json(['message' => 'Time slot not available.'], 422);

        $slot = ServicingSlot::where('workshop_id', $service->workshop_id)
            ->where('day', $appointment->format('l'))
            ->where('status', 'active')->first();
  		
        $attr = [];
        $attr['appointment_date'] = $appointment;
        $attr['status'] = 'upcoming';
        $attr['servicing_slot_id'] = $slot->id;
        $attr['rescheduled_count'] = $service->rescheduled_count + 1;
        $attr['remarks'] = $request->remarks;

        // Update service
        $service->update($attr);
        $service = Service::with('workshop')
                    ->with('vehicle')
                    ->with('service_type')
                    ->find($service->id);

        return $this->success($service);
    }

    public function cancel(Request $request, Service $service)
    {
        $customer = Auth::user();

        $this->authorize('view', $service);
        
        $service->status = 'cancelled';
        $service->save();
        $service = Service::with('workshop')
                    ->with('vehicle')
                    ->with('service_type')
                    ->find($service->id);

        return $this->success($service);
    }

    public function destroy(Request $request, Service $service)
    {
        $customer = Auth::user();

        // TODO: Check if user authorized to delete the resource

        // Delete the documents
        $service->documents()->delete();

        // Delete service
        $service->delete();
        return $this->success('Resource deleted');
    }
}
