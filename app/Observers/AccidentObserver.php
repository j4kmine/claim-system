<?php

namespace App\Observers;

use App\Models\Reports;
use App\Models\AccidentActionLog;
use Illuminate\Support\Facades\Auth;
use App\Activities\AccidentInspection;

class AccidentObserver
{
    /**
     * Handle the Reports "created" event.
     *
     * @param  \App\Models\Reports  $reports
     * @return void
     */
    public function created(Reports $reports)
    {
        $userClass = get_class(Auth::user());
        $isCustomer = $userClass == 'App\Models\Customer';

        AccidentActionLog::create([
            'accident_id' => $reports->id,
            'status' => $reports->status,
            'user_id' => !$isCustomer ? Auth::user()->id : null,
            'log' => 'Accident Reporting '
                . $reports->ref_no
                . ' with status '
                . unslugify($reports->status)
                . ' created by '
                . Auth::user()->name
                . '.',
        ]);

        /**
         * Customer 
         */
        if ($isCustomer) {
            if ($reports->workshop_visit_date != null) {
                Auth::user()->notify(new AccidentInspection($reports));
            }

            // ..
        }
    }

    /**
     * Handle the Reports "updated" event.
     *
     * @param  \App\Models\Reports  $reports
     * @return void
     */
    public function updated(Reports $reports)
    {
        $changes = $reports->getChanges();

        $userClass = get_class(Auth::user());
        $isCustomer = $userClass == 'App\Models\Customer';

        // Check if status updated
        if (array_key_exists('status', $changes)) {
            AccidentActionLog::create([
                'user_id' => !$isCustomer ? Auth::user()->id : null,
                'accident_id' => $reports->id,
                'status' => $reports->status,
                'log' => 'Accident Reporting '
                    . $reports->ref_no
                    . ' status changed from '
                    . $reports->getOriginal('status')
                    . ' to '
                    . $reports->status
                    . ' by '
                    . Auth::user()->name . '.'
            ]);
        }

        /**
         * Customer 
         */
        if ($isCustomer) {
            if ($reports->workshop_visit_date != null) {
                Auth::user()->notify(new AccidentInspection($reports));
            }

            // ..
        }
    }

    /**
     * Handle the Reports "deleted" event.
     *
     * @param  \App\Models\Reports  $reports
     * @return void
     */
    public function deleted(Reports $reports)
    {
        //
    }

    /**
     * Handle the Reports "restored" event.
     *
     * @param  \App\Models\Reports  $reports
     * @return void
     */
    public function restored(Reports $reports)
    {
        //
    }

    /**
     * Handle the Reports "force deleted" event.
     *
     * @param  \App\Models\Reports  $reports
     * @return void
     */
    public function forceDeleted(Reports $reports)
    {
        //
    }
}
