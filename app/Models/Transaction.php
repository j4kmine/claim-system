<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $guarded = [];

    const STATUSES = [
        'pending', 'completed', 'canceled', 'failed'
    ];

    public function payment_method()
    {
        return $this->morphTo();
    }

    public function payment_for()
    {
        return $this->morphTo();
    }
}
