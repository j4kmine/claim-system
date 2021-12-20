<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsurerExtend extends Model
{
    protected $with = ['company'];
    protected $table = 'insurers';

    public function company(){
        return $this->belongsTo(\App\Models\Company::class, 'insurer_id');
    }
}
