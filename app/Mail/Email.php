<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;
use App\Models\Claim;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $subject;
    public $data;
    public $view;
    public $files;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $subject, $data, $view, $files = null)
    {
        //
        $this->name = $name;
        $this->subject = $subject;
        $this->data = $data;
        $this->view = $view;
        $this->files = $files;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->files != null){
            $email =  $this->subject($this->subject)->view($this->view);
            foreach($this->files as $file){
                $email = $email->attach($file['path'], [
                    'as' => $file['name'],
                    'mime' => $file['mime'],
                ]);
            }
            return $email;
        } else {
            return $this->subject($this->subject)->view($this->view);
        }
    }
}
