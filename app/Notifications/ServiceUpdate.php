<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceUpdate extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Service $service, string $updatedColumn)
    {
        $normalizedColumn = ucwords(str_replace('_', ' ', $updatedColumn));

        $this->model = $service;
        $this->title = $service->vehicle->registration_no . ': Service ' . $normalizedColumn . ' has been updated';
        $this->message = 'The ' . $normalizedColumn . ' is ' . $service->$updatedColumn;

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
