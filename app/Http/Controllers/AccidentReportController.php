<?php

namespace App\Http\Controllers;
use Storage;
use App\Models\Reports;
use Illuminate\Support\Str;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\AccidentReport;
use Illuminate\Support\Facades\Auth;
use App\Models\AccidentReportDocument;

class AccidentReportController extends Controller
{
    use ApiResponser;

    public function store(Request $request, Reports $accident)
    {
        $attr = $request->validate([
            'accident_status' => 'nullable|in:' . implode(',', Reports::STATUSES),
            'remarks' => 'nullable',
        ]);

        // Update or Create accident reports
        $report = $accident->reports->first();
        if (!$report) {
            $report = AccidentReport::create([
                'accident_id' => $accident->id,
            ]);
        }

        $check_accident_report = array_column($report->documents()->get()->toArray(), 'type');
        
        if (in_array('inspection_report', $check_accident_report) && in_array('accident_report', $check_accident_report)){
            $attr['accident_status'] = 'completed';
        }

        // check check document accident report and inspection report 

        // Update the remarks, if exists
        if (Auth::user()->category == 'workshop' && isset($attr['remarks'])) {
            $report->update([
                'remarks' => $attr['remarks']
            ]);
        } else if (Auth::user()->category == 'all_cars' && isset($attr['remarks'])) {
            $report->update([
                'all_cars_remarks' => $attr['remarks']
            ]);
        }

        // Update accident status
        if (isset($attr['accident_status'])) $accident->update(['status' => $attr['accident_status']]);

        return $this->success($accident, 200);
    }

    public function document(Request $request, Reports $accident)
    {
        $attr = $request->validate([
            'type' => 'required|in:' . implode(',', AccidentReportDocument::TYPES),
            'file' => 'required|mimes:pdf,jpg,jpeg'
        ]);

        // Update or Create accident reports
        $report = $accident->reports->first();
        if (!$report) {
            $report = AccidentReport::create([
                'accident_id' => $accident->id,
            ]);
        }

        // Upload file to storage
        $file = $request->file('file');
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . substr(Str::uuid(), 0, 5) .
            '.' .
            $file->getClientOriginalExtension();

        // upload file to s3 bucket

        // $path = $file->storePubliclyAs(
        //     'accidents/' . $accident->id . '/reports',
        //     $fileName,
        //     'public'
        // );
        $ext = strtolower($file->getClientOriginalExtension());
        if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext != "pdf"){
            throw new \Exception('Wrong File Format');
        }
        $imageName = time() . '.'.$ext;
        $path = $file->store('accident/' . $attr['type'] . '/reports', 's3');

        // Store the uploaded file
        $document = AccidentReportDocument::create([
            'accident_report_id' => $report->id,
            'name' => $imageName,
            'type' => $attr['type'],
            'url' => Storage::disk('s3')->url($path)
        ]);

        return $this->success($document);
    }

    public function removeDocument(AccidentReportDocument $document)
    {
        $document->delete();

        return $this->success(null);
    }
}
