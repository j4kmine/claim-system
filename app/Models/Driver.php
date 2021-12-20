<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Driver extends Model
{
    use HasFactory;
    protected $appends = ['format_residential', 'format_nationality', 'format_dob', 'format_dol', 'format_id_type'];

    protected $table = 'drivers';

    protected $guarded = [];

    const OCCUPATIONS = [
        'Working Indoors',
        'Working Outdoors'
    ];

    public function owned()
    {
        return $this->morphTo();
    }


    public function getFormatIdTypeAttribute()
    {
        if ($this->nric_type != null && isset(Motor::NRIC_TYPES[$this->nric_type])) {
            return Motor::NRIC_TYPES[$this->nric_type];
        } else {
            return "";
        }
    }

    public function getFormatResidentialAttribute()
    {
        if ($this->residential != null && isset(Motor::RESIDENTIALS[$this->residential])) {
            return Motor::RESIDENTIALS[$this->residential];
        } else {
            return "";
        }
    }

    public function getFormatNationalityAttribute()
    {
        if ($this->nationality != null && isset(Motor::NATIONALITIES[$this->nationality])) {
            return Motor::NATIONALITIES[$this->nationality];
        } else {
            return "";
        }
    }

    public function getFormatDobAttribute()
    {
        if ($this->dob != null) {
            return Carbon::parse($this->dob)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getFormatDolAttribute()
    {
        if ($this->license_pass_date != null) {
            return Carbon::parse($this->license_pass_date)->format("d/m/Y");
        } else {
            return "";
        }
    }
}
