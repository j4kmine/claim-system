<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use App\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LaravelVueDatatableTrait;

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
        ],
        'name' => [
            'searchable' => true,
        ],
        'email' => [
            'searchable' => true,
        ],
        'category' => [
            'searchable' => true,
        ],
        'role' => [
            'searchable' => true,
        ],
        'status' => [
            'searchable' => true,
        ]
    ];

    protected $dataTableRelationships = [
        "belongsTo" => [
            'company' => [
                "model" => \App\Models\Company::class,
                'foreign_key' => 'company_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
        ],
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'category',
        'role',
        'status',
        'notification_email'
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
    
    protected $appends = [
        'company_name',
    ];

    const ROLES = [
        'admin', 'support_staff'
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function company() {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function getCompanyNameAttribute() {
        if($this->company_id != null){
            return $this->company->name;
        } else {
            return 'All Cars';
        }
    }
    
}
