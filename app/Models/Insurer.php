<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurer extends Model
{
    protected $fillable = ['surveyor_id', 'insurer_id'];
    protected $with = ['company'];

    public function surveyor()
    {
        return $this->belongsTo(\App\Models\Company::class, 'surveyor_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'insurer_id');
    }
}
