<?php

namespace App\Http\Controllers\Mobile\Report;

use App\Http\Controllers\Controller;
use App\Models\ReportDocument;
use App\Models\Reports;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ReportDocumentController extends Controller
{
    use ApiResponser;

    public function store(Request $request, Reports $report)
    {
        $attr = $request->validate([
            'type' => 'required|in:' . implode(',', ReportDocument::TYPES),
            'file' => 'required|max:100000'
        ]);

        $customer = Auth::user();

        // Upload file
        $file = $request->file('file');
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $path = $request->file('file')->storePubliclyAs(
            'reports/' . $report->id,
            $fileName,
            'public'
        );

        $document = ReportDocument::create([
            'report_id' => $report->id,
            'name' => $fileName,
            'type' => $attr['type'],
            'url' => $path
        ]);

        return $this->success($document, Response::HTTP_CREATED);
    }

    public function index(Request $request, Reports $report)
    {
        $customer = Auth::user();

        return $this->success($report->documents);
    }
}
