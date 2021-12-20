<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use Carbon\Carbon;

class Claim extends Model
{
    //
    use LaravelVueDatatableTrait;

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'insurer_ref_no' => [
            'searchable' => true,
            'orderable' => true
        ],
        'ref_no' => [
            'searchable' => true,
            'orderable' => true
        ],
        'policy_name' => [
            'searchable' => true,
            'orderable' => true
        ],
        'policy_certificate_no' => [
            'searchable' => true,
            'orderable' => true
        ],
        'total_claim_amount' => [
            'searchable' => true,
            'orderable' => true
        ],
        'date_of_loss' => [
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
            'insurerExtend' => [
                "model" => \App\Models\InsurerExtend::class,
                'foreign_key' => 'insurer_id',
                'columns' => [
                    'company.name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
        ],
    ];

    protected $fillable = ['insurer_ref_no', 'allcars_remarks', 'mileage', 'remarks', 'vehicle_id', 'ref_no', 'creator_id', 'insurer_id', 'workshop_id', 'policy_name', 'policy_certificate_no', 'policy_coverage_from', 'policy_coverage_to', 'policy_nric_uen', 'date_of_notification', 'date_of_loss', 'cause_of_damage', 'total_claim_amount', 'insurer_to_allcars_payment', 'allcars_to_workshop_payment', 'status', 'approved_at', 'repaired_at'];
    protected $appends = ['format_policy_coverage_from', 'format_policy_coverage_to', 'format_date_of_notification', 'format_date_of_loss'];
    
    public function getFormatPolicyCoverageFromAttribute(){
        $value = $this->policy_coverage_from;
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getFormatPolicyCoverageToAttribute(){
        $value = $this->policy_coverage_to;
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getFormatDateOfNotificationAttribute(){
        $value = $this->date_of_notification;
        if($value != null){
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "";
        }
    }

    public function getFormatDateOfLossAttribute(){
        $value = $this->date_of_loss;
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

    public function vehicle() {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    public function workshop() {
        return $this->belongsTo(\App\Models\Company::class, 'workshop_id');
    }

    public function insurer() {
        return $this->belongsTo(\App\Models\Company::class, 'insurer_id');
    }

    public function insurerExtend() {
        return $this->belongsTo(\App\Models\Insurer::class, 'insurer_id', 'insurer_id');
    } 

    public function documents() {
        return $this->hasMany(\App\Models\ClaimDocument::class);
    }

    public function items() {
        return $this->hasMany(\App\Models\ClaimItem::class);
    }
}
