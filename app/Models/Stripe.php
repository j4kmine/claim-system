<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stripe extends Model
{
    use HasFactory;

    protected $table = 'stripe';

    protected $guarded = [];

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'payment_method');
    }
}
