<?php

namespace App;

use App\Models\Stripe;

class StripeCallback
{
    /**
     * array
     */
    public $payload;

    /**
     * string
     */
    public $clientSecret;

    /**
     * string
     */
    public $intentType;

    /**
     * Parse the intent type and client secret
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;

        $this->intentType = $payload['type'];

        $this->clientSecret = $payload['data']['object']['client_secret'];
    }

    /**
     * Update the records,
     * based on received intent-type
     * 
     * @param Stripe $stripe
     * @return Stripe $stripe
     */
    public function updateRecords(Stripe $stripe): \App\Models\Stripe
    {
        // Normalized the intent type
        $normalizedType = str_replace(
            '_',
            ' ',
            str_replace('.', ' ', $this->intentType)
        );

        $normalizedType = str_replace(' ', '', ucwords($normalizedType));

        $stripe->update([
            'status' => $this->intentType,
        ]);

        return $this->$normalizedType($stripe);
    }

    /**
     * Method for payment_intent.succeeded type
     */
    public function PaymentIntentSucceeded(Stripe $stripe): \App\Models\Stripe
    {
        $stripe->update([
            'receipt_url' => $this->payload['data']['object']['charges']['data'][0]['receipt_url'],
            'receipt_number' => $this->payload['data']['object']['charges']['data'][0]['receipt_number'],
        ]);

        $stripe->transaction->update([
            'status' => 'completed'
        ]);

        return $stripe;
    }

    /**
     * Method for payment_intent.processing type
     */
    public function PaymentIntentProcessing(Stripe $stripe): \App\Models\Stripe
    {
        $stripe->transaction->update([
            'status' => 'pending'
        ]);

        return $stripe;
    }

    /**
     * Method for payment_intent.created type
     */
    public function PaymentIntentCreated(Stripe $stripe): \App\Models\Stripe
    {
        $stripe->transaction->update([
            'status' => 'pending'
        ]);

        return $stripe;
    }

    /**
     * Method for payment_intent.payment-failed type
     */
    public function PaymentIntentPaymentFailed(Stripe $stripe): \App\Models\Stripe
    {
        $stripe->transaction->update([
            'status' => 'failed'
        ]);

        return $stripe;
    }

    /**
     * Method for payment_intent.canceled type
     */
    public function PaymentIntentCanceled(Stripe $stripe): \App\Models\Stripe
    {
        $stripe->transaction->update([
            'status' => 'canceled'
        ]);

        return $stripe;
    }
}
