<?php

namespace App;

use App\Models\Customer;
use App\Models\Stripe as ModelsStripe;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class Stripe
{
    /** 
     * String
     */
    public $stripeCustomerId;

    /**
     * App\Models\Customer
     */
    public $customer;

    /**
     * Any models
     */
    public $paymentFor;

    /**
     * App\Models\Stripe
     */
    public $stripe;

    public function __construct($paymentFor, Customer $customer)
    {
        $this->paymentFor = $paymentFor;
        $this->customer = $customer;
        \Stripe\Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Set up Ephemeral Key
     * https://stripe.com/docs/mobile/android/basic#set-up-ephemeral-key
     */
    public function getKey(string $stripeCustomerId, string $stripeVersion)
    {
        $this->stripeCustomerId = $stripeCustomerId;

        $key = \Stripe\EphemeralKey::create(
            ['customer' => $stripeCustomerId],
            ['stripe_version' => $stripeVersion]
        );

        return $key;
    }

    /**
     * Generate PaymentIntent
     * https://stripe.com/docs/mobile/android/basic#complete-the-payment
     */
    public function getIntent(float $nominal, $currency = 'usd')
    {
        $intent = \Stripe\PaymentIntent::create([
            'amount' => $nominal,
            'currency' => $currency,
        ]);

        $client_secret = $intent->client_secret;

        $stripe = \App\Models\Stripe::create([
            'customer_id' => Auth::user()->id,
            'status' => 'pending',
            'client_secret' => $client_secret
        ]);

        $transaction = Transaction::create([
            'customer_id' => Auth::user()->id,
            'payment_method_type' => get_class($stripe),
            'payment_method_id' => $stripe->id,
            'payment_for_type' => get_class($this->paymentFor),
            'payment_for_id' => $this->paymentFor->id,
            'nominal' => $nominal,
            'status' => 'pending'
        ]);

        return $client_secret;
    }
}
