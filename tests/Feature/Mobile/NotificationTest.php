<?php

namespace Tests\Feature\Mobile;

use App\Models\Reports;
use Tests\TestCase;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AppointmentReminder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_will_get_3_days_notifications_before_visit_date()
    {
        $this->login();

        $reports = Reports::factory()->create([
            'workshop_visit_date' => now()->addDays(3),
            'customer_id' => Auth::user()->id
        ]);

        $this->artisan('check:appointment');

        $this->assertEquals('App\Notifications\AppointmentReminder', DB::table('notifications')->get()->toArray()[1]->type);
        $this->assertEquals(Auth::user()->id, DB::table('notifications')->get()->toArray()[1]->notifiable_id);
    }

    /** @test */
    public function customer_will_get_1_day_notifications_before_visit_date()
    {
        $this->login();

        $reports = Reports::factory()->create([
            'workshop_visit_date' => now()->addDays(1),
            'customer_id' => Auth::user()->id
        ]);

        $this->artisan('check:appointment');

        $this->assertEquals('App\Notifications\AppointmentReminder', DB::table('notifications')->get()->toArray()[1]->type);
        $this->assertEquals(Auth::user()->id, DB::table('notifications')->get()->toArray()[1]->notifiable_id);
    }

    /** @test */
    public function customer_will_get_today_notifications()
    {
        $this->login();

        $reports = Reports::factory()->create([
            'workshop_visit_date' => now(),
            'customer_id' => Auth::user()->id
        ]);

        $this->artisan('check:appointment');

        $this->assertEquals('App\Notifications\AppointmentReminder', DB::table('notifications')->get()->toArray()[1]->type);
        $this->assertEquals(Auth::user()->id, DB::table('notifications')->get()->toArray()[1]->notifiable_id);
    }

    /** @test */
    public function customer_will_get_one_month_notification_before_appointment_date()
    {
        $this->login();

        $service = Service::factory()->create([
            'appointment_date' => now()->addMonths(1),
            'customer_id' => Auth::user()->id
        ]);

        $this->artisan('check:appointment');

        $this->assertEquals('App\Notifications\AppointmentReminder', DB::table('notifications')->first()->type);
        $this->assertEquals(Auth::user()->id, DB::table('notifications')->first()->notifiable_id);
    }

    /** @test */
    public function customer_will_get_two_weeks_notification_before_appointment_date()
    {
        $this->login();

        $service = Service::factory()->create([
            'appointment_date' => now()->addDays(14),
            'customer_id' => Auth::user()->id
        ]);

        $this->artisan('check:appointment');

        $this->assertEquals('App\Notifications\AppointmentReminder', DB::table('notifications')->first()->type);
        $this->assertEquals(Auth::user()->id, DB::table('notifications')->first()->notifiable_id);
    }

    /** @test */
    public function customer_will_get_today_notification()
    {
        $this->login();

        $service = Service::factory()->create([
            'appointment_date' => now(),
            'customer_id' => Auth::user()->id
        ]);

        $this->artisan('check:appointment');

        $this->assertEquals('App\Notifications\AppointmentReminder', DB::table('notifications')->first()->type);
        $this->assertEquals(Auth::user()->id, DB::table('notifications')->first()->notifiable_id);
    }
}
