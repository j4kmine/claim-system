<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use Carbon\Carbon;

class MotorActionLog extends Model
{
    //
    use LaravelVueDatatableTrait;

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
    
    protected $fillable = ['log', 'user_id', 'motor_id', 'status'];
    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format("d/m/Y H:i:s");
    }
}