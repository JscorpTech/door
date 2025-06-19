<?php

namespace App\Http\Controllers\Admin\SystemSetup;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\LoginSetupRepositoryInterface;
use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Enums\GlobalConstant;
use App\Enums\ViewPaths\Admin\SystemSetup;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\LoginSetupRequest;
use App\Traits\FileManagerTrait;
use App\Traits\SettingsTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SystemLoginSetupController extends BaseController
{
    use SettingsTrait;
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly SettingRepositoryInterface         $settingRepo,
        private readonly LoginSetupRepositoryInterface      $loginSetupRepo,
        private readonly CurrencyRepositoryInterface        $currencyRepo,
    )
    {
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        // TODO: Implement index() method.
    }

    public function getCustomerLoginSetupView(): View
    {
        $loginOptionsValue = $this->loginSetupRepo->getFirstWhere(params: ['key' => 'login_options']);
        $loginOptions = $loginOptionsValue ? json_decode($loginOptionsValue?->value ?? [], true) : [
            'manual_login' => 0,
            'otp_login' => 0,
            'social_login' => 0,
        ];
        $socialMediaForLoginValue = $this->loginSetupRepo->getFirstWhere(params: ['key' => 'social_media_for_login']);
        $socialMediaLoginOptions = $socialMediaForLoginValue ? json_decode($socialMediaForLoginValue?->value ?? [], true) : [
            'google' => 0,
            'facebook' => 0,
            'apple' => 0,
        ];
        $emailVerification = $this->loginSetupRepo->getFirstWhere(params: ['key' => 'email_verification'])?->value ?? 0;
        $phoneVerification = $this->loginSetupRepo->getFirstWhere(params: ['key' => 'phone_verification'])?->value ?? 0;

        $configStatus = $this->checkSocialLoginMediaAbility();
        return view(SystemSetup::CUSTOMER_LOGIN_SETUP[VIEW], [
            'socialMediaLoginOptions' => $socialMediaLoginOptions,
            'loginOptions' => $loginOptions,
            'emailVerification' => $emailVerification,
            'phoneVerification' => $phoneVerification,
            'configStatus' => $configStatus,
        ]);
    }

    public function updateCustomerLoginSetup(Request $request): RedirectResponse
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::error(translate('you_can_not_update_this_on_demo_mode'));
            return back();
        }
        $this->loginSetupRepo->updateOrInsert(key: 'login_options', value: json_encode([
            'manual_login' => $request->get('manual_login', 0),
            'otp_login' => $request->get('otp_login', 0),
            'social_login' => $request->get('social_login', 0),
        ]));
        $this->loginSetupRepo->updateOrInsert(key: 'social_media_for_login', value: json_encode([
            'google' => $request->get('google_login', 0),
            'facebook' => $request->get('facebook_login', 0),
            'apple' => $request->get('apple_login', 0),
        ]));

        $this->loginSetupRepo->updateOrInsert(key: 'email_verification', value: $request->get('email_verification', 0));
        $this->loginSetupRepo->updateOrInsert(key: 'phone_verification', value: $request->get('phone_verification', 0));
        Toastr::success(translate('Login_settings_updated'));
        return back();
    }

    public function getConfigValidation(Request $request): JsonResponse
    {
        $status = 1;
        if ($request['key'] == 'google' || $request['key'] == 'facebook' || $request['key'] == 'apple') {
            $status = $this->checkSocialLoginMediaAbility()[$request['key']];
        }

        if ($request['key'] == 'email') {
            $emailServicesSmtp = getWebConfig(name: 'mail_config');
            if ($emailServicesSmtp['status'] == 0) {
                $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
            }
            $status = $emailServicesSmtp['status'] == 1 ? 1 : 0;
        }

        if ($request['key'] == 'otp' || $request['key'] == 'otp-login') {
            $paymentGatewayPublishedStatus = config('get_payment_publish_status') ?? 0;
            if ($paymentGatewayPublishedStatus) {
                $smsGatewaysList = $this->settingRepo->getListWhereIn(
                    whereInFilters: ['settings_type' => ['sms_config']],
                    dataLimit: 'all',
                );
            } else {
                $smsGatewaysList = $this->settingRepo->getListWhereIn(
                    whereInFilters: ['settings_type' => ['sms_config'], 'key_name' => GlobalConstant::DEFAULT_SMS_GATEWAYS],
                    dataLimit: 'all',
                );
            }
            $status = 0;
            foreach ($smsGatewaysList as $smsGateway) {
                $status = $smsGateway['is_active'] == 1 ? 1 : $status;
            }

            $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification');
            $firebaseOTPVerificationStatus = (int)($firebaseOTPVerification && $firebaseOTPVerification['status'] && $firebaseOTPVerification['web_api_key']);
            $status = (int)($firebaseOTPVerificationStatus || $status);
        }

        $data = [
            'type' => $request['key'],
        ];
        return response()->json([
            'status' => $status,
            'htmlView' => view(SystemSetup::CUSTOMER_CONFIG_VALIDATION[VIEW], ['data' => $data])->render()
        ]);
    }

    public function getOtpSetupView(): View
    {
        $maximumOtpHit = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'maximum_otp_hit'])->value ?? 0;
        $otpResendTime = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'otp_resend_time'])->value ?? 0;
        $temporaryBlockTime = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'temporary_block_time'])->value ?? 0;
        $maximumLoginHit = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'maximum_login_hit'])->value ?? 0;
        $temporaryLoginBlockTime = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'temporary_login_block_time'])->value ?? 0;
        return view(SystemSetup::OTP_SETUP[VIEW], compact('maximumOtpHit', 'otpResendTime',
            'temporaryBlockTime', 'maximumLoginHit', 'temporaryLoginBlockTime'));
    }

    public function updateOtpSetup(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'maximum_otp_hit', value: $request['maximum_otp_hit']);
        $this->businessSettingRepo->updateOrInsert(type: 'otp_resend_time', value: $request['otp_resend_time']);
        $this->businessSettingRepo->updateOrInsert(type: 'temporary_block_time', value: $request['temporary_block_time']);
        $this->businessSettingRepo->updateOrInsert(type: 'maximum_login_hit', value: $request['maximum_login_hit']);
        $this->businessSettingRepo->updateOrInsert(type: 'temporary_login_block_time', value: $request['temporary_login_block_time']);
        clearWebConfigCacheKeys();
        Toastr::success(translate('Settings_updated'));
        return back();
    }

    public function getLoginSetupView(): View
    {
        return view(SystemSetup::LOGIN_URL_SETUP[VIEW]);
    }

    public function updateLoginSetupView(LoginSetupRequest $request): RedirectResponse
    {
        $currentUrl = strtolower($request->url);

        if ($request['type'] == 'admin_login_url' || $request->type == 'employee_login_url') {
            $anotherType = ($request['type'] == 'admin_login_url') ? 'employee_login_url' : 'admin_login_url';
            $anotherLoginUrl = $this->businessSettingRepo->getFirstWhere(['type' => $anotherType])->value ?? '';

            if ($anotherLoginUrl != $currentUrl) {
                $this->businessSettingRepo->updateOrInsert(type: $request['type'], value: $currentUrl);
                Toastr::success(translate('Updated_successfully'));
            } else {
                Toastr::error(translate('admin_and_employee_URL_cannot_be_same'));
            }
        }

        return back();
    }

    private function checkSocialLoginMediaAbility(): array
    {
        $configStatus = [
            'google' => 0,
            'facebook' => 0,
            'apple' => 0,
        ];
        foreach (getWebConfig(name: 'social_login') as $key => $singleItem) {
            if (isset($singleItem['client_id']) && $singleItem['client_id'] && isset($singleItem['client_secret']) && $singleItem['client_secret']) {
                $configStatus[$singleItem['login_medium']] = 1;
            }
        }
        foreach (getWebConfig(name: 'apple_login') as $key => $singleItem) {
            if (
                isset($singleItem['client_id']) && $singleItem['client_id'] &&
                isset($singleItem['team_id']) && $singleItem['team_id'] &&
                isset($singleItem['key_id']) && $singleItem['key_id'] &&
                isset($singleItem['service_file']) && $singleItem['service_file']
            ) {
                $configStatus[$singleItem['login_medium']] = 1;
            }
        }

        return $configStatus;
    }
}
