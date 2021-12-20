<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class ServiceType extends Model
{
    use HasFactory, LaravelVueDatatableTrait;
    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
        ],
        'workshop_id' => [
            'searchable' => true,
        ],
        'name' => [
            'searchable' => true,
        ],
        'status' => [
            'searchable' => true,
        ],
        'color' => [
            'searchable' => true,
        ],
    ];
    protected $table = 'service_types';

    protected $fillable = [
        'name', 'workshop_id', 'status', 'description', 'color'
    ];
}
