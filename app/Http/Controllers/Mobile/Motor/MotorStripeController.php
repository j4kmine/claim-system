<?php

namespace App\Http\Controllers\Mobile\Motor;

use Throwable;
use App\Models\Stripe;
use App\Transaction;
use App\Models\Customer;
use App\Models\Motor;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class MotorStripeController extends Controller
{
    use ApiResponser;

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'motor_id' => 'required|exists:motors,id',
            'payment_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->toArray(), 422);
        }

        try {
            $customer = Auth::user(); 
            //$motor = $customer->motors()->where('motors.status', 'pending_acceptance')->where('motors.id', $request->motor_id)->first();
            $motor = $customer->motors()->where('motors.id', $request->motor_id)->first();
            if($motor != null){
                //$stripeCustomer = $customer->createOrGetStripeCustomer();
                DB::beginTransaction();
                $stripeCharge = $request->user()->charge(
                    $motor->price * 100, $request->payment_id
                );
                $stripe = new Stripe;
                $stripe->customer_id = $customer->id;
                $stripe->payment_id = $request->payment_id;
                if($stripeCharge->isSucceeded()){
                    $stripe->status = 'success';
                } else {
                    $stripe->status = 'failed';
                }
                $stripe->save();
                $motor->status = 'pending_admin_review';
                $motor->save();
                DB::commit();
                return $this->success(['message' => 'Successfully made payment.']);
            } else {
                return response()->json(['message' => 'Failed to make payment.'], 422);
            }
        } catch (Throwable $e) {
            DB::rollback();
            Log::error([
                'MotorStripeController.store',
                $e->getMessage(),
                $request->all()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
