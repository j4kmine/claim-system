<?php

namespace App\Activities;

use App\Models\Warranty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BuyWarranty extends BaseActivity
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Warranty $warranty)
    {
        $this->model = $warranty;
        $this->title = $warranty->vehicle->registration_no . ': Bought Warranty';
        // $this->message = 'Warranty Bought from ' . $warranty->dealer->name;
        $this->message = 'Warranty Bought';
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
