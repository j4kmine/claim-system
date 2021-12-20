<?php

namespace App\Notifications;

use App\PushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

abstract class BaseNotification extends Notification
{
    public $title, $message, $image;

    public $model;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        string $title,
        string $message,
        ?string $image = null
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->image = $image;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Send push notification
     */
    protected function push()
    {
        if (!App::runningUnitTests()) {
            $fcm = new PushNotification($this->title, $this->message, $this->image);
    
            $fcm->to($this->model->customer)->send();
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $output = [
            'title' => $this->title,
            'message' => $this->message,
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id
        ];

        if ($this->image != null) {
            $output = array_merge($output, ['image' => $this->image]);
        }

        return $output;
    }
}
