<?php

namespace App\Http\Controllers\RestAPI\v1\auth;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Events\PasswordResetEvent;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Traits\CustomerTrait;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    use CustomerTrait;

    public function __construct(
        private readonly CustomerRepositoryInterface                 $customerRepo,
        private readonly PasswordResetRepositoryInterface            $passwordResetRepo,
    )
    {
    }

    public function reset_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $verification_by = getWebConfig(name: 'forgot_password_verification');
        $otp_interval_time = getWebConfig(name: 'otp_resend_time') ?? 1; //second

        $password_verification_data = PasswordReset::where(['user_type'=>'customer'])->where('identity', 'like', "%{$request['identity']}%")->latest()->first();
        if ($verification_by == 'email') {
            $customer = User::Where(['email' => $request['identity']])->first();
            if (isset($customer)) {
                if(isset($password_verification_data) &&  Carbon::parse($password_verification_data->created_at)->diffInSeconds() < $otp_interval_time){
                    $time= $otp_interval_time - Carbon::parse($password_verification_data->created_at)->diffInSeconds();

                    return response()->json(['message' => translate('please_try_again_after').' '.CarbonInterval::seconds($time)->cascade()->forHumans()], 200);
                }else {
                    $token = Str::random(120);
                    $reset_data = PasswordReset::where(['identity' => $customer['email']])->latest()->first();
                    if($reset_data){
                        $reset_data->token = $token;
                        $reset_data->created_at = now();
                        $reset_data->updated_at = now();
                        $reset_data->save();
                    }else{
                        $reset_data = new PasswordReset();
                        $reset_data->identity = $customer['email'];
                        $reset_data->token = $token;
                        $reset_data->user_type = 'customer';
                        $reset_data->created_at = now();
                        $reset_data->updated_at = now();
                        $reset_data->save();
                    }

                    $reset_url = url('/') . '/customer/auth/reset-password?token=' . $token;

                    $emailServices_smtp = getWebConfig(name: 'mail_config');
                    if ($emailServices_smtp['status'] == 0) {
                        $emailServices_smtp = getWebConfig(name: 'mail_config_sendgrid');
                    }
                    if ($emailServices_smtp['status'] == 1) {
                        try{
                            $data = [
                                'userType' => 'customer',
                                'templateName' => 'forgot-password',
                                'vendorName' => $customer['f_name'],
                                'subject' => translate('password_reset'),
                                'title' => translate('password_reset'),
                                'passwordResetURL' => $reset_url,
                            ];
                            event(new PasswordResetEvent(email: $customer['email'],data: $data));
                            $response = 'Check your email';
                        } catch (\Exception $exception) {
                            return response()->json([
                                'message' => translate('email_is_not_configured'). translate('contact_with_the_administrator')
                            ], 403);
                        }
                    } else {
                        $response = translate('email_failed');
                    }
                    return response()->json(['message' => $response], 200);
                }
            }
        } elseif ($verification_by == 'phone') {
            $customer = User::where('phone', 'like', "%{$request['identity']}%")->first();
            $otp_resend_time = getWebConfig(name: 'otp_resend_time') > 0 ? getWebConfig(name: 'otp_resend_time') : 0;
            if (isset($customer)) {
                if(isset($password_verification_data) &&  Carbon::parse($password_verification_data->created_at)->diffInSeconds() < $otp_interval_time){
                    $time= $otp_interval_time - Carbon::parse($password_verification_data->created_at)->diffInSeconds();

                    return response()->json(['message' => translate('please_try_again_after').' '.CarbonInterval::seconds($time)->cascade()->forHumans()], 200);
                }else {
                    $token = (env('APP_MODE') == 'live') ? rand(100000, 999999) : 123456;
                    $reset_data = PasswordReset::where(['identity' => $customer['phone']])->latest()->first();
                    if($reset_data){
                        $reset_data->token = $token;
                        $reset_data->created_at = now();
                        $reset_data->updated_at = now();
                        $reset_data->save();
                    }else{
                        $reset_data = new PasswordReset();
                        $reset_data->identity = $customer['phone'];
                        $reset_data->token = $token;
                        $reset_data->user_type = 'customer';
                        $reset_data->created_at = now();
                        $reset_data->updated_at = now();
                        $reset_data->save();
                    }

                    SMSModule::sendCentralizedSMS($customer->phone, $token);
                    return response()->json([
                        'message' => translate('otp_sent_successfully'),
                        'resend_time'=> $otp_resend_time,
                    ], 200);
                }
            }
        }
        return response()->json(['errors' => [
            ['code' => 'not-found', 'message' => translate('user not found').'!']
        ]], 403);
    }

    public function tokenVerificationSubmit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required',
            'reset_token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $verificationData = $this->passwordResetRepo->getFirstWhere(params: ['identity' => $request['email_or_phone']]);
        $verifyStatus = $this->checkPasswordResetOTPBlockTimeOrInvalid(verificationData: $verificationData, identity: $request['email_or_phone']);
        if ($verifyStatus['status'] == 1) {
            return response()->json([
                'errors' => [
                    ['code' => $verifyStatus['code'], 'message' => $verifyStatus['message']]
                ]
            ], 403);
        }

        $verify = $this->passwordResetRepo->getFirstWhere(params: ['identity' => $request['email_or_phone'], 'token' => $request['reset_token']]);
        if ($verify) {
            return response()->json(['message' => translate('otp_verified')], 200);
        }

        return response()->json([
            'errors' => [
                ['code' => 'token', 'message' => translate('OTP_is_not_matched')]
            ]
        ], 403);
    }

    public function reset_password_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'otp' => 'required',
            'password' => 'required|same:confirm_password|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $data = DB::table('password_resets')
            ->where('user_type','customer')
            ->where('identity', 'like', "%{$request['identity']}%")
            ->where(['token' => $request['otp']])->first();

        if (!$data) {
            $data = DB::table('phone_or_email_verifications')
                ->where('phone_or_email', 'like', "%{$request['identity']}%")
                ->where(['token' => $request['otp']])->first();
        }

        if (isset($data)) {
            User::where('email', 'like', "%{$data->identity}%")
                ->orWhere('phone', 'like', "%{$data->identity}%")
                ->update([
                    'password' => bcrypt(str_replace(' ', '', $request['password']))
                ]);

            DB::table('password_resets')
                ->where('user_type','customer')
                ->where('identity', 'like', "%{$request['identity']}%")
                ->where(['token' => $request['otp']])->delete();

            DB::table('phone_or_email_verifications')
                ->where('phone_or_email', 'like', "%{$request['identity']}%")
                ->where(['token' => $request['otp']])->delete();

            return response()->json(['message' => translate('password_changed_successfully')], 200);
        }
        return response()->json(['errors' => [
            ['code' => 'invalid', 'message' => translate('invalid_token')]
        ]], 400);
    }
}
