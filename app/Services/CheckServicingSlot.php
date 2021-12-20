<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Company;
use App\Models\ServicingSlot;
use App\Models\Service;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\DB;

class CheckServicingSlot
{
    /**
     * Check it timeslot is available
     * 1. Check if the timeslot is available
     * 2. Check if the quota hasn't meet yet
     * 
     * @param Company $company
     * @param Carbon $appointment
     * @return array
     */
    public function handle(Company $company, Carbon $appointment, $is_web = false): array
    {
        $appointment_time = $appointment->format('H:i:s');
        $slot = ServicingSlot::where('workshop_id', $company->id)
            ->where('day', $appointment->format('l'))
            ->where('status', 'active')
            ->where('time_start', '<=', $appointment_time)
            ->where('time_end', '>', $appointment_time)
            ->first();
        if ($slot == null){
            if ($is_web) return [false, 'Timeslot unavailable'];
            return [false, 'timeslot.not.available'];
        }

        // get the interval range and store it into array
        $start = new DateTime($slot->time_start);
        $end = new DateTime($slot->time_end);
        $interval = DateInterval::createFromDateString($slot->interval.' min');
        $times = new DatePeriod($start, $interval, $end);
        $data = [];
        foreach ($times as $time) {
            $data[] = [$time->format('h:i a'), $time->add($interval)->format('h:i a')];
        }
        
        // filter data to get the right time from based on form book appointment
        $filtered = array_values(array_filter($data, function($data) use ($appointment){
            $date1 = DateTime::createFromFormat('h:i a', $appointment->format('h:i a'));
            $date2 = DateTime::createFromFormat('h:i a', $data[0]);
            $date3 = DateTime::createFromFormat('h:i a', $data[1]);
            return $date1 >= $date2 && $date1 < $date3;
        }));

        // get the data based on filtered data
        $appointments = Service::where('servicing_slot_id', $slot->id)
        ->whereBetween('appointment_date', [
            $appointment->format("Y-m-d")." ".date("H:i:s", strtotime($filtered[0][0])),
            $appointment->format("Y-m-d"). " ".date("H:i:s", strtotime($filtered[0][1]))
            ])
        ->get();

        // Check if there's slot available (not fully booked)
        if ($slot->slots_per_interval <= count($appointments))
        {
            if ($is_web) return [false, 'Timeslot Fully Booked'];
            return [false, 'timeslot.fully.booked'];
        }

        return [true, null];
    }

}
