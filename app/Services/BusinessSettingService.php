<?php

namespace App\Services;

use App\Events\MaintenanceModeNotificationEvent;

class BusinessSettingService
{

    public function getLanguageData(object $request, object $language): array
    {
        $languageArray = [];
        foreach (json_decode($language['value'], true) as $key => $data) {
            if ($data['code'] == $request['language']) {
                $lang = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'code' => $data['code'],
                    'status' => 1,
                    'default' => true,
                ];
            } else {
                $lang = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'default' => false,
                ];
            }
            $languageArray[] = $lang;
        }
        return $languageArray;
    }

    public function getInvoiceSettingsData(object|null $request, array|null $imageArray): array
    {
        return [
            'terms_and_condition' => $request['terms_and_condition'] ?? null,
            'business_identity' => $request['business_identity'] ?? null,
            'business_identity_value' => $request['business_identity_value'] ?? null,
            'image' => $imageArray,
        ];
    }

    public function sendMaintenanceModeNotification($status, $topic): void
    {
        $mailData = $this->getMaintenanceModeMessagesInfo(status: $status, topic: $topic, type: 'maintenance_mode');
        event(new MaintenanceModeNotificationEvent(data: $mailData));
    }

    public function getMaintenanceModeMessagesInfo($status, $topic, $user = null, $type = null): array
    {
        return [
            'topic' => $topic,
            'key' => $topic,
            'subject' => translate('Maintenance_Mode'),
            'title' => $status == 'on' ? translate('Maintenance_Mode_start') : translate('Maintenance_Mode_End'),
            'description' => $status == 'on' ? translate('we_are_currently_undergoing_maintenance') : translate('Maintenance_mode_turned_off'),
            'type' => $type,
            'user_name' => $user ? $user?->f_name : null,
            'userData' => $user,
        ];
    }
}
