<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotorDriver extends Model
{
    protected $with = ['driver'];
    
    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id');
    }
}
