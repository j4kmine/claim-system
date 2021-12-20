<?php

namespace App\Http\Controllers\Mobile\Warranty;

use Throwable;
use App\Models\Stripe as Stripes;
use App\Stripe; 
use App\Transaction;
use App\Models\Customer;
use App\Models\Warranty;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WarrantyStripeController extends Controller
{
    use ApiResponser;
    public function store(Request $request){
         $attr = $request->validate([
            'warranty_id' => 'required|exists:warranties,id',
            'payment_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $customer = Auth::user();
            $warranty = $customer->warranties()->with(['vehicle', 'proposer', 'insurer:id,code'])->where('warranties.id', $request->warranty_id)->first();
            $stripeCharge = $request->user()->charge(
                    $warranty->price * 100, $request->payment_id
            );
            $stripe = new Stripes;
            $stripe->customer_id = $customer->id;
            $stripe->payment_id = $request->payment_id;
            if($stripeCharge->isSucceeded()){
                $stripe->status = 'success';
            } else {
                $stripe->status = 'failed';
            }
           
             $stripe->save();
            $warranty->status = 'pending_admin_review';
            $warranty->save();
            DB::commit();
            return $this->success(['message' => 'Successfully made payment.','warranty' => $warranty->toArray()]);
        } catch (Throwable $e) {
            DB::rollback();
            Log::error([
                'WarrantyStripeController.store',
                $e->getMessage(),
                $request->all()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function store_old(Request $request)
    {
        $attr = $request->validate([
            'warranty_id' => 'required|exists:warranties,id',
            'stripe_customer_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $customer = Auth::user();
            $warranty = $customer->warranties()->with(['vehicle', 'proposer', 'insurer:id,code'])->where('warranties.id', $request->warranty_id)->first();

            $stripe = new Stripe(
                $warranty,
                $customer
            ); 

            $key = $stripe->getKey(
                $request->stripe_customer_id,
                env('STRIPE_VERSION')
            );

            DB::commit();

            return $this->success(
                array_merge(
                    ['ephemeral' => $key->toArray()],
                    ['warranty' => $warranty->toArray()]
                )
            );
        } catch (Throwable $e) {
            DB::rollback();
            Log::error([
                'WarrantyStripeController.store',
                $e->getMessage(),
                $request->all()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function intent(Request $request)
    {
        $attr = $request->validate([
            'warranty_id' => 'required|exists:warranties,id',
        ]);

        try {
            DB::beginTransaction();

            $customer = Auth::user();

            $warranty = Warranty::with('vehicle')
                ->with('dealer')
                ->find($attr['warranty_id']);

            // Check Authorization
            $this->authorize('update', $warranty);

            $stripe = new \App\Stripe(
                $warranty,
                $customer
            );

            $clientSecret = $stripe->getIntent(substr($warranty->format_price, 1));

            DB::commit();

            return $this->success(
                array_merge(
                    ['key' => $clientSecret],
                    ['warranty' => $warranty->toArray()]
                )
            );
        } catch (Throwable $e) {
            DB::rollback();
            Log::error([
                'WarrantyStripeController.intent',
                $e->getMessage(),
                $request->all()
            ]);
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
