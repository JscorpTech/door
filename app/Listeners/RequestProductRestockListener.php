<?php

namespace App\Listeners;

use App\Events\RequestProductRestockEvent;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;

class RequestProductRestockListener
{
    use PushNotificationTrait, EmailTemplateTrait;

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
    public function handle(RequestProductRestockEvent $event): void
    {
        $this->sendNotification($event);
    }

    private function sendNotification(RequestProductRestockEvent $event): void
    {
        $data = $event->data;

        if (isset($data['firebase_token'])) {
            $postData = [
                'message' => [
                    'token' => $data['firebase_token'],
                    'data' => [
                        'title' => (string)$data['title'],
                        'body' => (string)$data['body'],
                        'image' => (string)($data['image'] ?? ''),
                    ],
                    'notification' => [
                        'title' => (string)$data['title'],
                        'body' => (string)$data['body'],
                    ]
                ]
            ];
            $this->sendNotificationToHttp($postData);
        }
    }
}
