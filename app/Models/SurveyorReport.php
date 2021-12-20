<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use Carbon\Carbon;

class SurveyorReport extends Model
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
        'policy_certificate_no' => [
            'searchable' => true,
        ],
        'status' => [
            'searchable' => true,
        ],
        'surveyor_review_count' => [
            'searchable' => true,
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
                    'chassis_no' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
            'company' => [
                "model" => \App\Models\Company::class,
                'foreign_key' => 'insurer_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
        ]
    ];

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format("d/m/Y H:i:s");
    }

    public function vehicle() {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    public function company() {
        return $this->belongsTo(\App\Models\Company::class, 'insurer_id');
    }
}
