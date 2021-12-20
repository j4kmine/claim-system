<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class ServicingSlot extends Model
{
    use HasFactory, LaravelVueDatatableTrait, SoftDeletes;
    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
        ],
        'day' => [
            'searchable' => true,
        ],
        'time_start' => [
            'searchable' => true,
        ],
        'time_end' => [
            'searchable' => true,
        ],
        'interval' => [
            'searchable' => true,
        ],
        'slots_per_interval' => [
            'searchable' => true,
        ],
        'status' => [
            'searchable' => true,
        ],
        'total_slot' => [
            'searchable' => false,
        ],
    ];
    protected $table = 'servicing_slots';

    protected $guarded = [];

    const DAYS = [
        'Sunday', 'Monday', 'Tuesday', 'Wednesday',
        'Thursday', 'Friday', 'Saturday'
    ];

    const STATUSES = [
        'active', 'inactive'
    ];

    /** Interval in Minutes */
    const INTERVALS = [
        30, 60, 90, 120
    ];

    protected $appends = [
        'total_slot'
    ];

    public function getTimeStartAttribute($value)
    {
        return \Carbon\Carbon::create($value)->format('H:i');
    }

    public function getTimeEndAttribute($value)
    {
        return \Carbon\Carbon::create($value)->format('H:i');
    }

    public function getTotalSlotAttribute()
    {
        //calculate session
        $session = 60 / $this->interval;

        //hour
        $date1 = "2021-01-01 " . $this->time_start;
        $date2 = "2021-01-01 " . $this->time_end;
        $datetimeObj1 = new DateTime($date1);
        $datetimeObj2 = new DateTime($date2);
        $interval = $datetimeObj1->diff($datetimeObj2);

        $hour = $interval->format('%h');

        return  floor($session * $hour) * $this->slots_per_interval;
    }
}
