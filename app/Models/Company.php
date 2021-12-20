<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class Company extends Model
{
    //
    use LaravelVueDatatableTrait, HasFactory;

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'name' => [
            'searchable' => true,
            'orderable' => true
        ],
        'code' => [
            'searchable' => true,
            'orderable' => true
        ],
        'status' => [
            'searchable' => false,
            'orderable' => true
        ]
    ];

    protected $dataTableRelationships = [
        "hasMany" => [
            'details' => [
                "model" => \App\Models\Insurer::class,
                'foreign_key' => 'insurer_id',
                'columns' => [
                    'surveyor_id' => [
                        'searchable' => true,
                        'orderable' => true
                    ],
                ],
            ],
        ],
    ];

    protected $fillable = [
        'name',
        'type',
        'status',
        'code',
        'acra',
        'address',
        'contact_no',
        'contact_person',
        'contact_email',
        'description'
    ];

    protected $appends = ['count'];

    public function getCountAttribute()
    {
        return $this->users()->count();
    }

    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    public function details()
    {
        // TODO:: add in other category details
        return $this->hasOne('App\Models\Insurer', 'insurer_id');
    }

    public function servicing_slots()
    {
        return $this->hasMany(ServicingSlot::class, 'workshop_id');
    }

    public function service_types()
    {
        return $this->hasMany(ServiceType::class, 'workshop_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'workshop_id');
    }
}
