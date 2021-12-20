<?php

namespace App\Http\Controllers\Mobile\Report;

use App\Activities\AccidentInspection;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ReportDocument;
use App\Models\Reports;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Validator;
use Str;
use Storage;
use Log;

class ReportController extends Controller
{
    use ApiResponser;

    public function vehicleInfo()
    {
        $customer = Auth::user();

        $vehicles = $customer->vehicles()->whereNotNull('vehicles.registration_no')->get();
        $arr = [];
        foreach($vehicles as $vehicle){
            $info = [];

            $motor = $vehicle->motor;
            if($motor != null){
                $insurer = $motor->insurer;
                $driver = $motor->driver;

                $info['vehicle_id'] = $vehicle->id;
                $info['registration_no'] = $vehicle->registration_no;
                $info['vehicle_make'] = $vehicle->make;
                $info['vehicle_model'] = $vehicle->model;
                
                $info['insurance_company'] = $insurer->name;
                $info['certificate_number'] = $motor->ci_no;
                $info['insured_nric'] = $driver->nric;
                $info['insured_name'] = $driver->name;
                $info['insured_contact_number'] = $driver->contact_number;

                $arr[] = $info;
            }
        }

        if(sizeof($arr) > 0){
            return $this->success($arr);
        } else {
            return response()->json(['message' => 'Vehicle does not have insurance.'], 422);
        }
    }
    
    public function index(Request $request)
    {
        $customer = Auth::user();

        $reports = $customer->reports();

        // Need a cron job to reset upcoming to completed
        // Filter based on parameters

        $reports->with('workshop');

        if($request->registration != null){
            $reports = $reports->with('vehicle')->whereHas('vehicle', function($q) use ($request) {
                $q->where('registration_no', '=', $request->registration);
            });
        } else {
            $reports = $reports->with('vehicle');
        }

        if($request->status != null){
            $reports = $reports->where('status', $request->status);
        }

        if($request->limit != null){
            return $this->success($reports->limit($request->limit)->get());
        } else {
            return $this->success($reports->get());
        }

        return $this->success($customer->reports);
    }

    public function show(Request $request, Reports $report)
    {
        $customer = Auth::user();

        // TODO: Check if customer authorized to this resources

        $report = Reports::with('workshop')->with('documents')->with('vehicle')->find($report->id);

        return $this->success($report);
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            // Basic Information / Required
            'vehicle_id' => 'required|exists:vehicles,id|exists:motors,vehicle_id',
            'vehicle_make' => 'required',
            'vehicle_model' => 'required',

            // Accident Info
            'date_of_accident' => 'required|date_format:d/m/Y',
            'time_of_accident' => 'required|date_format:H:i',
            'location_of_accident' => 'required|max:255',
            'weather_road_condition' => 'required|in:' . implode(',', Reports::WEATHER_ROAD_CONDITIONS),
            'reporting_type' => 'required|in:' . implode(',', Reports::REPORTING_TYPES),
            'number_of_passengers' => 'required|integer',
            'is_video_captured' => 'required|in:yes,no',
            'purpose_of_use' => 'required|in:' . implode(',', Reports::PURPOSE_OF_USE),

            // Driver Info
            'is_owner_drives' => 'required|in:yes,no',
            'is_other_vehicle' => 'required|in:yes,no',

            // Workshop & Visit date
            'is_visiting_workshop' => 'nullable|in:yes,no',
            'workshop_id' => 'nullable|exists:companies,id',
            'workshop_visit_date' => 'nullable|date_format:d/m/Y',
            'workshop_visit_time' => 'nullable|date_format:H:i',
            
            'accident_scene' => 'nullable|array',
            'vehicle_car_plate' => 'nullable|array',
            'close_range_damage' => 'nullable|array',
            'long_range_damage' => 'nullable|array',
            'other_driving_license' => 'nullable|array',
            'other_car_plate' => 'nullable|array',
            'other_close_range' => 'nullable|array',
            'other_long_range' => 'nullable|array'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        if($request->is_owner_drives == 'no'){

            $valid = Validator::make($request->all(), [
                'owner_driver_relationship' => 'required|in:' . implode(',', Reports::OWNER_DRIVER_RELATIONSHIPS),
                'driver_name' => 'required|max:255',
                'driver_nric' => 'required|regex:/^[STFG]\d{7}[A-Z]$/',
                'driver_dob' => 'required|date_format:d/m/Y',
                'driver_license' => 'required|date_format:d/m/Y',
                'driver_address' => 'required|max:255',
                'driver_contact_no' => 'required|numeric',
                'driver_email' => 'required|email',
                'driver_occupation' => 'required|in:indoor,outdoor',
            ]);

            if ($valid->fails()) {
                return response()->json($valid->errors()->toArray(), 422);
            }
        }
        
        if($request->is_other_vehicle == 'yes'){
            $valid = Validator::make($request->all(), [
                'other_vehicle_car_plate' => 'required|max:255',
                'other_vehicle_make' => 'required|max:255',
                'other_vehicle_model' => 'required|max:255',
                'other_driver_name' => 'required|max:255',
                'other_driver_nric' => 'required|regex:/^[STFG]\d{7}[A-Z]$/',
                'other_driver_contact_no' => 'required|numeric',
                'other_driver_address' => 'required|max:255'
            ]);
                
            if ($valid->fails()) {
                return response()->json($valid->errors()->toArray(), 422);
            }
        }

        // Parse date of accident
        if (isset($request->date_of_accident) && isset($request->time_of_accident)) {
            $date = Carbon::createFromFormat(
                'd/m/Y H:i',
                $request->date_of_accident . ' ' . $request->time_of_accident
            );
            $request->date_of_accident = $date;
            unset($request->time_of_accident);
        }

        // Date of Accident must not greater than today
        if (isset($request->date_of_accident) && $request->date_of_accident->gt(now())) {
            return response()->json(['message' => 'Date of accident is invalid.'], 422);
        }

        // Parse workshop visit date
        if (isset($request->workshop_visit_date) && isset($request->workshop_visit_time)) {
            $date = Carbon::createFromFormat(
                'd/m/Y H:i',
                $request->workshop_visit_date . ' ' . $request->workshop_visit_time
            );
            $request->workshop_visit_date = $date;
            unset($request->workshop_visit_time);
        } else {
            return response()->json(['message' => 'Date & time of workshop appointment is invalid.'], 422);
        }

        // Workshop visit date must greater than today
        if (isset($request->workshop_visit_date) && $request->workshop_visit_date->lt(now())) {
            return response()->json(['message' => 'Date & time of workshop appointment is invalid.'], 422);
        }

        $customer = Auth::user();
        $vehicle = $customer->vehicles()->where('vehicles.id', $request->vehicle_id)->first();
        if ($vehicle == null) {
            return response()->json(['message' => 'No permission.'], 422);
        }
       
          
        try {

            DB::beginTransaction();
            $motor = $vehicle->motor;
            $insurer = $motor->insurer;
            $driver = $motor->driver;

            $report = Reports::create([
                'ref_no' => Str::random(10),
                'vehicle_id' => $request->vehicle_id,
                'vehicle_make' => $request->vehicle_make,
                'vehicle_model' => $request->vehicle_mode,
                
                'insurance_company' => $insurer->name,
                'certificate_number' => $motor->ci_no,
                'insured_nric' => $driver->nric,
                'insured_name' => $driver->name,
                'insured_contact_number' => $driver->contact_number,

                'date_of_accident' => $request->date_of_accident,
                'location_of_accident' => $request->location_of_accident,
                'weather_road_condition' => $request->weather_road_condition,
                'reporting_type' => $request->reporting_type,
                'number_of_passengers' => $request->number_of_passengers,
                'is_video_captured' => $request->is_video_captured  == 'yes' ? true : false,
                'purpose_of_use' => $request->purpose_of_use,
                'details' => $request->details,
                'vehicle_make' => $request->vehicle_make,
                'vehicle_model' => $request->vehicle_model,
                'insurance_company' => $request->insurance_company,
                'certificate_number' => $request->certificate_number,
                'insured_nric' => $request->insured_nric,
                'insured_name' => $request->insured_name,
                'insured_contact_number' => "+65 " . $request->insured_contact_number,

                'driver_name' => $request->is_owner_drives  == 'yes' ? $driver->name : $request->driver_name,
                'driver_nric' => $request->is_owner_drives  == 'yes' ? $driver->nric : $request->driver_nric, 
                'driver_dob' => $request->is_owner_drives  == 'yes' ? $driver->dob : Carbon::createFromFormat('d/m/Y', $request->driver_dob),
                'driver_license' => $request->is_owner_drives  == 'yes' ? $driver->license_pass_date : Carbon::createFromFormat('d/m/Y', $request->driver_license),
                'driver_address' => $request->is_owner_drives  == 'yes' ? $customer->address : $request->driver_address,
                'driver_contact_no' => $request->is_owner_drives  == 'yes' ? $customer->phone : $request->driver_contact_no,
                'driver_email' => $request->is_owner_drives  == 'yes' ? $customer->email : $request->driver_email,
                'driver_occupation'  => $request->is_owner_drives  == 'yes' ? $driver->occupations : $request->driver_occupation,
            
                'is_other_vehicle' => $request->is_other_vehicle == 'yes' ? true : false,
                'other_vehicle_car_plate' => $request->other_vehicle_car_plate,
                'other_vehicle_make' => $request->other_vehicle_make,
                'other_vehicle_model' => $request->other_vehhicle_model,
                'other_driver_name' => $request->other_driver_name,
                'other_driver_nric' => $request->other_driver_nric,
                'other_driver_contact_no' => "+65 " . $request->other_contact_no,
                'other_driver_address' => $request->other_driver_address,

                'is_visiting_workshop' => $request->is_visiting_workshop == 'yes' ? true : false,
                'workshop_id' => $request->workshop_id,
                'workshop_visit_date' => $request->workshop_visit_date,
                'is_owner_drives' => $request->is_owner_drives  == 'yes' ? true : false,
                'owner_driver_relationship' => $request->owner_driver_relationship,
                'customer_id' => $customer->id
            ]);

            if($request->accident_scene != null){
                foreach($request->accident_scene as $accident_scene){
                    $ext = strtolower($accident_scene->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $accident_scene->store('accident/scene', 's3');
                    $document = ReportDocument::create([
                        'report_id' => $report->id,
                        'name' => $fileName,
                        'type' => 'scene',
                        'url' => $path
                    ]);
                }
            }

            if($request->vehicle_car_plate != null){
                foreach($request->vehicle_car_plate as $vehicle_car_plate){
                    $ext = strtolower($vehicle_car_plate->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $vehicle_car_plate->store('accident/car_plate', 's3');
                    $document = ReportDocument::create([
                        'report_id' => $report->id,
                        'name' => $fileName,
                        'type' => 'car_plate',
                        'url' => $path
                    ]);
                }
            }

            if($request->close_range_damage != null){
                foreach($request->close_range_damage as $close_range_damage){
                    $ext = strtolower($close_range_damage->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $close_range_damage->store('accident/close_damage', 's3');
                    $document = ReportDocument::create([
                        'report_id' => $report->id,
                        'name' => $fileName,
                        'type' => 'close_damage',
                        'url' => $path
                    ]);
                }
            }

            if($request->long_range_damage != null){
                foreach($request->long_range_damage as $long_range_damage){
                    $ext = strtolower($long_range_damage->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $long_range_damage->store('accident/long_damage', 's3');
                    $document = ReportDocument::create([
                        'report_id' => $report->id,
                        'name' => $fileName,
                        'type' => 'long_damage',
                        'url' => $path
                    ]);
                }
            }

            if($request->other_car_plate != null){
                foreach($request->other_car_plate as $other_car_plate){
                    $ext = strtolower($other_car_plate->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $other_car_plate->store('accident/other_car_plate', 's3');
                    $document = ReportDocument::create([
                        'report_id' => $report->id,
                        'name' => $fileName,
                        'type' => 'other_car_plate',
                        'url' => $path
                    ]);
                }
            }

            if($request->other_driving_license != null){
                foreach($request->other_driving_license as $other_driving_license){
                    $ext = strtolower($other_driving_license->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $other_driving_license->store('accident/other_driving_license', 's3');
                    $document = ReportDocument::create([
                        'report_id' => $report->id,
                        'name' => $fileName,
                        'type' => 'other_driving_license',
                        'url' => $path
                    ]);
                }
            }

            if($request->other_close_range_damage != null){
                foreach($request->other_close_range_damage as $other_close_range_damage){
                    $ext = strtolower($other_close_range_damage->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $other_close_range_damage->store('accident/other_close_damage', 's3');
                    $document = ReportDocument::create([
                        'report_id' => $report->id,
                        'name' => $fileName,
                        'type' => 'other_close_damage',
                        'url' => $path
                    ]);
                }
            }

            if($request->other_long_range_damage != null){
                foreach($request->other_long_range_damage as $other_long_range_damage){
                    $ext = strtolower($other_long_range_damage->getClientOriginalExtension());
                    if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext == "pdf"){
                        throw new \Exception('Wrong File Format');
                    }
                    $fileName = time() . '.' . $ext;
                    $path = $other_long_range_damage->store('accident/other_long_damage', 's3');
                    $document = ReportDocument::create([
                        'report_id' => $report->id,
                        'name' => $fileName,
                        'type' => 'other_long_damage',
                        'url' => $path
                    ]);
                }
            }

            DB::commit();
            return $this->success(['message' => 'Accident report created.'], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::debug($e->getMessage());
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, Reports $report)
    {
        $attr = $request->validate([
            // Basic Information
            'vehicle_make' => 'nullable',
            'vehicle_model' => 'nullable',

            // Insurance Info
            'insurance_company' => 'nullable',
            'certificate_number' => 'nullable',
            'insured_nric' => 'nullable',
            'insured_name' => 'nullable',
            'insured_contact_number' => 'nullable',

            // Accident Info
            'date_of_accident' => 'nullable|date_format:Y-m-d',
            'time_of_accident' => 'nullable|date_format:H:i',
            'location_of_accident' => 'nullable|max:255',
            'weather_road_condition' => 'nullable|in:' . implode(',', Reports::WEATHER_ROAD_CONDITIONS),
            'reporting_type' => 'nullable|in:' . implode(',', Reports::REPORTING_TYPES),
            'number_of_passengers' => 'nullable|integer',
            'is_video_captured' => 'nullable|boolean',
            'purpose_of_use' => 'nullable|in:' . implode(',', Reports::PURPOSE_OF_USE),

            // Driver Info
            'is_owner_drives' => 'nullable|boolean',
            'owner_driver_relationship' => 'nullable|in:' . implode(',', Reports::OWNER_DRIVER_RELATIONSHIPS),

            // Workshop & Visit date
            'is_visiting_workshop' => 'nullable|boolean',
            'workshop_id' => 'nullable|exists:companies,id',
            'workshop_visit_date' => 'nullable|date_format:Y-m-d',
            'workshop_visit_time' => 'nullable|date_format:H:i',
        ]);

        $customer = Auth::user();

        // Parse date of accident
        if (isset($attr['date_of_accident']) && isset($attr['time_of_accident'])) {
            $date = Carbon::createFromFormat(
                'Y-m-d H:i',
                $attr['date_of_accident'] . ' ' . $attr['time_of_accident']
            );
            $attr['date_of_accident'] = $date;
            unset($attr['time_of_accident']);
        }

        // Date of Accident must not greater than today
        if (isset($attr['date_of_accident']) && $attr['date_of_accident']->gt(now())) {
            return $this->error('invalid.date.of.accident', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Parse workshop visit date
        if (isset($attr['workshop_visit_date']) && isset($attr['workshop_visit_time'])) {
            $date = Carbon::createFromFormat(
                'Y-m-d H:i',
                $attr['workshop_visit_date'] . ' ' . $attr['workshop_visit_time']
            );
            $attr['workshop_visit_date'] = $date;
            unset($attr['workshop_visit_time']);
        }

        // Workshop visit date must greater than today
        if (isset($attr['workshop_visit_date']) && $attr['workshop_visit_date']->lt(now())) {
            return $this->error('invalid.workshop.visit.date', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $report->update($attr);

        // if ($report->workshop_visit_date != null) {
        //     Auth::user()->notify(new AccidentInspection($report));
        // }

        return $this->success($report);
    }
}
