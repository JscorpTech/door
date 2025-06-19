<?php

namespace App\Services;

use Exception;

class PushNotificationService
{
    public function getMessageKeyData(string $userType):array
    {
        $customer = [
            'order_pending_message',
            'order_confirmation_message',
            'order_processing_message',
            'out_for_delivery_message',
            'order_delivered_message',
            'order_returned_message',
            'order_failed_message',
            'order_canceled',
            'order_refunded_message',
            'refund_request_canceled_message',
            'message_from_delivery_man',
            'message_from_admin',
            'message_from_seller',
            'fund_added_by_admin_message',
        ];
        $vendor = [
            'new_order_message',
            'refund_request_message',
            'order_edit_message',
            'withdraw_request_status_message',
            'message_from_customer',
            'message_from_delivery_man',
            'delivery_man_assign_by_admin_message',
            'order_delivered_message',
            'order_canceled',
            'order_refunded_message',
            'refund_request_canceled_message',
            'refund_request_status_changed_by_admin',
            'product_request_approved_message',
            'product_request_rejected_message'
        ];
        $delivery_man = [
            'new_order_assigned_message',
            'expected_delivery_date',
            'delivery_man_charge',
            'order_canceled',
            'order_rescheduled_message',
            'order_edit_message',
            'message_from_seller',
            'message_from_admin',
            'message_from_customer',
            'cash_collect_by_admin_message',
            'cash_collect_by_seller_message',
            'withdraw_request_status_message',
        ];
        return match ($userType) {
            'customer' => $customer,
            'seller' => $vendor,
            'delivery_man' => $delivery_man,
        };
    }
    public function getAddData(string $userType, string $value):array
    {
        return [
            'user_type' => $userType,
            'key' => $value,
            'message' => 'customize your' . ' ' . str_replace('_', ' ', $value) . ' ' . 'message',
            'created_at' => now(),
            'updated_at' => now(),
        ];

    }
    public function getUpdateData(object $request,string $message,string $status,string $lang):array
    {
        return [
            'message'=>$request->$message[array_search('en', $request->$lang)],
            'status'=>$request->$status ?? false,
            'updated_at'=>now(),
        ];
    }

    public function getFCMCredentialsArray(object|array $request): array
    {
        return [
            'apiKey' => $request['apiKey'],
            'authDomain' => $request['authDomain'],
            'projectId' => $request['projectId'],
            'storageBucket' => $request['storageBucket'],
            'messagingSenderId' => $request['messagingSenderId'],
            'appId' => $request['appId'],
            'measurementId' => $request['measurementId'],
        ];
    }

    /**
     * @throws Exception
     */
    public function firebaseConfigFileGenerate(array $config): void
    {
        $apiKey = $config['apiKey'] ?? '';
        $authDomain = $config['authDomain'] ?? '';
        $projectId = $config['projectId'] ?? '';
        $storageBucket = $config['storageBucket'] ?? '';
        $messagingSenderId = $config['messagingSenderId'] ?? '';
        $appId = $config['appId'] ?? '';
        $measurementId = $config['measurementId'] ?? '';

        $filePaths = [
            base_path('firebase-messaging-sw.js'),
            base_path('public/firebase-messaging-sw.js')
        ];

        $fileContent = <<<JS
            importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
            importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
            importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

            firebase.initializeApp({
                apiKey: "{$apiKey}",
                authDomain: "{$authDomain}",
                projectId: "{$projectId}",
                storageBucket: "{$storageBucket}",
                messagingSenderId: "{$messagingSenderId}",
                appId: "{$appId}",
                measurementId: "{$measurementId}"
            });

            const messaging = firebase.messaging();
            messaging.setBackgroundMessageHandler(function(payload) {
                return self.registration.showNotification(payload.data.title, {
                    body: payload.data.body || '',
                    icon: payload.data.icon || ''
                });
            });
            JS;


        foreach ($filePaths as $filePath) {
            $this->writeToFile($filePath, $fileContent);
        }
    }

    /**
     * @throws Exception
     */
    private function writeToFile(string $filePath, string $fileContent): void
    {
        try {
            if (!file_exists($filePath)) {
                if (file_put_contents($filePath, '') === false) {
                    throw new Exception("Failed to create file: $filePath");
                }
            }

            if (!is_writable($filePath)) {
                throw new Exception("File exists but is not writable: $filePath");
            }

            if (file_put_contents($filePath, $fileContent, LOCK_EX) === false) {
                throw new Exception("Failed to write to file: $filePath");
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

}
