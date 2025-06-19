<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Events\PasswordResetEvent;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\Web\CustomerAuthService;
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
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Gateways\Traits\SmsGateway;
use function Laravel\Prompts\password;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService                         $customerAuthService,
        private readonly CustomerRepositoryInterface                 $customerRepo,
        private readonly FirebaseService                             $firebaseService,
        private readonly PhoneOrEmailVerificationRepositoryInterface $phoneOrEmailVerificationRepo,
    )
    {
        $this->middleware('guest:customer', ['except' => ['logout']]);
    }

    public function reset_password()
    {
        $verification_by = getWebConfig(name: 'forgot_password_verification');
        return view(VIEW_FILE_NAMES['recover_password'], compact('verification_by'));
    }

    public function resetPasswordRequest(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'identity' => 'required',
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

        $customer = $this->customerRepo->getByIdentity(filters: ['phone' => $request['identity']]);
        if (!$customer) {
            Toastr::error(translate('No_such_user_found'));
            return back();
        }

        if($customer->is_active == 0) {
            Toastr::error(translate('Your_account_is_deactivated'));
            return back();
        }

        session()->put('forgot_password_identity', $request['identity']);
        $verificationBy = 'phone';
        $otpIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 1;
        $smsErrorMsg = translate('something_went_wrong.').' '.translate('please_try_again_after_sometime');

        $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['identity']]);

        if (isset($OTPVerificationData) && Carbon::parse($OTPVerificationData->created_at)->diffInSeconds() < $otpIntervalTime) {
            $time = $otpIntervalTime - Carbon::parse($OTPVerificationData->created_at)->diffInSeconds();
            Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
            return back();
        } else {
            $token = $this->customerAuthService->getCustomerVerificationToken();

            $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
            if ($verificationBy == 'phone' && $firebaseOTPVerification && $firebaseOTPVerification['status']) {

                $firebaseResponse = $this->firebaseService->sendOtp($customer['phone']);
                if ($firebaseResponse['status'] == 'success') {
                    $token = $firebaseResponse['sessionInfo'];
                    $response = $firebaseResponse['status'];
                } else {
                    $smsErrorMsg = translate(strtolower($firebaseResponse['errors']));
                }
            } else if ($verificationBy == 'phone') {
                $response = $this->customerAuthService->sendCustomerPhoneVerificationToken($customer['phone'], $token);
                $response = $response['status'];
                if (env('APP_MODE') == 'dev') {
                    $response = 'success';
                }
            } else {
                try {
                    $token = Str::random(120);
                    $resetUrl = route('customer.auth.reset-password', ['identity' => base64_encode($customer['email']), 'token' => $token]);
                    $data = [
                        'userType' => 'customer',
                        'templateName' => 'forgot-password',
                        'userName' => $customer['f_name'],
                        'subject' => translate('password_reset'),
                        'title' => translate('password_reset'),
                        'passwordResetURL' => $resetUrl,
                    ];
                    $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $customer['email']], value: [
                        'phone_or_email' => $customer['email'],
                        'token' => $token,
                    ]);
                    event(new PasswordResetEvent(email: $customer['email'], data: $data));
                    Toastr::success(translate('Check_your_email') . ' ' . translate('Password_reset_url_sent'));
                } catch (\Exception $exception) {
                    Toastr::error(translate('email_is_not_configured') . '. ' . translate('contact_with_the_administrator'));
                }
                return back();
            }

            if (isset($response) && $response == 'success') {
                $identity = $verificationBy == 'phone' ? $customer['phone'] : $customer['email'];
                $type = $verificationBy == 'phone' ? 'phone_verification' : 'email_verification';
                $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $identity], value: [
                    'phone_or_email' => $identity,
                    'token' => $token,
                ]);
                Toastr::success(translate('Check_your_phone') .' '. translate('Password_reset_OTP_sent'));
                return redirect()->route('customer.auth.login.verify-account', ['identity' => base64_encode($identity), 'type' => base64_encode($type), 'action' => base64_encode('password-reset')]);
            } else {
                Toastr::error($smsErrorMsg);
                return back();
            }
        }
    }

    public function resendPhoneOTPRequest(Request $request): JsonResponse|RedirectResponse
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

        $customer = $this->customerRepo->getByIdentity(filters: ['identity' => base64_decode($request['identity'])]);
        if ($customer) {
            $tokenInfo = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $customer['phone']]);
            $otpIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 1;

            if (isset($tokenInfo) && Carbon::parse($tokenInfo->updated_at)->diffInSeconds() < $otpIntervalTime) {
                $time = $otpIntervalTime - Carbon::parse($tokenInfo->updated_at)->diffInSeconds();
                Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                return redirect()->back();
            } else {
                $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
                $token = $this->customerAuthService->getCustomerVerificationToken();
                $response = 'not_found';
                if ($firebaseOTPVerification && $firebaseOTPVerification['status']) {
                    $firebaseResponse = $this->firebaseService->sendOtp($customer['phone']);
                    if ($firebaseResponse['status'] == 'success') {
                        $token = $firebaseResponse['sessionInfo'];
                        $response = $firebaseResponse['status'];
                    }
                } else {
                    $response = $this->customerAuthService->sendCustomerPhoneVerificationToken($customer['phone'], $token);
                    $response = $response['status'];
                }

                $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $customer['phone']], value: [
                    'phone_or_email' => $customer['phone'],
                    'token' => $token,
                    'otp_hit_count' => 0,
                    'is_temp_blocked' => 0,
                    'temp_block_time' => 0,
                    'created_at' => now(),
                ]);

                if ($response == "not_found") {
                    Toastr::error(translate('something_went_wrong.').' '.translate('please_try_again_after_sometime'));
                    return redirect()->back();
                }
                Toastr::success(translate('OTP_sent_successfully'));
                return redirect()->back();
            }
        } else {
            Toastr::error(translate('Invalid_user'));
            return redirect()->back();
        }
    }

    public function otp_verification(Request $request)
    {
        $token_info = PasswordReset::where('identity', $request['identity'])->latest()->first();
        if (!$token_info) {
            return redirect()->route('customer.auth.recover-password');
        }

        $otp_resend_time = getWebConfig(name: 'otp_resend_time') > 0 ? getWebConfig(name: 'otp_resend_time') : 0;
        $token_time = Carbon::parse($token_info->created_at);
        $convert_time = $token_time->addSeconds($otp_resend_time);
        $time_count = $convert_time > Carbon::now() ? Carbon::now()->diffInSeconds($convert_time) : 0;

        return view(VIEW_FILE_NAMES['otp_verification'], compact('time_count'));
    }

    public function otp_verification_submit(Request $request)
    {
        $max_otp_hit = getWebConfig(name: 'maximum_otp_hit') ?? 5;
        $temp_block_time = getWebConfig(name: 'temporary_block_time') ?? 5; // minute
        $id = theme_root_path() == 'default' ? session('forgot_password_identity') : $request['identity'];

        $password_reset_token = PasswordReset::where(['token' => $request['otp'], 'user_type' => 'customer'])
            ->where('identity', 'like', "%{$id}%")
            ->latest()
            ->first();

        if (isset($password_reset_token)) {
            if (isset($password_reset_token->temp_block_time) && Carbon::parse($password_reset_token->temp_block_time)->diffInSeconds() <= $temp_block_time) {
                $time = $temp_block_time - Carbon::parse($password_reset_token->temp_block_time)->diffInSeconds();

                Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                return redirect()->back();
            }

            $token = $request['otp'];
            return redirect()->route('customer.auth.reset-password', ['token' => $token]);

        } else {
            $password_reset = PasswordReset::where(['user_type' => 'customer'])
                ->where('identity', 'like', "%{$id}%")
                ->latest()
                ->first();

            if ($password_reset) {
                if (isset($password_reset->temp_block_time) && Carbon::parse($password_reset->temp_block_time)->diffInSeconds() <= $temp_block_time) {
                    $time = $temp_block_time - Carbon::parse($password_reset->temp_block_time)->diffInSeconds();

                    Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                } elseif ($password_reset->is_temp_blocked == 1 && Carbon::parse($password_reset->created_at)->diffInSeconds() >= $temp_block_time) {
                    $password_reset->otp_hit_count = 1;
                    $password_reset->is_temp_blocked = 0;
                    $password_reset->temp_block_time = null;
                    $password_reset->updated_at = now();
                    $password_reset->save();

                    Toastr::error(translate('invalid_otp'));

                } elseif ($password_reset->otp_hit_count >= $max_otp_hit && $password_reset->is_temp_blocked == 0) {
                    $password_reset->is_temp_blocked = 1;
                    $password_reset->temp_block_time = now();
                    $password_reset->updated_at = now();
                    $password_reset->save();

                    $time = $temp_block_time - Carbon::parse($password_reset->temp_block_time)->diffInSeconds();

                    Toastr::error(translate('Too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                } else {
                    $password_reset->otp_hit_count += 1;
                    $password_reset->save();

                    Toastr::error(translate('invalid_OTP'));
                }
            } else {
                Toastr::error(translate('invalid_OTP'));
            }

            return redirect()->back();
        }
    }

    public function resetPasswordView(Request $request): View|RedirectResponse
    {
        $data = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => base64_decode($request['identity']), 'token' => $request['token']]);
        if (isset($data)) {
            $token = $request['token'];
            return view(VIEW_FILE_NAMES['reset_password'], compact('token'));
        }
        Toastr::error(translate('Invalid_credentials'));
        return back();
    }

    public function resetPasswordSubmit(Request $request): View|Redirector|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|same:confirm_password',
        ]);

        $token = $request['reset_token'];
        if ($validator->fails()) {
            Toastr::error(translate('password_mismatch'));
            return view(VIEW_FILE_NAMES['reset_password'], compact('token'));
        }

        $data = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => base64_decode($request['identity']), 'token' => $request['token']]);
        $customer = $this->customerRepo->getByIdentity(filters: ['identity' => base64_decode($request['identity'])]);
        if (isset($data) && $customer) {
            $this->customerRepo->updateWhere(params: ['id' => $customer['id']], data: [
                'is_email_verified' => 1,
                'password' => bcrypt(str_replace(' ', '', $request['password']))
            ]);
            Toastr::success(translate('Password_reset_successfully'));
            DB::table('password_resets')->where('user_type', 'customer')->where(['token' => $request['reset_token']])->delete();
            $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => base64_decode($request['identity'])]);
            return redirect('/');
        }
        Toastr::error(translate('Invalid_data'));
        return back();
    }

    public function verifyRecoverPassword(Request $request): View|RedirectResponse|JsonResponse
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

        $phoneVerification = base64_decode($request['type']) == 'phone_verification';
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
        $customer = $this->customerRepo->getByIdentity(filters: ['identity' => $identity]);

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
        }

        $tokenVerifyStatus = false;
        if ($verificationData && $phoneVerification && $firebaseOTPVerification && $firebaseOTPVerification['status']) {
            $firebaseVerify = $this->firebaseService->verifyOtp($verificationData['token'], $verificationData['phone_or_email'], $request['token']);
            $tokenVerifyStatus = (boolean)($firebaseVerify['status'] == 'success');
            if (!$tokenVerifyStatus) {
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
            if (isset($verificationData->temp_block_time) && \Illuminate\Support\Carbon::parse($verificationData->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
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

            $this->customerRepo->updateWhere(params: ['id' => $customer['id']], data: [
                'is_phone_verified' => 1,
            ]);
            return redirect()->route('customer.auth.reset-password', ['identity' => base64_encode($identity), 'token' => $verificationData['token']]);
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
