<?php

namespace App\Http\Controllers;

use Storage;
use Throwable;
use App\Models\Service;
use App\Models\ServicingActionLog;
use Illuminate\Support\Str;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\ServicingReport;
use App\Traits\AttributeModifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ServicingReportInvoice;
use App\Models\ServicingReportDocument;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ServicingReportController extends Controller
{
    use ApiResponser, AttributeModifier;

    public function show(Request $request, Service $service)
    {
        $servicing = Service::with('servicing_reports.documents')
            ->with('servicing_reports.invoices')
            ->find($service->id);

        return $this->success($servicing);
    }

    public function store(Request $request, Service $service)
    {
        $defaultValidation = [
            'servicing_status' => 'nullable|in:' . implode(',', Service::STATUSES),
            'documents.*' => 'nullable|mimes:jpg,jpeg,pdf',
            'invoices.*' => 'nullable|mimes:jpg,jpeg,pdf',
            'total_amount' => 'nullable',
            'remarks' => 'nullable',
            'all_cars_remarks' => 'nullable'
        ];
        if ($request->documents_web) $defaultValidation['documents.*'] = "";
        if ($request->invoices_web) $defaultValidation['invoices.*'] = "";

        // only validate when status is open
        if ($service->status == 'open') {

            // validate if total_amount are empty or 0
            $defaultValidation['total_amount'] = "required|min:0|not_in:0";

            // validate has document or not
            if ($service->servicing_reports()->first() == null) {
                return $this->errors(["message" => "File documents and invoices are required!"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // validate if user has only uploaded only one type documents or invoices
            if ($service->servicing_reports()->first()->invoices()->first() == null) {
                return $this->errors(["message" => "File documents and invoices are required!"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $attr = $request->validate($defaultValidation);

        /**
         * Upload Documents
         */
        $documents = [];
        if ($request->file('documents')) {
            foreach ($request->file('documents') as $file) {

                $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . substr(Str::uuid(), 0, 5) .
                    '.' .
                    $file->getClientOriginalExtension();

                $path = $file->storePubliclyAs(
                    'servicing/' . $service->id . '/documents',
                    $fileName,
                    'public'
                );
                $documents = array_merge($documents, [$fileName => $path]);
            }
        }

        /**
         * Upload Invoices
         */
        $invoices = [];
        if ($request->file('invoices')) {
            foreach ($request->file('invoices') as $file) {

                $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . substr(Str::uuid(), 0, 5) .
                    '.' .
                    $file->getClientOriginalExtension();

                $path = $file->storePubliclyAs(
                    'servicing/' . $service->id . '/invoices',
                    $fileName,
                    'public'
                );
                $invoices = array_merge($invoices, [$fileName => $path]);
            }
        }

        unset($attr['documents']);
        unset($attr['invoices']);
        try {
            DB::beginTransaction();

            // check if its the first file and previous status was open
            if ($service->status == 'open') {
                $check_document = $service->servicing_reports()->first()->documents()->first();
                $check_invoice = $service->servicing_reports()->first()->invoices()->first();
                if (!is_null($check_document) && !is_null($check_invoice)) {
                    $attr['servicing_status'] = 'completed';
                }
            }
            if (isset($attr['servicing_status']))
                $service->update([
                    'status' => $attr['servicing_status']
                ]);

            unset($attr['servicing_status']);

            // Update or create servicing reports
            $report = $service->servicing_reports()->first();

            $attr = $this->modifyKeys($attr, [
                'remarks' => 'workshop_remarks'
            ]);

            if (!$report) {
                $report =  $service->servicing_reports()->insert(array_merge($attr, [
                    'servicing_id' => $service->id
                ]));
            } else {
                $report =  $service->servicing_reports()->update($attr);
            }

            $report = $service->servicing_reports()->first();

            foreach ($documents as $name => $path) {
                ServicingReportDocument::create([
                    'servicing_report_id' => $report->id,
                    'name' => $name,
                    'url' => $path
                ]);
            }

            foreach ($invoices as $name => $path) {
                ServicingReportInvoice::create([
                    'servicing_report_id' => $report->id,
                    'name' => $name,
                    'url' => $path
                ]);
            }

            DB::commit();

            return $this->success($service->servicing_reports()->first());
        } catch (Throwable $e) {
            DB::rollback();
            Log::error([
                'ServicingReportController.store',
                $e->getMessage(),
                $request->all()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function document(Request $request, Service $service)
    {
        $attr = $request->validate([
            'file' => 'required|mimes:jpg,jpeg,pdf'
        ]);

        // First or create Servicing Reports
        $report = $service->servicing_reports()->first();
        if (!$report) {
            $report = ServicingReport::create([
                'servicing_id' => $service->id
            ]);
        }


        // Upload document
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext != "pdf") {
            throw new \Exception('Wrong File Format');
        }
        $imageName = time() . '.' . $ext;
        $path = $file->store('servicing/document/documents', 's3');


        $document = ServicingReportDocument::create([
            'servicing_report_id' => $report->id,
            'name' => $imageName,
            'url' => Storage::disk('s3')->url($path)
        ]);

        return $this->success($document, Response::HTTP_CREATED);
    }

    public function invoice(Request $request, Service $service)
    {
        $attr = $request->validate([
            'file' => 'required|mimes:jpg,jpeg,pdf'
        ]);

        // First or create Servicing Reports
        $report = $service->servicing_reports()->first();
        if (!$report) {
            $report = ServicingReport::create([
                'servicing_id' => $service->id
            ]);
        }

        // Upload document
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext != "pdf") {
            throw new \Exception('Wrong File Format');
        }
        $imageName = time() . '.' . $ext;
        $path = $file->store('servicing/invoice/invoices', 's3');

        $invoice = ServicingReportInvoice::create([
            'servicing_report_id' => $report->id,
            'name' => $imageName,
            'url' => Storage::disk('s3')->url($path)
        ]);

        return $this->success($invoice, Response::HTTP_CREATED);
    }

    public function destroyDocument(ServicingReportDocument $document)
    {
        $document->delete();

        return $this->success(null);
    }

    public function destroyInvoice(ServicingReportInvoice $invoice)
    {
        $invoice->delete();

        return $this->success(null);
    }
}
