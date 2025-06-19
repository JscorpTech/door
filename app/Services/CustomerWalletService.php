<?php

namespace App\Services;

use App\Traits\PushNotificationTrait;

class CustomerWalletService
{
    use PushNotificationTrait;

    public function sendPushNotificationMessage(object $request, object $customer): bool
    {
        $customerFCMToken = $customer?->cm_firebase_token;
        if (!empty($customerFCMToken)) {
            $lang = $customer?->app_language ?? getDefaultLanguage();
            $value = $this->pushNotificationMessage('fund_added_by_admin_message', 'customer', $lang);
            if ($value) {
                $data = [
                    'title' => setCurrencySymbol(amount: currencyConverter(amount: $request['amount']), currencyCode: getCurrencyCode(type: 'default')) . ' ' . translate('_fund_added'),
                    'description' => $value,
                    'image' => '',
                    'type' => 'wallet'
                ];
                $this->sendPushNotificationToDevice($customerFCMToken, $data);
            }
        }

        return true;
    }
}
