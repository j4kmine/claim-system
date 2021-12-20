<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicingAppointment extends Model
{
    use HasFactory;

    protected $table = 'servicing_appointments';

    protected $guarded = [];

    protected $appends = [
        'formatted_date', 'formatted_time'
    ];

    /**
     * Format date attribute,
     * used by the data table
     * Format: 15/15/2021
     */
    public function getFormattedDateAttribute()
    {
        $date = Carbon::createFromFormat('Y-m-d', $this->appointment_date);
        return $date->format('d/m/Y');
    }

    /**
     * Format time attribute
     * used by the data table
     * Format: 07:00AM - 08:00AM
     */
    public function getFormattedTimeAttribute()
    {
        $time = Carbon::createFromFormat('H:i:s', $this->time_start);

        $timeStart = $time->format('H:iA');
        $timeEnd = $time->addMinutes($this->interval);

        return $timeStart . ' - ' . $timeEnd->format('H:iA');
    }
}
