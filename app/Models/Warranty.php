<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Auth;

class Warranty extends Model
{
    use LaravelVueDatatableTrait, HasFactory;
    protected $fillable = [
        'ref_no', 'customer_id', 'proposer_id', 'vehicle_id', 'insurer_id',
        'creator_id', 'dealer_id', 'price', 'package', 'remarks',
        'max_claim', 'mileage', 'mileage_coverage', 'warranty_duration', 'start_date', 'status',
        'expiry_date', 'submitted_at', 'quote_valid_till'
    ];
    protected $appends = ['format_premium_per_year', 'format_price', 'format_max_claim', 'format_mileage_coverage', 'format_start_date', 'format_valid_till', 'format_submitted_at', 'end_date', 'warranty_period'];

    const STATUSES = [
        'pending_enquiry',
        'pending_proposal',
        'pending_admin_review',
        'completed'
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'start_date' => 'datetime',
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
        'ci_no' => [
            'searchable' => true,
            'orderable' => true
        ],
        'price' => [
            'searchable' => true,
            'orderable' => true
        ],
        'start_date' => [
            'searchable' => true,
            'orderable' => true
        ],
        'expiry_date' => [
            'searchable' => true,
            'orderable' => true
        ],
        'status' => [
            'searchable' => true,
            'orderable' => true
        ],
        'warranty_duration' => [
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
            'proposer' => [
                "model" => \App\Models\Proposer::class,
                'foreign_key' => 'proposer_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
            'insurer' => [
                "model" => \App\Models\InsurerExtend::class,
                'foreign_key' => 'insurer_id',
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
            'dealer' => [
                "model" => \App\Models\Company::class,
                'foreign_key' => 'dealer_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ]
        ],
        "hasMany" => [
            'appointments' => [
                "model" => \App\Models\WarrantyDocument::class,
                'foreign_key' => 'warranty_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ]
        ]
    ];

    public function insurer()
    {
        return $this->belongsTo(\App\Models\Company::class, 'insurer_id');
    }

    public function dealer()
    {
        return $this->belongsTo(\App\Models\Company::class, 'dealer_id');
    }

    public function proposer()
    {
        return $this->belongsTo(\App\Models\Proposer::class, 'proposer_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    public function documents()
    {
        $user = Auth::user();
        if (Auth::guard('mobile')->check() || $user->category == 'dealer' || $user->category == 'all_cars' || $user->category == 'insurer') {
            return $this->hasMany(\App\Models\WarrantyDocument::class);
        } else {
            return $this->hasMany(\App\Models\WarrantyDocument::class)
                ->where('type', '!=', 'log')
                ->where('type', '!=', 'assessment');
        }
    }

    public function getFormatStartDateAttribute()
    {
        if ($this->start_date != null) {
            return Carbon::parse($this->start_date)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatValidTillAttribute()
    {
        if ($this->quote_valid_till != null) {
            return Carbon::parse($this->quote_valid_till)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatSubmittedAtAttribute()
    {
        if ($this->submitted_at != null) {
            return Carbon::parse($this->submitted_at)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getEndDateAttribute()
    {
        if ($this->start_date != null) {
            return Carbon::parse($this->start_date)->addMonths($this->warranty_duration * 12)->subDays(1)->format("d/m/Y");
        } else {
            return '-';
        }
    }

    public function getCreatedAtAttribute($value)
    {
        if ($value != null) {
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getUpdatedAtAttribute($value)
    {
        if ($value != null) {
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getWarrantyPeriodAttribute()
    {
        if ($this->warranty_duration > 1) {
            return (float) $this->warranty_duration . '-years';
        } elseif ($this->warranty_duration == 1) {
            return (float) $this->warranty_duration . '-year';
        } else {
            return (float) $this->warranty_duration * 12 . '-months';
        }
    }

    public function getFormatMileageCoverageAttribute()
    {
        if ($this->mileage_coverage != null) {
            return number_format($this->mileage_coverage) . " KM";
        } else {
            return "-";
        }
    }

    public function getFormatPriceAttribute()
    {
        if ($this->price != null) {
            if ($this->extended) {
                $exp_created_at = explode('/', $this->created_at);
                if ("$exp_created_at[2]-$exp_created_at[1]-$exp_created_at[0]" < "2021-10-05") {
                    return "$" . number_format($this->price * 1.05);
                } else {
                    return "$" . number_format($this->price * 1.1);
                }
            } else {
                return "$" . number_format($this->price);
            }
        } else {
            return "-";
        }
    }

    public function getFormatPremiumPerYearAttribute()
    {
        if ($this->price != null && $this->warranty_duration != null && $this->warranty_duration != 0) {
            return "$" . number_format($this->price / $this->warranty_duration, 2);
        } else {
            return null;
        }
    }

    public function getFormatMaxClaimAttribute()
    {
        if ($this->max_claim != null) {
            return "$" . number_format($this->max_claim);
        } else {
            return "-";
        }
    }

    public function driver()
    {
        return $this->morphOne(Driver::class, 'ownable');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'payment_for');
    }
}
