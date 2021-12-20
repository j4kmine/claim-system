<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paynow extends Model
{
    use HasFactory;

    protected $table = 'paynow';

    protected $guarded = [];

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'payment_method');
    }
}
