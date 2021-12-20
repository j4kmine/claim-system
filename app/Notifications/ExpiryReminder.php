<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiryReminder extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($model, string $expiryColumn = 'expiry_date')
    {
        $daysRemaining = (int) now()->diff($model->$expiryColumn)->format("%a");

        $this->model = $model;
        $this->title = $model->vehicle->registration_no . ' : ' . $daysRemaining . ' days before your ' . class_basename($model) . ' get expired';
        $this->message = 'Your ' . class_basename($model) . ' will be expired at ' . $model->$expiryColumn->format('Y-m-d');

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
