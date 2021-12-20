<?php

namespace App\Observers;

use App\Models\Service;
use App\Models\ServicingActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     *
     * @param  \App\Models\Service  $service
     * @return void
     */
    public function created(Service $service)
    {
        $userClass = get_class(Auth::user());
        $isCustomer = $userClass == 'App\Models\Customer';

        ServicingActionLog::create([
            'service_id' => $service->id,
            'status' => $service->status,
            'user_id' => !$isCustomer ? Auth::user()->id : null,
            'log' => 'Servicing '
                . $service->id
                . ' with status '
                . unslugify($service->status)
                . ' created by '
                . Auth::user()->name
                . '.',
        ]);
    }


    /**
     * Handle the Service "updated" event.
     *
     * @param  \App\Models\Service  $service
     * @return void
     */
    public function updated(Service $service)
    {
        $changes = $service->getChanges();

        $userClass = get_class(Auth::user());
        $isCustomer = $userClass == 'App\Models\Customer';

        // Check if status updated
        if (array_key_exists('status', $changes)) {
            ServicingActionLog::create([
                'user_id' => !$isCustomer ? Auth::user()->id : null,
                'service_id' => $service->id,
                'status' => $service->status,
                'log' => 'Servicing '
                    . $service->id
                    . ' status changed from '
                    . $service->getOriginal('status')
                    . ' to '
                    . $service->status
                    . ' by '
                    . Auth::user()->name . '.'
            ]);
        }
    }

    /**
     * Handle the Service "deleted" event.
     *
     * @param  \App\Models\Service  $service
     * @return void
     */
    public function deleted(Service $service)
    {
        //
    }

    /**
     * Handle the Service "restored" event.
     *
     * @param  \App\Models\Service  $service
     * @return void
     */
    public function restored(Service $service)
    {
        //
    }

    /**
     * Handle the Service "force deleted" event.
     *
     * @param  \App\Models\Service  $service
     * @return void
     */
    public function forceDeleted(Service $service)
    {
        //
    }
}
