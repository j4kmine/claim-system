<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\Stripe;
use App\StripeCallback;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StripeCallbackController extends Controller
{
    use ApiResponser;

    public function store(Request $request)
    {
        try {
            $callback = new StripeCallback($request->all());

            $clientSecret = $callback->clientSecret;

            $stripe = Stripe::where('client_secret', $clientSecret)->first();

            // If there's no records based on the client secret
            if (!$stripe) {
                return $this->error(
                    'Record not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            $callback->updateRecords($stripe);

            return $this->success(null);
        } catch (Throwable $e) {
            Log::error([
                'StripeCallbackController.store',
                $e->getMessage(),
                $request->all()
            ]);
            return $this->error(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
