<?php

namespace App\Http\Controllers\Mobile\Warranty;

use Throwable;
use App\Models\Paynow;
use App\Models\Customer;
use App\Models\Warranty;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WarrantyPaynowController extends Controller
{
    use ApiResponser;

    public function reference(Request $request)
    {
        $attr = $request->validate([
            'warranty_id' => 'required|exists:warranties,id'
        ]);

        try {
            $customer = Auth::user();
            $warranty = $customer->warranties()->where('warranties.id', $request->warranty_id)->first();

            if ($warranty != null) {
                return $this->success(['reference' => $warranty->ref_no, 'format_amount' => $warranty->price, 'amount' => "$".number_format($warranty->price, 2), 'uen' => 'UEN201420214']);
            } else {
                return response()->json(['message' => 'Failed to retrieve reference.'], 422);
            }
        } catch (Throwable $e) {
            Log::error([
                'WarrantyPaynowController.reference',
                $request->all(),
                $e->getMessage()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $attr = $request->validate([
            'warranty_id' => 'required|exists:warranties,id'
        ]);

        try {
            $customer = Auth::user();
            $warranty = $customer->warranties()->where('warranties.id', $request->warranty_id)->first();

            if ($warranty != null) {
                $paynow = new PayNow;
                $paynow->customer_id = $customer->id;
                $paynow->ref = $warranty->ref_no;
                $paynow->status = 'pending';
                $paynow->save();
                $warranty->status = 'pending_admin_review';
                $warranty->save();
                return $this->success(['message' => 'Waiting for admin review.']);
            } else {
                return response()->json(['message' => 'Failed to retrieve reference.'], 422);
            }
        } catch (Throwable $e) {
            Log::error([
                'WarrantyPaynowController.reference',
                $request->all(),
                $e->getMessage()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
