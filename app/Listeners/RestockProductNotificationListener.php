<?php

namespace App\Listeners;

use App\Events\RestockProductNotificationEvent;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;

class RestockProductNotificationListener
{
    use EmailTemplateTrait, PushNotificationTrait;

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
    public function handle(RestockProductNotificationEvent $event): void
    {
        $this->sendNotification($event);
    }

    private function sendNotification(RestockProductNotificationEvent $event): void
    {
        $data = $event->data;
        $this->sendNotificationToHttp([
            'message' => [
                'topic' => $data['topic'],
                'data' => [
                    'title' => (string)$data['title'],
                    'product_id' => (string)($data['product_id'] ?? ''),
                    'slug' => (string)($data['slug'] ?? ''),
                    'body' => (string)$data['description'],
                    'image' => $data['image'] ?? '',
                    'type' => (string)$data['type'] ?? '',
                    'status' => (string)$data['status'] ?? '',
                    'route' => (string)$data['route'] ?? '',
                    'is_read' => '0'
                ],
                'notification' => [
                    'title' => (string)$data['title'],
                    'body' => (string)$data['description'],
                ]
            ]
        ]);
    }
}
