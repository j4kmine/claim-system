<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($model, string $appointmentColumn = 'appointment_date')
    {
        $daysRemaining = (int) now()->diff($model->$appointmentColumn)->format("%a");

        $this->model = $model;

        // Appointment date today
        if ($daysRemaining == 0) {
            $this->title = $model->vehicle->registration_no . ' : Today is  your appointment date';
            $this->message = 'Dont forget your appointment today at ' . $model->$appointmentColumn->format('H:i');
        } else {
            $this->title = $model->vehicle->registration_no . ' : ' . $daysRemaining . ' days before your appointment date';
            $this->message = 'Dont forget your appointment date on ' . $model->$appointmentColumn->format('d M Y');
        }

        $this->push();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}
