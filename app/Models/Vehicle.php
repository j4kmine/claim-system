<?php

namespace App\Models;

use Carbon\Carbon;
use App\Utilities\QueryFilterBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, LaravelVueDatatableTrait;
    
    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'registration_no' => [
            'searchable' => true,
            'orderable' => true
        ],
        'coe_expiry_date' => [
            'searchable' => true,
            'orderable' => true
        ],
    ];

    protected $fillable = [
        'registration_no', 'chassis_no', 'engine_no', 'make', 'model', 'mileage', 'manufacture_year',
        'registration_date', 'nric_uen', 'type', 'capacity', 'category', 'type', 'fuel',
        'tax_expiry_date', 'coe_expiry_date'
    ];
    protected $appends = ['format_registration_date', 'format_capacity'];

    public function getFormatRegistrationDateAttribute()
    {
        $value = $this->registration_date;
        if ($value != null) {
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return '-';
        }
    }

    public function getFormatCapacityAttribute()
    {
        $value = $this->capacity;
        if ($value != null) {
            return number_format($value) . ' CC';
        } else {
            return '-';
        }
    }
    

    public function customers()
    {
        return $this->belongsToMany(Customer::class)->withPivot('granted_by');
    }

    public function warranty()
    {
        return $this->hasOne(Warranty::class);
    }

    public function motor()
    {
        return $this->hasOne(Motor::class);
    }
    
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /*
    public function customer_vehicle()
    {
        return $this->hasMany(CustomerVehicle::class);
    }*/

    public function reports()
    {
        return $this->hasMany(Reports::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * Filter class
     */
    public function scopeFilterBy($query, $filters)
    {
        $namespace = 'App\Utilities\VehicleFilter';
        $filter = new QueryFilterBuilder($query, $filters, $namespace);

        return $filter->apply();
    }
}
