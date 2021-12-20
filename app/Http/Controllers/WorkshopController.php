<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ServicingSlot;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    use ApiResponser;

    /**
     * Return slots that
     * workshop has
     */
    public function slots(Company $company)
    {
        return $this->success($company->servicing_slots);
    }

    /**
     * Generate times
     * that the workshop has
     * based on the given date
     */
    public function times(Request $request, Company $company)
    {
        $attr = $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ]);

        // Get full day name i.e. Sunday, Monday, etc..
        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $attr['date']);
        $day = $date->format('l');

        // Get servicing slots
        $slot = ServicingSlot::where('workshop_id', $company->id)
            ->where('day', $day)->first();

        // If the slot not available, return error
        if (!$slot) return $this->error('Timeslot Not Available on ' . $day, 422);

        /**
         * Calculate the time that available
         */
        $timeSlots = [];
        $timeStart = Carbon::createFromFormat('Y-m-d H:i', $attr['date'] . ' ' . $slot->time_start);
        $timeEnd = Carbon::createFromFormat('Y-m-d H:i', $attr['date'] . ' ' . $slot->time_end);
        $minutes = 0;

        while ($timeStart->addMinutes($minutes) < $timeEnd) {
            $timeSlots = array_merge($timeSlots, [$timeStart->format('H:i')]);
            $minutes = $slot->interval;
        }

        // Return time slots
        return $this->success($timeSlots);
    }

    public function serviceTypes(Company $company)
    {
        return $this->success($company->service_types);
    }
}
