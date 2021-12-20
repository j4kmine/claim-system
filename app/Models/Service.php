<?php

namespace App\Models;

use App\Traits\RefNumber;
use App\Utilities\QueryFilterBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use Carbon\Carbon;

class Service extends Model
{
    use HasFactory, SoftDeletes, RefNumber, LaravelVueDatatableTrait;

    protected $table = 'services';

    protected $fillable = [
        'customer_id', 'vehicle_id', 'workshop_id', 'service_type_id', 'servicing_slot_id', 
        'appointment_date', 'status', 'rescheduled_count', 'remarks'
    ];

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'ref_no' => [
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
        ],
        'appointment_date' => [
            'searchable' => true,
            'orderable' => true
        ],
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
                    'make' => [
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
                    'phone' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
            'service_type' => [
                "model" => \App\Models\ServiceType::class,
                'foreign_key' => 'service_type_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
        ],
        "hasMany" => [
            'appointments' => [
                "model" => \App\Models\ServicingAppointment::class,
                'foreign_key' => 'service_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ]
        ]
    ];

    const STATUSES = [
        'upcoming' => 'upcoming',
        'open' => 'open',
        'pending' => 'pending',
        'completed' => 'completed',
        'cancelled' => 'cancelled'
    ];


    protected $appends = ['format_appointment_date', 'format_appointment_time', 'format_created_at', 'format_created_at_date'];

    public function workshop()
    {
        return $this->belongsTo(Company::class, 'workshop_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function service_type()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function documents()
    {
        return $this->hasMany(ServiceDocument::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service_types()
    {
        return $this->hasMany(ServiceType::class);
    }

    public function appointments()
    {
        return $this->hasMany(ServicingAppointment::class);
    }

    public function servicing_reports()
    {
        return $this->hasMany(ServicingReport::class, 'servicing_id');
    }

    /**
     * Filter class
     */
    public function scopeFilterBy($query, $filters)
    {
        $namespace = 'App\Utilities\ServiceFilter';
        $filter = new QueryFilterBuilder($query, $filters, $namespace);

        return $filter->apply();
    }

    public function getFormatCreatedAtAttribute(){
        if ($this->created_at != null) {
            return Carbon::parse($this->created_at)->format("d/m/Y H:iA");
        } else {
            return "-";
        }
    }

    public function getFormatCreatedAtDateAttribute(){
        if ($this->created_at != null) {
            return Carbon::parse($this->created_at)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatAppointmentDateAttribute()
    {
        if ($this->appointment_date != null) {
            return Carbon::parse($this->appointment_date)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatAppointmentTimeAttribute()
    {
        if ($this->appointment_date != null){
            $time = Carbon::parse($this->appointment_date)->format('H:i A');

            return $time;
        }else{
            return "-";
        }
    }
}
