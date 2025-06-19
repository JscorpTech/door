<?php

namespace App\Listeners;

use App\Events\PasswordResetEvent;
use App\Traits\EmailTemplateTrait;

class PasswordResetListener
{
    use EmailTemplateTrait;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PasswordResetEvent $event): void
    {
        $this->sendMail($event);
    }

    private function sendMail(PasswordResetEvent $event):void{
        $email = $event->email;
        $data = $event->data;
        $this->sendingMail(sendMailTo: $email,userType: $data['userType'],templateName: $data['templateName'],data: $data);
    }
}
