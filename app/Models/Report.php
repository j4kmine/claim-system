<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use Carbon\Carbon;

class Report extends Model
{
    //
    protected $table = "claims";
    use LaravelVueDatatableTrait;
    
    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
        ],
        'ref_no' => [
            'searchable' => true,
        ],
        'policy_name' => [
            'searchable' => true,
        ],
        'policy_certificate_no' => [
            'searchable' => true,
        ],
        'total_claim_amount' => [
            'searchable' => false,
        ],
        'date_of_loss' => [
            'searchable' => true,
            'orderable' => true
        ],
        'status' => [
            'searchable' => true,
        ],
        'created_at' => [
            'searchable' => true,
        ],
        'approved_at' => [
            'searchable' => true,
        ],
        'repaired_at' => [
            'searchable' => true,
        ]
    ];

    protected $dataTableRelationships = [
        "hasMany" => [
            'items' => [
                "model" => \App\Models\ClaimItem::class,
                'foreign_key' => 'claim_id',
                'columns' => [
                    'item' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
        ],
        "belongsTo" => [
            'vehicle' => [
                "model" => \App\Models\Vehicle::class,
                'foreign_key' => 'vehicle_id',
                'columns' => [
                    'registration_no' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                    'chassis_no' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
            'company' => [
                "model" => \App\Models\InsurerExtend::class,
                'foreign_key' => 'insurer_id',
                'columns' => [
                    'company.name' => [
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
        ]
    ];

    public function getPolicyCoverageFromAttribute($value){
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getPolicyCoverageToAttribute($value){
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getDateOfNotificationAttribute($value){
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }
    
    public function getTotalClaimAmountAttribute($value){
        return '$'.$value;
    }

    public function getCreatedAtAttribute($value){
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getApprovedAtAttribute($value){
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }
    
    public function getDateOfLossAttribute($value){
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getRepairedAtAttribute($value){
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function vehicle() {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    public function company() {
        return $this->belongsTo(\App\Models\Company::class, 'insurer_id');
    }

    public function items() {
        return $this->hasMany(\App\Models\ClaimItem::class, 'claim_id');
    }

    public function workshop() {
        return $this->belongsTo(\App\Models\Company::class, 'workshop_id');
    }
}
