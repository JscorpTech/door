<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\Web\CustomerAuthService;
use App\Utils\CustomerManager;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Utils\CartManager;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{

    public function __construct(
        private readonly CustomerRepositoryInterface                 $customerRepo,
        private readonly PhoneOrEmailVerificationRepositoryInterface $phoneOrEmailVerificationRepo,
        private readonly CustomerAuthService                         $customerAuthService,
        private readonly FirebaseService                             $firebaseService,
    )
    {
    }

    public function redirectToProvider($service)
    {
        return Socialite::driver($service)->redirect();
    }

    public function handleProviderCallback(Request $request, $service)
    {
        try {
            $userSocialData = Socialite::driver($service)->stateless()->user();
        } catch (\Exception $e) {
            Toastr::error(translate('Login_failed'));
            return redirect()->route('home');
        }

        $user = $this->customerRepo->getFirstWhere(params: ['email' => $userSocialData->getEmail()]);

        if (!$user || $user['login_medium'] != $service) {
            $name = explode(' ', $userSocialData['name']);
            if (count($name) > 1) {
                $fastName = implode(" ", array_slice($name, 0, -1));
                $lastName = end($name);
            } else {
                $fastName = implode(" ", $name);
                $lastName = '';
            }
            $fullName = $fastName . ' ' . $lastName;

            session()->forget('socialLoginEmailRemovedForOldUser');
            session()->put('social_login_new_customer', [
                'name' => $fullName,
                'f_name' => $fastName,
                'l_name' => $lastName,
                'email' => $userSocialData->getEmail(),
                'phone' => '',
                'password' => bcrypt($userSocialData->id),
                'is_active' => 1,
                'login_medium' => $service,
                'social_id' => $userSocialData->id,
                'is_phone_verified' => 0,
                'is_email_verified' => 1,
                'referral_code' => Helpers::generate_referer_code(),
                'temporary_token' => Str::random(40)
            ]);

            return redirect()->route('customer.auth.social-login-confirmation', [
                'identity' => base64_encode($userSocialData->getEmail()),
                'fullName' => base64_encode($fullName),
            ]);
        } else {
            $this->customerRepo->updateWhere(params: ['email' => $user['email']], data: [
                'is_email_verified' => 1,
                'login_medium' => $service,
                'social_id' => $userSocialData->id,
                'temporary_token' => Str::random(40)
            ]);

            return self::actionCustomerLoginProcess($request, $user, $user['email']);
        }
    }

    public function actionCustomerLoginProcess($request, $user, $email): JsonResponse|RedirectResponse
    {
        // Need Verification Or Not
        $phoneVerification = getLoginConfig(key: 'phone_verification');
        $emailVerification = getLoginConfig(key: 'email_verification');

        if ($user && $user['phone']) {
            $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $user['phone']]);
        }
        if ($user && $user['email']) {
            $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $user['email']]);
        }

        if (($phoneVerification && !$user['is_phone_verified']) || ($emailVerification && !$user['is_email_verified'])) {
            $this->getCustomerVerificationCheck($request, $user, $phoneVerification, $emailVerification);
            $verifyType = ($phoneVerification && !$user['is_phone_verified']) ? 'phone_verification' : 'email_verification';
            $verifyIdentity = ($phoneVerification && !$user['is_phone_verified']) ? $user['phone'] : $user['email'];
            $message = $verifyType == 'phone_verification' ? translate('Please_verify_your_phone') : translate('Please_verify_your_email');
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'redirect_url' => route('customer.auth.check-verification', ['identity' => base64_encode($verifyIdentity), 'type' => base64_encode($verifyType)]),
                ]);
            }
            Toastr::success($message);
            return redirect(route('customer.auth.check-verification', ['identity' => base64_encode($verifyIdentity), 'type' => base64_encode($verifyType)]));
        }

        $message = translate('credentials_are_not_matched_or_your_account_is_not_active') . '!';
        if ($user->is_active) {
            auth('customer')->login($user);
            CustomerManager::updateCustomerSessionData(userId: $user->id);
            CartManager::cartListSessionToDatabase();
            $message = translate('welcome_to') . ' ' . getWebConfig(name: 'company_name') . '!';
        }
        Toastr::info($message);
        return redirect()->route('home');
    }

    public function getCustomerVerificationCheck($request, $user, $phoneVerification, $emailVerification): array|RedirectResponse|JsonResponse|null
    {
        $response = null;
        $token = $this->customerAuthService->getCustomerVerificationToken();
        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];

        $OTPIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 60;
        $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $user['phone']]);

        if (isset($OTPVerificationData) && Carbon::parse($OTPVerificationData['created_at'])->DiffInSeconds() < $OTPIntervalTime) {
            $time = $OTPIntervalTime - Carbon::parse($OTPVerificationData['created_at'])->DiffInSeconds();
            if (request()->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('please_try_again_after_') . $time . ' ' . translate('seconds')
                ]);
            }
            Toastr::error(translate('please_try_again_after_') . $time . ' ' . translate('seconds'));
            return redirect()->back();
        }

        if ($phoneVerification && !$user['is_phone_verified'] && $firebaseOTPVerification && $firebaseOTPVerification['status']) {
            $response = $this->firebaseService->sendOtp($user['phone']);
            if ($response['status'] == 'success') {
                $token = $response['sessionInfo'];
            } else {
                Toastr::error($response['errors']);
                return back();
            }
        } else if ($phoneVerification && !$user['is_phone_verified']) {
            $response = $this->customerAuthService->sendCustomerPhoneVerificationToken($user['phone'], $token);
            if (env('APP_MODE') == 'dev') {
                $response['status'] = 'success';
            }
        } else if ($emailVerification && !$user['is_email_verified']) {
            $response = $this->customerAuthService->sendCustomerEmailVerificationToken($user, $token);
        }

        if ($response && $response['status'] == 'error') {
            Toastr::error($response['message']);
            return back();
        }

        if ($response && $response['status'] == 'success') {
            $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $user?->email], value: [
                'phone_or_email' => ($phoneVerification && !$user['is_phone_verified']) ? $user?->phone : $user?->email,
                'token' => $token,
            ]);
        }
        return $response;
    }

    public function socialLoginConfirmation(Request $request): View|RedirectResponse
    {
        $user = $this->customerRepo->getFirstWhere(params: ['email' => base64_decode($request['identity'])]);
        $socialLoginNewCustomer = session('social_login_new_customer');
        if ($request['status'] == 'approve' && $user) {
            $this->customerRepo->updateWhere(params: ['email' => $user['email']], data: [
                'is_email_verified' => 1,
                'login_medium' => $socialLoginNewCustomer['login_medium'] ?? null,
                'social_id' => $socialLoginNewCustomer['social_id'] ?? null,
                'temporary_token' => Str::random(40)
            ]);
            return self::actionCustomerLoginProcess($request, $user, $user['email']);
        } else {
            return view(VIEW_FILE_NAMES['customer_auth_verify_otp_update_info'], [
                'user' => $user,
                'socialLoginNewCustomer' => $socialLoginNewCustomer,
                'updateType' => 'social',
                'identity' => $request['identity'],
            ]);
        }
    }

    public function updateSocialLoginConfirmation(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'phone' => 'required',
        ]);

        if (!$request['phone']) {
            Toastr::error(translate('Please_use_a_phone_number'));
            return back();
        }

        $socialLoginNewCustomer = session('social_login_new_customer');
        $phoneCheck = $this->customerRepo->getFirstWhere(params: ['phone' => $request['phone']]);
        if ($phoneCheck) {
            Toastr::error(translate('Phone_Number_Already_Exist'));
            return back();
        }

        if (!session()->has('socialLoginEmailRemovedForOldUser')) {
            $this->customerRepo->updateWhere(params: ['email' => base64_decode($request['identity'])], data: ['email' => null, 'is_email_verified' => 0]);
            session()->put('socialLoginEmailRemovedForOldUser', 1);
        }

        if ($socialLoginNewCustomer) {
            session()->put('social_login_new_customer', [
                'name' => $request['name'],
                'f_name' => $request['name'],
                'l_name' => '',
                'email' => $socialLoginNewCustomer['email'],
                'phone' => $request['phone'],
                'social_id' => $socialLoginNewCustomer['social_id'],
                'is_email_verified' => 1,
                'is_phone_verified' => 0,
                'password' => bcrypt(rand(11111111, 99999999)),
                'temporary_token' => Str::random(40),
                'app_language' => 'en',
                'email_verified_at' => now(),
                'referral_code' => Helpers::generate_referer_code(),
                'login_medium' => $socialLoginNewCustomer['login_medium'] ?? null,
            ]);
            $this->customerRepo->updateOrCreate(params: ['email' => $socialLoginNewCustomer['email']], data: session('social_login_new_customer'));
            $user = $this->customerRepo->getFirstWhere(params: ['email' => $socialLoginNewCustomer['email']]);
            $phoneVerification = getLoginConfig(key: 'phone_verification');
            if ($phoneVerification == 1) {
                return $this->loginByOTP(request: $request);
            } else {
                return self::actionCustomerLoginProcess($request, $user, $user['email']);
            }
        } else {
            Toastr::error(translate('Invalid_Request'));
            return back();
        }
    }

    public function loginByOTP(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:6|max:20'
        ]);

        $OTPIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 60;// seconds
        $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['phone']]);

        if (isset($OTPVerificationData) && Carbon::parse($OTPVerificationData['created_at'])->DiffInSeconds() < $OTPIntervalTime) {
            $time = $OTPIntervalTime - Carbon::parse($OTPVerificationData['created_at'])->DiffInSeconds();

            $errors = [];
            $errors[] = [
                'code' => 'otp',
                'message' => translate('please_try_again_after_') . $time . ' ' . translate('seconds')
            ];
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $token = $this->customerAuthService->getCustomerVerificationToken();
        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
        $errorMsg = translate('OTP_sending_failed');

        if ($firebaseOTPVerification && $firebaseOTPVerification['status']) {
            $response = $this->firebaseService->sendOtp($request['phone']);
            if ($response['status'] == 'success') {
                $token = $response['sessionInfo'];
            } else {
                $errorMsg = translate(strtolower($response['errors']));
            }
        } else {
            $response = $this->customerAuthService->sendCustomerPhoneVerificationToken($request['phone'], $token);
            if (env('APP_MODE') == 'dev') {
                $response['status'] = 'success';
            }
        }

        if ($response && $response['status'] == 'success') {
            $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $request['phone']], value: [
                'phone_or_email' => $request['phone'],
                'token' => $token,
            ]);

            return redirect()->route('customer.auth.login.verify-account', ['identity' => base64_encode($request['phone']), 'action' => base64_encode('social-login-verify') ]);
        } else {
            Toastr::error($errorMsg);
            return redirect()->back();
        }
    }

    public function verifyAccount(Request $request): View|RedirectResponse|JsonResponse
    {
        if (!$request->has('token') || empty($request['token'])) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('The_token_field_is_required'),
                ]);
            }
            Toastr::error(translate('The_token_field_is_required'));
            return redirect()->back();
        }

        $identity = base64_decode($request['identity']);
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
        $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);
        $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity, 'token' => $request['token']]);

        if ($verificationData) {
            $validateBlock = 0;
            $errorMsg = translate('OTP_is_not_matched');
            if (isset($verificationData->temp_block_time) && Carbon::parse($verificationData->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                $time = $tempBlockTime - Carbon::parse($verificationData->temp_block_time)->DiffInSeconds();
                $validateBlock = 1;
                $errorMsg = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
            } else if ($verificationData['is_temp_blocked'] == 1 && Carbon::parse($verificationData['updated_at'])->DiffInSeconds() >= $tempBlockTime) {
                $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                    'otp_hit_count' => 0,
                    'is_temp_blocked' => 0,
                    'temp_block_time' => null,
                ]);
                $validateBlock = 1;
                $errorMsg = translate('OTP_is_not_matched');
            } else if ($verificationData['otp_hit_count'] >= $maxOTPHit && Carbon::parse($verificationData['updated_at'])->DiffInSeconds() < $maxOTPHitTime && $verificationData['is_temp_blocked'] == 0) {
                $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                    'is_temp_blocked' => 1,
                    'temp_block_time' => now(),
                ]);

                $validateBlock = 1;
                $time = $tempBlockTime - Carbon::parse($verificationData['temp_block_time'])->DiffInSeconds();
                $errorMsg = translate('Too_many_attempts.') .' '. translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
            }
            $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);
            $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                'otp_hit_count' => ($verificationData['otp_hit_count'] + 1),
                'updated_at' => now(),
            ]);
            if ($validateBlock) {
                if (request()->ajax()) {
                    return response()->json([
                        'status' => 0,
                        'message' => $errorMsg
                    ]);
                }
                Toastr::error($errorMsg);
                return redirect()->back();
            }

            $tokenVerifyStatus = false;
            if ($firebaseOTPVerification && $firebaseOTPVerification['status']) {
                $firebaseVerify = $this->firebaseService->verifyOtp($verificationData['token'], $verificationData['phone_or_email'], $request['token']);
                $tokenVerifyStatus = (boolean)($firebaseVerify['status'] == 'success');
                if (!$tokenVerifyStatus) {
                    $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);
                    $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                        'otp_hit_count' => ($verificationData['otp_hit_count'] + 1),
                        'updated_at' => now(),
                        'temp_block_time' => null,
                    ]);
                    Toastr::error(translate(strtolower($firebaseVerify['errors'])));
                    return back();
                }
            } else {
                $tokenVerifyStatus = (boolean)$OTPVerificationData;
            }

            if ($tokenVerifyStatus) {
                if (isset($OTPVerificationData->temp_block_time) && \Illuminate\Support\Carbon::parse($OTPVerificationData->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                    $time = $tempBlockTime - Carbon::parse($OTPVerificationData->temp_block_time)->DiffInSeconds();
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

                $user = $this->customerRepo->getByIdentity(filters: ['identity' => $identity]);
                $this->customerRepo->updateWhere(params: ['phone' => $user['phone']], data: [
                    'is_phone_verified' => 1,
                ]);
                $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $identity]);

                auth('customer')->login($user);
                CustomerManager::updateCustomerSessionData(userId: auth('customer')->id());
                return redirect()->route('home');
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
}
