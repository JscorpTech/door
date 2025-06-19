<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\LoginSetupRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Events\EmailVerificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CustomerRegistrationRequest;
use App\Models\BusinessSetting;
use App\Models\PhoneOrEmailVerification;
use App\Models\Wishlist;
use App\Services\FirebaseService;
use App\Services\Web\CustomerAuthService;
use App\Traits\EmailTemplateTrait;
use App\Models\User;
use App\Utils\CartManager;
use App\Utils\CustomerManager;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Traits\SmsGateway;

class RegisterController extends Controller
{
    use EmailTemplateTrait;

    public function __construct(
        private readonly CustomerRepositoryInterface                 $customerRepo,
        private readonly BusinessSettingRepositoryInterface          $businessSettingRepo,
        private readonly PhoneOrEmailVerificationRepositoryInterface $phoneOrEmailVerificationRepo,
        private readonly LoginSetupRepositoryInterface               $loginSetupRepo,
        private readonly CustomerAuthService                         $customerAuthService,
        private readonly FirebaseService                             $firebaseService,
    )
    {
        $this->middleware('guest:customer', ['except' => ['logout']]);
    }

    public function getRegisterView(): View
    {
        session()->put('keep_return_url', url()->previous());
        return view('web-views.customer-views.auth.register');
    }

    public function submitRegisterData(CustomerRegistrationRequest $request): JsonResponse|RedirectResponse
    {
        $referUser = $request['referral_code'] ? $this->customerRepo->getFirstWhere(params: ['referral_code' => $request['referral_code']]) : null;
        $user = $this->customerRepo->add(data: $this->customerAuthService->getCustomerRegisterData($request, $referUser));

        $phoneVerification = getLoginConfig(key: 'phone_verification');
        $emailVerification = getLoginConfig(key: 'email_verification');

        if ($request->ajax()) {
            if ($phoneVerification && !$user->is_phone_verified) {
                $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $user?->phone]);
                $this->getCustomerVerificationCheck($user, 'phone');
                return response()->json([
                    'redirect_url' => route('customer.auth.check-verification', ['identity' => base64_encode($user['phone']), 'type' => base64_encode('phone_verification')]),
                ]);
            } else if ($emailVerification && !$user->is_email_verified) {
                $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $user?->email]);
                $this->getCustomerVerificationCheck($user, 'email');
                return response()->json([
                    'redirect_url' => route('customer.auth.check-verification', ['identity' => base64_encode($user['email']), 'type' => base64_encode('email_verification')]),
                ]);
            }
            return response()->json([
                'status' => 1,
                'message' => translate('registration_successful'),
                'redirect_url' => theme_root_path() == 'default' ? route('customer.auth.login') : '',
            ]);
        } else {
            if ($phoneVerification && !$user->is_phone_verified) {
                $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $user?->phone]);
                $this->getCustomerVerificationCheck($user, 'phone');
                return redirect(route('customer.auth.check-verification', ['identity' => base64_encode($user['phone']), 'type' => base64_encode('phone_verification')]));
            }
            if ($emailVerification && !$user->is_email_verified) {
                $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $user?->email]);
                $this->getCustomerVerificationCheck($user, 'email');
                return redirect(route('customer.auth.check-verification', ['identity' => base64_encode($user['email']), 'type' => base64_encode('email_verification')]));
            }
            Toastr::success(translate('registration_success_login_now'));
            return redirect(route('customer.auth.login'));
        }
    }

    public function getCustomerVerificationCheck($user, $type, $config = []): array|RedirectResponse|string|null
    {
        $token = $this->customerAuthService->getCustomerVerificationToken();
        $phoneVerification = getLoginConfig(key: 'phone_verification');
        $emailVerification = getLoginConfig(key: 'email_verification');
        if (isset($config['phone_verification'])) {
            $phoneVerification = $config['phone_verification'];
        }
        if (isset($config['email_verification'])) {
            $emailVerification = $config['email_verification'];
        }
        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];

        if ($phoneVerification && !$user['is_phone_verified'] && $firebaseOTPVerification && $firebaseOTPVerification['status']) {
            $response = $this->firebaseService->sendOtp($user['phone']);
            if ($response['status'] == 'error') {
                Toastr::error(translate(strtolower($response['errors'])));
                return back();
            }
            $token = $response['sessionInfo'];
        } else if ($phoneVerification && !$user['is_phone_verified']) {
            $response = $this->customerAuthService->sendCustomerPhoneVerificationToken($user['phone'], $token);
            Toastr::success($response['message']);
        } else if ($emailVerification && !$user['is_email_verified']) {
            $response = $this->customerAuthService->sendCustomerEmailVerificationToken($user, $token);
            if ($response['status'] == 'error') {
                Toastr::error($response['message']);
                return back();
            }
        }
        $this->phoneOrEmailVerificationRepo->add(data: [
            'phone_or_email' => $type == 'email' ? $user['email'] : $user['phone'],
            'token' => $token,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $response ?? [];
    }

    public function verificationCheckView(Request $request): View
    {
        $phoneVerification = getLoginConfig(key: 'phone_verification');
        $emailVerification = getLoginConfig(key: 'email_verification');

        $user = $this->customerRepo->getByIdentity(filters: ['identity' => base64_decode($request['identity'])]);
        $getTime = 0;
        $userVerify = 1;
        $verifyType = '';
        if ($phoneVerification && !$user['is_phone_verified']) {
            $userVerify = 0;
            $verifyType = 'phone';
        } else if ($emailVerification && !$user['is_email_verified']) {
            $userVerify = 0;
            $verifyType = 'email';
        }

        $OTPIdentity = $request['type'] && base64_decode($request['type']) == 'phone_verification' ? $user['phone'] : $user['email'];
        $token = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $OTPIdentity]);
        if ($token) {
            $otpResendTime = getWebConfig(name: 'otp_resend_time') > 0 ? getWebConfig(name: 'otp_resend_time') : 0;
            $tokenTime = Carbon::parse($token['created_at']);
            $convertTime = $tokenTime->addSeconds($otpResendTime);
            $getTime = $convertTime > Carbon::now() ? Carbon::now()->diffInSeconds($convertTime) : 0;
        }

        return view(VIEW_FILE_NAMES['customer_auth_verify'], [
            'user' => $user,
            'user_verify' => $userVerify,
            'verifyType' => $verifyType,
            'get_time' => $getTime,
        ]);
    }

    // Customer Default Verify
    public function verifyRegistration(Request $request): RedirectResponse|JsonResponse
    {
        Validator::make($request->all(), [
            'token' => 'required',
        ]);

        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
        if ($firebaseOTPVerification && $firebaseOTPVerification['status'] && empty($request['g-recaptcha-response'])) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('ReCAPTCHA_Failed')
                ]);
            }
            Toastr::error(translate('ReCAPTCHA_Failed'));
            return redirect()->back();
        }

        $maxOTPHit = getWebConfig(name: 'maximum_otp_hit') ?? 5;
        $maxOTPHitTime = getWebConfig(name: 'otp_resend_time') ?? 60;// seconds
        $tempBlockTime = getWebConfig(name: 'temporary_block_time') ?? 600; // seconds
        $tempBlockTime = getWebConfig(name: 'temporary_block_time') ?? 5;

        $customer = $this->customerRepo->getByIdentity(filters: ['identity' => base64_decode($request['identity'])]);
        $verificationType = base64_decode($request['type']);
        $identity = $verificationType == 'email_verification' ? $customer['email'] : $customer['phone'];
        $identityType = $verificationType == 'email_verification' ? 'email' : 'phone';
        $getToken = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);

        if ($getToken) {
            if (isset($getToken->temp_block_time) && Carbon::parse($getToken->temp_block_time)->diffInSeconds() <= $tempBlockTime) {
                $time = $tempBlockTime - Carbon::parse($getToken->temp_block_time)->diffInSeconds();
                Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                return redirect()->back();
            }

            if ($getToken['is_temp_blocked'] == 1 && Carbon::parse($getToken['updated_at'])->DiffInSeconds() >= $tempBlockTime) {
                $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                    'otp_hit_count' => 0,
                    'is_temp_blocked' => 0,
                    'temp_block_time' => null,
                ]);
            }

            if ($getToken['otp_hit_count'] >= $maxOTPHit && Carbon::parse($getToken['updated_at'])->DiffInSeconds() < $maxOTPHitTime && $getToken['is_temp_blocked'] == 0) {
                $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                    'is_temp_blocked' => 1,
                    'temp_block_time' => now(),
                ]);

                $time = $tempBlockTime - Carbon::parse($getToken['temp_block_time'])->DiffInSeconds();
                $errorMsg = translate('Too_many_attempts.') . ' ' . translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
                if (request()->ajax()) {
                    return response()->json([
                        'status' => 0,
                        'message' => $errorMsg
                    ]);
                }
                Toastr::error($errorMsg);
                return redirect()->back();
            }

            if ($identityType == 'phone' && $firebaseOTPVerification && $firebaseOTPVerification['status']) {
                $firebaseVerify = $this->firebaseService->verifyOtp($getToken['token'], $getToken['phone_or_email'], $request['token']);
                $tokenVerifyStatus = (boolean)($firebaseVerify['status'] == 'success');
                if (!$tokenVerifyStatus) {
                    $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                        'otp_hit_count' => ($getToken['otp_hit_count'] + 1),
                        'updated_at' => now(),
                        'temp_block_time' => null,
                    ]);
                    Toastr::error(translate(strtolower($firebaseVerify['errors'])));
                    return back();
                }
            } else {
                $tokenVerify = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity, 'token' => $request['token']]);
                $tokenVerifyStatus = (boolean)$tokenVerify;
            }

            if ($tokenVerifyStatus) {
                $data = $verificationType == 'phone_verification' ? ['is_phone_verified' => 1] : ['is_email_verified' => 1];
                $this->customerRepo->updateWhere(params: ['id' => $customer['id']], data: $data);
                $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $identity]);
                Toastr::success(translate('verification_done_successfully'));
                return redirect(route('customer.auth.login'));
            } else {
                if (isset($getToken->temp_block_time) && Carbon::parse($getToken->temp_block_time)->diffInSeconds() <= $tempBlockTime) {
                    $time = $tempBlockTime - Carbon::parse($getToken->temp_block_time)->diffInSeconds();
                    Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                } elseif ($getToken['is_temp_blocked'] == 1 && isset($getToken->created_at) && Carbon::parse($getToken->created_at)->diffInSeconds() >= $tempBlockTime) {
                    $this->phoneOrEmailVerificationRepo->update(id: $getToken['id'], data: [
                        'otp_hit_count' => 1,
                        'is_temp_blocked' => 0,
                        'temp_block_time' => null,
                        'updated_at' => now(),
                    ]);
                } elseif ($getToken['otp_hit_count'] >= $maxOTPHit && $getToken['is_temp_blocked'] == 0) {
                    $this->phoneOrEmailVerificationRepo->update(id: $getToken['id'], data: [
                        'is_temp_blocked' => 1,
                        'temp_block_time' => now(),
                        'updated_at' => now(),
                    ]);

                    $time = $tempBlockTime - Carbon::parse($getToken['temp_block_time'])->diffInSeconds();
                    Toastr::error(translate('too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                } else {
                    $this->phoneOrEmailVerificationRepo->update(id: $getToken['id'], data: [
                        'otp_hit_count' => $getToken['otp_hit_count'] + 1,
                        'updated_at' => now(),
                    ]);
                }
                Toastr::error(translate('invalid_OTP'));
                return back();
            }
        } else {
            $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);

            if ($verificationData) {
                if (isset($verificationData->temp_block_time) && Carbon::parse($verificationData->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                    $time = $tempBlockTime - Carbon::parse($verificationData->temp_block_time)->DiffInSeconds();
                    $errorMsg = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
                    if (request()->ajax()) {
                        return response()->json([
                            'status' => 0,
                            'message' => $errorMsg
                        ]);
                    }
                    Toastr::error($errorMsg);
                    return redirect()->back();
                }

                if ($verificationData['is_temp_blocked'] == 1 && Carbon::parse($verificationData['updated_at'])->DiffInSeconds() >= $tempBlockTime) {
                    $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                        'otp_hit_count' => 0,
                        'is_temp_blocked' => 0,
                        'temp_block_time' => null,
                    ]);
                }

                if ($verificationData['otp_hit_count'] >= $maxOTPHit && Carbon::parse($verificationData['updated_at'])->DiffInSeconds() < $maxOTPHitTime && $verificationData['is_temp_blocked'] == 0) {
                    $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                        'is_temp_blocked' => 1,
                        'temp_block_time' => now(),
                    ]);

                    $time = $tempBlockTime - Carbon::parse($verificationData['temp_block_time'])->DiffInSeconds();
                    $errorMsg = translate('Too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
                    if (request()->ajax()) {
                        return response()->json([
                            'status' => 0,
                            'message' => $errorMsg
                        ]);
                    }
                    Toastr::error($errorMsg);
                    return redirect()->back();
                }

                $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                    'otp_hit_count' => ($verificationData['otp_hit_count'] + 1),
                    'updated_at' => now(),
                    'temp_block_time' => null,
                ]);
            }
        }

        $errorMsg = translate('OTP_is_not_matched');
        if (request()->ajax()) {
            return response()->json([
                'status' => 0,
                'message' => $errorMsg
            ]);
        }
        Toastr::error($errorMsg);
        return redirect()->back();
    }

    // Customer Ajax Verify
    public function ajax_verify(Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'token' => 'required',
        ]);

        $email_status = getLoginConfig(key: 'email_verification');
        $phone_status = getLoginConfig(key: 'phone_verification');

        $user = $this->customerRepo->getFirstWhere(params: ['id' => $request['id']]);
        $verify = PhoneOrEmailVerification::where(['phone_or_email' => $user['email'], 'token' => $request['token']])->first();

        $maxOTPHit = getWebConfig(name: 'maximum_otp_hit') ?? 5;
        $temp_block_time = getWebConfig(name: 'temporary_block_time') ?? 5; //minute

        if (isset($verify)) {
            if (isset($verify->temp_block_time) && Carbon::parse($verify->temp_block_time)->diffInSeconds() <= $temp_block_time) {
                $time = $temp_block_time - Carbon::parse($verify->temp_block_time)->diffInSeconds();

                $verify_status = 'error';
                $message = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
                return response()->json([
                    'status' => $verify_status,
                    'message' => $message,
                ]);
            }

            ($email_status == 1 || ($phone_status == '0' && $email_status == '0')) ? ($user->is_email_verified = 1) : ($user->is_phone_verified = 1);
            $user->save();
            $verify->delete();

            $verify_status = 'success';
            $message = translate('verification_done_successfully');
        } else {
            $verification = PhoneOrEmailVerification::where(['phone_or_email' => $user['email']])->first();

            if ($verification) {
                if (isset($verification->temp_block_time) && Carbon::parse($verification->temp_block_time)->diffInSeconds() <= $temp_block_time) {
                    $time = $temp_block_time - Carbon::parse($verification->temp_block_time)->diffInSeconds();

                    $verify_status = 'error';
                    $message = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();

                } elseif ($verification->is_temp_blocked == 1 && isset($verification->created_at) && Carbon::parse($verification->created_at)->diffInSeconds() >= $temp_block_time) {
                    $verification->otp_hit_count = 1;
                    $verification->is_temp_blocked = 0;
                    $verification->temp_block_time = null;
                    $verification->updated_at = now();
                    $verification->save();

                    $verify_status = 'error';
                    $message = translate('Verification_OTP_mismatched');

                } elseif ($verification->otp_hit_count >= $maxOTPHit && $verification->is_temp_blocked == 0) {
                    $verification->is_temp_blocked = 1;
                    $verification->temp_block_time = now();
                    $verification->updated_at = now();
                    $verification->save();

                    $time = $temp_block_time - Carbon::parse($verification->temp_block_time)->diffInSeconds();
                    $verify_status = 'error';
                    $message = translate('too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();

                } else {
                    $verification->otp_hit_count += 1;
                    $verification->save();

                    $verify_status = 'error';
                    $message = translate('Verification code/ OTP mismatched');
                }
            } else {
                $verify_status = 'error';
                $message = translate('Verification code/ OTP mismatched');
            }
        }

        return response()->json([
            'status' => $verify_status,
            'message' => $message,
        ]);
    }

    public static function login_process($user, $email, $password): ?string
    {
        if (auth('customer')->attempt(['email' => $email, 'password' => $password], true)) {
            CustomerManager::updateCustomerSessionData(userId: auth('customer')->id());
            CartManager::cartListSessionToDatabase();
            return translate('welcome_to') . ' ' . getWebConfig(name: 'company_name') . '!';
        }
        return translate('credentials_are_not_matched_or_your_account_is_not_active');
    }

    public function resendOTPToCustomer(Request $request): JsonResponse|RedirectResponse
    {
        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
        if ($firebaseOTPVerification && $firebaseOTPVerification['status'] && empty($request['g-recaptcha-response'])) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('ReCAPTCHA_Failed')
                ]);
            }
            Toastr::error(translate('ReCAPTCHA_Failed'));
            return redirect()->back();
        }

        $maxOTPHit = getWebConfig(name: 'maximum_otp_hit') ?? 5;
        $maxOTPHitTime = getWebConfig(name: 'otp_resend_time') ?? 60;// seconds
        $tempBlockTime = getWebConfig(name: 'temporary_block_time') ?? 600; // seconds
        $tempBlockTime = getWebConfig(name: 'temporary_block_time') ?? 5;
        $phoneVerification = getLoginConfig(key: 'phone_verification');
        $emailVerification = getLoginConfig(key: 'email_verification');

        $timeDifferance = 0;
        $verificationType = base64_decode($request['type']);
        $customer = $this->customerRepo->getByIdentity(filters: ['identity' => base64_decode($request['identity'])]);
        if ($customer) {
            $identity = $verificationType == 'email_verification' ? $customer['email'] : $customer['phone'];
            $getToken = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);
            $identityType = $verificationType == 'email_verification' ? 'email' : 'phone';
        } else {
            $identity = base64_decode($request['identity']);
            $getToken = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);
            $identityType = 'phone';
            $phoneVerification = 1;
            $customer = [
              'phone' => $identity,
              'email' => $identity,
              'is_phone_verified' => 0,
              'is_email_verified' => 0,
            ];
        }

        if ($getToken) {
            $tokenTime = Carbon::parse($getToken['created_at']);
            $addTime = $tokenTime->addSeconds($maxOTPHitTime);
            $timeDifferance = $addTime > Carbon::now() ? Carbon::now()->diffInSeconds($addTime) : 0;
        }

        if ($timeDifferance > 0) {
            Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($timeDifferance)->cascade()->forHumans());
            return redirect()->back();
        } else {
            $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $identity]);
            if ($identityType == 'phone') {
                $this->getCustomerVerificationCheck($customer, 'phone', ['phone_verification' => $phoneVerification]);
                return redirect()->back();
            } else if ($identityType == 'email') {
                $this->getCustomerVerificationCheck($customer, 'email');
                return redirect()->back();
            }
            Toastr::success(translate('registration_success_login_now'));
            return redirect(route('customer.auth.login'));
        }
    }

}
