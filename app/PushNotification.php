<?php

namespace App;

use App\Models\Customer;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Throwable;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class PushNotification
{
    /**
     * Usage
     * $notif = new PushNotification( $title, $body, nullable $image )
     * 
     * - Send notification with topic
     *  $notif->topic( $topic )->send()
     * 
     * - Send notification with targeted device (need device_id on customers table)
     *  $notif->to( $customer )->send()
     */

    public $title, $body, $image;

    /**
     * Store the topic
     */
    public ?string $topic = null;

    public Customer $customer;

    public function __construct(string $title, string $body, string $image = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->image = $image;
    }

    /**
     * Set specific device token
     */
    public function to(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * Set specific topic
     */
    public function topic(string $topic)
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * Send the notification
     */
    public function send()
    {
        $messaging = Firebase::messaging();

        $notification = Notification::create(
            $this->title,
            $this->body,
            $this->image
        );

        $messages = [];

        // Send with topic
        if ($this->topic != null) {
            array_push($messages, CloudMessage::withTarget('topic', $this->topic)
                ->withNotification($notification));
        }

        // Send the customers notification to theirs devices
        // One customer can have more than one device Ids
        if ($this->customer != null) {
            foreach ($this->customer->devices as $device) {
                array_push($messages, CloudMessage::withTarget('token', $device->device_id)
                    ->withNotification($notification));
            }
        }

        // If there's messages to be sent
        if (count($messages) != 0)
            $report = $messaging->sendAll($messages);

        return $report ?? null;
    }


    // public function handle()
    // {
    //     try {
    //         $messaging = Firebase::messaging();

    //         $notification = Notification::create('test title', 'test body');

    //         $message = CloudMessage::withTarget('topic', 'test')
    //             ->withNotification($notification);

    //         return $messaging->send($message);
    //         return null;
    //     } catch (Throwable $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public function auth()
    // {
    //     $auth = app('firebase.auth');

    //     $users = $auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);
    //     $user = $auth->getUserByPhoneNumber('+6593886996');
    //     dd($user);

    //     array_map(function (\Kreait\Firebase\Auth\UserRecord $user) {
    //         dd($user);
    //     }, iterator_to_array($users));
    //     //         $serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/storage/aetnabrokers-firebase-adminsdk-g1ets-e0e8b6ac60.json');
    //     // dd('tets');
    //     //         $firebase = (new Factory)
    //     //             ->withServiceAccount(storage_path('/app/aetnabrokers-firebase-adminsdk-g1ets-e0e8b6ac60.json'))
    //     //             ->createDatabase();
    //     //         // $users = Auth::listUsers();

    //     //         $auth = $firebase->getAuth();

    //     //         $user = new Auth($defaultMaxResults = 1000, $defaultBatchSize = 1000);

    //     //         $users = $user->listUsers();
    //     //         // dd($users);
    //     //         foreach ($users as $user) {
    //     //             dd($user);
    //     //         }
    //     //         // dd($user->listUsers());
    // }
}
