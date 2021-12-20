<?php

namespace App\Http\Controllers\Mobile\Report;

use App\Http\Controllers\Controller;
use App\Models\Reports;
use App\Models\ReportVehicleInvolve;
use App\Traits\ApiResponser;
use App\Traits\AttributeModifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportInvolveController extends Controller
{
    use ApiResponser, AttributeModifier;

    public function store(Request $request, Reports $report)
    {
        $attr = $request->validate([
            // Vehicle Info
            'vehicle_plate_number' => 'required',
            'vehicle_make' => 'required',
            'vehicle_model' => 'required',
            'vehicle_plate_number' => 'required',

            // Driver Info
            'driver_name' => 'required',
            'driver_nric' => 'required',
            'driver_contact_number' => 'required',
            'driver_address' => 'required',
        ]);

        $customer = Auth::user();

        // Create record that involved the accident
        $involve = ReportVehicleInvolve::create([
            'report_id' => $report->id,
            'vehicle_plate_number' => $attr['vehicle_plate_number'],
            'vehicle_make' => $attr['vehicle_make'],
            'vehicle_model' => $attr['vehicle_model'],
        ]);

        // Add driver on that involved accident
        $involve->driver()->create([
            'name' => $attr['driver_name'],
            'nric' => $attr['driver_nric'],
            'contact_number' => $attr['driver_contact_number'],
            'address' => $attr['driver_address'],
        ]);

        return $this->success(array_merge($involve->toArray(), [
            'driver' => $involve->driver
        ]), 201);
    }
}
