<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class CustomerVehicle extends Model
{
    use HasFactory, LaravelVueDatatableTrait;

    protected $table = 'customer_vehicle';

    protected $fillable = ['customer_id', 'vehicle_id', 'granted_by'];

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'created_at' => [
            'searchable' => false,
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
        ]
    ];

    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
