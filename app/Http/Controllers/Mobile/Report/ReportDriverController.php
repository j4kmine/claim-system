<?php

namespace App\Http\Controllers\Mobile\Report;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Reports;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ReportDriverController extends Controller
{
    use ApiResponser;

    public function store(Request $request, Reports $report)
    {
        $attr = $request->validate([
            'name' => 'required|string',
            'nric' => 'nullable|string',
            'dob' => 'nullable|date_format:Y-m-d',
            'license_pass_date' => 'nullable|date_format:Y-m-d',
            'address' => 'nullable|max:255',
            'contact_number' => 'nullable',
            'email' => 'nullable|email',
            'occupations' => 'nullable|in:' . implode(',', Driver::OCCUPATIONS),
        ]);

        $customer = Auth::user();
        
        unset($attr['customer_id']);

        $driver = $report->driver()->create($attr);

        return $this->success($driver, Response::HTTP_CREATED);
    }
}
