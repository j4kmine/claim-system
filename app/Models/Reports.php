<?php

namespace App\Models;

use App\Traits\RefNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use Carbon\Carbon;

class Reports extends Model
{
    use HasFactory, SoftDeletes, RefNumber, LaravelVueDatatableTrait;

    protected $table = 'reports';

    protected $guarded = [];

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'ref_no' => [
            'searchable' => true,
            'orderable' => true
        ],
        'insured_name' => [
            'searchable' => true,
            'orderable' => true
        ],
        'certificate_number' => [
            'searchable' => true,
            'orderable' => true
        ],
        'number_of_passengers' => [
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

    protected $dataTableRelationships = [
        "belongsTo" => [
            'vehicle' => [
                "model" => \App\Models\Vehicle::class,
                'foreign_key' => 'vehicle_id',
                'columns' => [
                    'registration_no' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
            'workshop' => [
                "model" => \App\Models\Company::class,
                'foreign_key' => 'workshop_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
            'customer' => [
                "model" => \App\Models\Customer::class,
                'foreign_key' => 'customer_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
        ],
    ];

    const WEATHER_ROAD_CONDITIONS = [
        'Clear & Dry',
        'Raining & Wet',
        'After Rain & Wet'
    ];

    const REPORTING_TYPES = [
        'Reporting Only',
        'Claim Other Party',
        'Claim Own Insurance'
    ];

    const PURPOSE_OF_USE = [
        'Private Use',
        'Work Purpose',
    ];

    const OWNER_DRIVER_RELATIONSHIPS = [
        'Spouse', 'Parents',
        'Children', 'Sibling',
        'Employee', 'Others'
    ];

    const STATUSES = [
        'pending', 'completed'
    ];

    protected $casts = [
        'workshop_visit_date' => 'datetime',
    ];

    protected $appends = [
        'format_accident_date', 
        'format_accident_date_web', 
        'format_accident_time', 
        'format_accident_time_web', 
        'format_appointment_date', 
        'format_appointment_time', 
        'format_dob', 
        'format_license_date',
    ];

    public function driver()
    {
        return $this->morphOne(Driver::class, 'ownable');
    }

    public function vehicle_involves()
    {
        return $this->hasMany(ReportVehicleInvolve::class, 'report_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function documents()
    {
        return $this->hasMany(ReportDocument::class, 'report_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Company::class, 'workshop_id');
    }

    public function reports()
    {
        return $this->hasMany(AccidentReport::class, 'accident_id');
    }

    public function getCreatedAtAttribute($val)
    {
        return Carbon::parse($val)->format("d/m/Y");
    }

    public function getFormatAccidentDateAttribute()
    {
        if ($this->date_of_accident != null) {
            return Carbon::parse($this->date_of_accident)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatAccidentDateWebAttribute()
    {
        if ($this->date_of_accident != null) {
            return Carbon::parse($this->date_of_accident)->format("d-m-Y");
        } else {
            return "-";
        }
    }
    
    public function getFormatAccidentTimeWebAttribute()
    {
        if ($this->date_of_accident != null) {
            return Carbon::parse($this->date_of_accident)->format("H:i");
        } else {
            return "-";
        }
    }

    public function getFormatAccidentTimeAttribute()
    {
        if ($this->date_of_accident != null){
            $time = Carbon::parse($this->date_of_accident)->format('H:i A');

            return $time;
        }else{
            return "-";
        }
    }

    public function getFormatAppointmentDateAttribute()
    {
        if ($this->workshop_visit_date != null) {
            return Carbon::parse($this->workshop_visit_date)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatAppointmentTimeAttribute()
    {
        if ($this->workshop_visit_date != null){
            $time = Carbon::parse($this->workshop_visit_date)->format('H:i A');

            return $time;
        }else{
            return "-";
        }
    }

    public function getFormatDobAttribute()
    {
        if ($this->driver_dob != null) {
            return Carbon::parse($this->driver_dob)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatLicenseDateAttribute()
    {
        if ($this->driver_license != null) {
            return Carbon::parse($this->driver_license)->format("d/m/Y");
        } else {
            return "-";
        }
    }


}
