<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Carbon\Carbon;
use App\Models\Motor;
use Laravel\Cashier\Billable;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LaravelVueDatatableTrait, Notifiable, Billable;

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'name' => [
            'searchable' => true,
            'orderable' => true
        ],
        'email' => [
            'searchable' => true,
            'orderable' => true
        ],
        'nric_uen' => [
            'searchable' => true,
            'orderable' => true
        ],
        'phone' => [
            'searchable' => true,
            'orderable' => true
        ],
        'status' => [
            'searchable' => true,
            'orderable' => true
        ],
        'created_at' => [
            'searchable' => true,
            'orderable' => true
        ]
    ];

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
        'password',
        'status',
        'date_of_birth',
        'profile_photo',
        'gender',
        'occupation',
        'nationality',
        'residential',
        'driving_license_class',
        'driving_license_validity'

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['format_driving_license_validity', 'format_dob', 'format_nationality', 'format_residential', 'format_occupation'];

    public function getFormatDobAttribute(){
        if ($this->date_of_birth != null) {
            return Carbon::parse($this->date_of_birth)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatDrivingLicenseValidityAttribute(){
        if ($this->driving_license_validity != null) {
            return Carbon::parse($this->driving_license_validity)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatNationalityAttribute(){
        if ($this->nationality != null) {
            return Motor::NATIONALITIES[$this->nationality];
        } else {
            return "-";
        }
    }

    public function getFormatResidentialAttribute(){
        if ($this->residential != null) {
            return Motor::RESIDENTIALS[$this->residential];
        } else {
            return "-";
        }
    }

    public function getFormatOccupationAttribute(){
        if ($this->occupation != null) {
            return Motor::OCCUPATIONS[$this->occupation];
        } else {
            return "-";
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    // public function vehicles() {
    //     return $this->hasMany(\App\Models\Vehicle::class);
    // }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class)->withPivot('granted_by');
    }

    public function ownedVehicles()
    {
        // return $this->hasMany(Vehicle::class, 'nric_uen', 'nric_uen')->whereNotNull('registration_no')->groupby('registration_no');
        return $this->belongsToMany(Vehicle::class)->withPivot('granted_by')->where('granted_by', null);
    }

    public function grantedVehicles()
    {
        return $this->belongsToMany(Vehicle::class)->withPivot('granted_by')->where('granted_by', '!=',null);
    }

    public function getCreatedAtAttribute($value)
    {
        if ($value != null) {
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function settings()
    {
        return $this->morphToMany(Setting::class, 'ownable', 'user_setting')->withPivot('value');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function reports()
    {
        return $this->hasMany(Reports::class);
    }
    
    public function warranties()
    {
        return $this->belongsToMany(Warranty::class)->withPivot('granted_by');
    }

    public function motors()
    {
        return $this->belongsToMany(Motor::class)->withPivot('granted_by');
    }

    public function devices()
    {
        return $this->hasMany(CustomerDevice::class);
    }
    /*
    public function customer_vehicle()
    {
        return $this->hasMany(CustomerVehicle::class);
    }*/

    public function getCurrency()
    {
        return 'sgd';
    }

}
