<?php

namespace App;

use App\Models\Customer;
use Illuminate\Support\Str;

class Paynow
{
    /**
     * \App\Models\Customer
     */
    public $customer;

    /**
     * int
     */
    public $nominal;

    /**
     * Any models
     */
    public $paymentFor;

    /**
     * App\Models\Paynow
     */
    public $paynow;

    /**
     * \App\Models\Transaction
     */
    public $transaction;

    /**
     * @param mixed $paymentFor
     * @param Customer $customer
     * @param float $nominal
     */
    public function __construct($paymentFor, Customer $customer)
    {
        $this->paymentFor = $paymentFor;
        $this->customer = $customer;
    }

    /**
     * Create paynow and transaction records
     * 
     * @param string $ref
     * @param float $nominal
     * @return array
     */
    public function saveRecords(string $ref, float $nominal): array
    {
        // Create records for Paynow and Transaction models
        $paynow = \App\Models\Paynow::create([
            'customer_id' => $this->customer->id,
            'ref' => $ref,
            'status' => 'pending'
        ]);

        $transaction = \App\Models\Transaction::create([
            'customer_id' => $this->customer->id,
            'payment_method_type' => get_class($paynow),
            'payment_method_id' => $paynow->id,
            'payment_for_type' => get_class($this->paymentFor),
            'payment_for_id' => $this->paymentFor->id,
            'nominal' => $nominal,
            'status' => 'pending'
        ]);

        $this->paynow = $paynow;
        $this->transaction = $transaction;

        return [$paynow, $transaction];
    }

    /**
     * Helper function to
     * generate a random and unique ref
     * 
     * @return string
     */
    public function generateRef(): string
    {
        $exists = true;
        $randomRef = '';

        while ($exists) {
            $randomRef = substr( // Set to 8 characters
                strtoupper( // Capital Case
                    str_replace('-', '', Str::uuid()) // Remove '-'
                ),
                0,
                8
            );
            $exists = \App\Models\Paynow::where('ref', $randomRef)->exists();
        }

        return $randomRef;
    }
}
