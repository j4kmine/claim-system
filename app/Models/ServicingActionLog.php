<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class ServicingActionLog extends Model
{
    use HasFactory, LaravelVueDatatableTrait;

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false
        ],
        'log' => [
            'searchable' => true,
        ],
        'created_at' => [
            'searchable' => true,
        ],
    ];

    protected $table = 'servicing_action_logs';

    protected $guarded = [];
}
