<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimItem extends Model
{
    //
    protected $fillable = ['claim_id', 'item_id', 'item', 'amount', 'recommended', 'type'];
}
