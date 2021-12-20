<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Motor;
use Carbon\Carbon;

class Proposer extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'salutation',
        'name',
        'email',
        'phone',
        'nric_uen',
        'address',
        'date_of_birth',
    ];

    protected $appends = ['format_residential', 'format_nationality', 'format_occupation', 'format_dob', 'format_dol'];

    public function getFormatResidentialAttribute()
    {
        if ($this->residential != null && isset(Motor::OCCUPATIONS[$this->residential])) {
            return Motor::RESIDENTIALS[$this->residential];
        } else {
            return "";
        }
    }

    public function getFormatNationalityAttribute()
    {
        if ($this->nationality != null) {
            return Motor::NATIONALITIES[$this->nationality];
        } else {
            return "";
        }
    }

    public function getFormatOccupationAttribute()
    {
        if ($this->occupation != null && isset(Motor::OCCUPATIONS[$this->occupation])) {
            return Motor::OCCUPATIONS[$this->occupation];
        } else {
            return "";
        }
    }

    public function getFormatDobAttribute()
    {
        if ($this->date_of_birth != null) {
            return Carbon::parse($this->date_of_birth)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getFormatDolAttribute()
    {
        if ($this->date_of_license != null) {
            return Carbon::parse($this->date_of_license)->format("d/m/Y");
        } else {
            return "";
        }
    }
}
