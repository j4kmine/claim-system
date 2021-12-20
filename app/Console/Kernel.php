<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('claims:archive')->daily();
        $schedule->command('update:servicing')->hourly();
        /**
         * Check expiry date.
         * Remind 30 days before expired.
         * For Warranty
         */
        $schedule->command('check:expiry')->daily();

        /**
         * Remind the appointment date:
         * 
         * 1) booked appointment (3 intervals)
         * - 3 days before appointment
         * - 1 day before appointment
         * - actual date of appointment (in the morning e.g. 8am)
         * 
         * 2) servicing appointment due (3 intervals)
         * 1 month before due date
         * 2 weeks before due date
         * due date
         * 
         * For Service and Reports modules
         */
        $schedule->command('check:appointment')->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
