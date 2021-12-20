<?php

namespace App\Notifications;

use App\Models\Warranty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarrantyUpdate extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Warranty $warranty, string $updatedColumn)
    {
        $normalizedColumn = ucwords(str_replace('_', ' ', $updatedColumn));

        $this->model = $warranty;
        $this->title = $warranty->vehicle->registration_no . ': Warranty ' . $normalizedColumn . ' has been updated';
        $this->message = 'The ' . $normalizedColumn . ' is ' . $warranty->$updatedColumn;

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
