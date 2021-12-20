<?php

namespace App\Console\Commands;

use App\Models\Reports;
use App\Models\Service;
use App\Notifications\AppointmentReminder;
use Illuminate\Console\Command;

class CheckAppointmentDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:appointment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check Appointment Date
     * For 
     * 1. Service
     * 2. Reports
     * 3. Insurance (module is not created yet) - not implemented
     */
    public function handle()
    {
        /**
         * 1) booked appointment (3 intervals)
         * - 3 days before appointment
         * - 1 day before appointment
         * - actual date of appointment (in the morning e.g. 8am)
         * 
         * 2) servicing appointment due (3 intervals)
         * 1 month before due date
         * 2 weeks before due date
         * due date
         */

        // Remind 1 month before
        $date1MonthBefore = now()->addMonths(1);

        // Remind 2 weeks before
        $date2WeeksBefore = now()->addDay(14);

        // Remind 3 days before
        $date3DaysBefore = now()->addDays(3);

        // Remind 1 day before
        $date1DayBefore = now()->addDays(1);

        // Remind today
        $dateToday = now();

        /**
         * Check appointment date for service
         */
        $services = Service::whereDate('appointment_date', $date1MonthBefore)
            ->orWhereDate('appointment_date', $date2WeeksBefore)
            ->orWhereDate('appointment_date', $dateToday)->get();
        foreach ($services as $service) {
            $service->customer->notify(new AppointmentReminder($service, 'appointment_date'));
        }

        /**
         * Check appointment date for reports
         */
        $reports = Reports::whereDate('workshop_visit_date', $date3DaysBefore)
            ->orWhereDate('workshop_visit_date', $date1DayBefore)
            ->orWhereDate('workshop_visit_date', $dateToday)->get();
        foreach ($reports as $report) {
            $report->customer->notify(new AppointmentReminder($report, 'workshop_visit_date'));
        }

        return 0;
    }
}
