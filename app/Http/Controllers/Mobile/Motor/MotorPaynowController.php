<?php

namespace App\Http\Controllers\Mobile\Motor;

use Throwable;
use App\Models\Paynow;
use App\Models\Customer;
use App\Models\Motor;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MotorPaynowController extends Controller
{
    use ApiResponser;

    public function reference(Request $request)
    {
        $attr = $request->validate([
            'motor_id' => 'required|exists:motors,id'
        ]);

        try {
            $customer = Auth::user();
            $motor = $customer->motors()->where('motors.id', $request->motor_id)->first();

            if ($motor != null) {
                return $this->success(['reference' => $motor->ref_no, 'format_amount' => $motor->price, 'amount' => "$".number_format($motor->price, 2), 'uen' => 'UEN201420214']);
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
            'motor_id' => 'required|exists:motors,id'
        ]);

        try {
            $customer = Auth::user();
            $motor = $customer->motors()->where('motors.id', $request->motor_id)->first();

            if ($motor != null) {
                $paynow = new PayNow;
                $paynow->customer_id = $customer->id;
                $paynow->ref = $motor->ref_no;
                $paynow->status = 'pending';
                $paynow->save();
                $motor->status = 'pending_admin_review';
                $motor->save();
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
