<?php

namespace App\Http\Controllers;

use App\Models\ClaimDocument;
use App\Models\ReportDocument;
use Illuminate\Http\Request;
use Validator;
use Storage;

class DocumentController extends Controller
{
    //
    public function claimUpload(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'type' => 'required|in:damage,quotation,service,supplier,note,tax,insurer-invoice',
            'file' => 'required|max:100000|mimes:pdf,jpg,jpeg,png' // max 100000kb
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        // TODO :: check permission
        $path = $request->file('file')->store('claim/' . $request->type, 's3');
        //Storage::disk('s3')->response('images/' . $image->filename);
        return response()->json(['message' => "File uploaded successfully.", 'url' => Storage::disk('s3')->url($path)], 201);
    }

    public function uploadInsurerInvoice(Request $request, $id)
    {
        // validate data
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'ref_no' => 'required',
            'insurer_invoice' => 'required|array'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        // update data
        $saveDocument = [];
        foreach ($request->insurer_invoice as $key => $value) {
            $saveDocument = ClaimDocument::create([
                'claim_id' => $id,
                'name' => 'Invoice-' . $request->ref_no,
                'url' => $value['url'],
                'type' => 'insurer-invoice'
            ]);
        }
        return response()->json(['message' => "Insurer Invoice uploaded successfully.", 'data' => $saveDocument], 201);
    }

    public function warrantyUpload(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'type' => 'required|in:log,assessment',
            'file' => 'required|max:100000|mimes:pdf,jpg,jpeg,png' // max 100000kb
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        // TODO :: check permission
        $path = $request->file('file')->store('warranty/' . $request->type, 's3');
        //Storage::disk('s3')->response('images/' . $image->filename);
        return response()->json(['message' => "File uploaded successfully.", 'url' => Storage::disk('s3')->url($path)], 201);
    }

    public function motorUpload(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'type' => 'required|in:log,note,ci,driving_license',
            'file' => 'required|max:100000|mimes:pdf,jpg,jpeg,png' // max 100000kb
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }
        // TODO :: check permission
        $path = $request->file('file')->store('motor/' . $request->type, 's3');
        //Storage::disk('s3')->response('images/' . $image->filename);
        return response()->json(['message' => "File uploaded successfully.", 'url' => Storage::disk('s3')->url($path)], 201);
    }
}
