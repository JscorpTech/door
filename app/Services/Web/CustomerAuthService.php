<?php

namespace App\Services\Web;

use App\Events\EmailVerificationEvent;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class CustomerAuthService
{
    public function getCustomerVerificationToken(): string
    {
        return (env('APP_MODE') == 'live') ? rand(100000, 999999) : 123456;
    }

    public function getCustomerLoginDataReset(): array
    {
        return [
            'login_hit_count' => 0,
            'is_temp_blocked' => 0,
            'temp_block_time' => null,
            'updated_at' => now()
        ];
    }


    public function sendCustomerPhoneVerificationToken($phone, $token): array
    {
        $response = SMSModule::sendCentralizedSMS($phone, $token);
        return [
            'response' => $response,
            'status' => 'success',
            'message' => translate('please_check_your_SMS_for_OTP'),
        ];
    }

    public function sendCustomerEmailVerificationToken($user, $token): array
    {
        $emailServicesSmtp = getWebConfig(name: 'mail_config');
        if ($emailServicesSmtp['status'] == 0) {
            $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
        }
        if ($emailServicesSmtp['status'] == 1 && $user['email']) {
            try {
                $data = [
                    'userName' => $user['f_name'],
                    'subject' => translate('registration_Verification_Code'),
                    'title' => translate('registration_Verification_Code'),
                    'verificationCode' => $token,
                    'userType' => 'customer',
                    'templateName' => 'registration-verification',
                ];

                event(new EmailVerificationEvent(email: $user['email'], data: $data));
                return [
                    'status' => 'success',
                    'message' => translate('check_your_email'),
                ];
            } catch (\Exception $exception) {
                return [
                    'status' => 'error',
                    'message' => translate('email_is_not_configured') . '. ' . translate('contact_with_the_administrator'),
                ];
            }
        } else {
            return [
                'status' => 'error',
                'message' => translate('email_failed'),
            ];
        }
    }

    public function getCustomerLoginPreviousRoute($previousUrl): string
    {
        $redirectUrl = "";
        $previousUrl = url()->previous();
        if (
            strpos($previousUrl, 'checkout-complete') !== false ||
            strpos($previousUrl, 'offline-payment-checkout-complete') !== false ||
            strpos($previousUrl, 'track-order') !== false
        ) {
            $redirectUrl = route('home');
        }
        return $redirectUrl;
    }

    public function getCustomerRegisterData(object|array $request, object|array|null $referUser)
    {
        return [
            'name' => $request['f_name'] . ' ' . $request['l_name'],
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'is_active' => 1,
            'password' => bcrypt($request['password']),
            'referral_code' => Helpers::generate_referer_code(),
            'referred_by' => $referUser ? $referUser['id'] : null,
        ];
    }
}
