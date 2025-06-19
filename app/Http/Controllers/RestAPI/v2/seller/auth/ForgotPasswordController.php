<?php

namespace App\Http\Controllers\RestAPI\v2\seller\auth;

use App\Events\PasswordResetEvent;
use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Gateways\Traits\SmsGateway;

class ForgotPasswordController extends Controller
{
    public function reset_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $verification_by = getWebConfig(name: 'vendor_forgot_password_method');
        DB::table('password_resets')->where('user_type','seller')->where('identity', 'like', "%{$request['identity']}%")->delete();

        if ($verification_by == 'email') {
            $seller = Seller::Where(['email' => $request['identity']])->first();
            if (isset($seller)) {
                $token = Str::random(120);
                DB::table('password_resets')->insert([
                    'identity' => $seller['email'],
                    'token' => $token,
                    'user_type'=>'seller',
                    'created_at' => now(),
                ]);
                $reset_url = route('vendor.auth.forgot-password.reset-password', ['token' => $token]);

                $emailServices_smtp = getWebConfig(name: 'mail_config');
                if ($emailServices_smtp['status'] == 0) {
                    $emailServices_smtp = getWebConfig(name: 'mail_config_sendgrid');
                }
                if ($emailServices_smtp['status'] == 1) {
                    $data = [
                        'userType' => 'vendor',
                        'templateName' => 'forgot-password',
                        'vendorName' => $seller['f_name'],
                        'subject' => translate('password_reset'),
                        'title' => translate('password_reset'),
                        'passwordResetURL' => $reset_url,
                    ];
                    event(new PasswordResetEvent(email: $seller['email'],data: $data));
                    $response = translate('check_your_email');
                }else{
                    $response= translate('email_failed');
                }
                return response()->json(['message' => $response], 200);
            }
        } elseif ($verification_by == 'phone') {
            $seller = Seller::where('phone', 'like', "%{$request['identity']}%")->first();
            if (isset($seller)) {
                $token = (env('APP_MODE') == 'live') ? rand(1000, 9999) : 1234;
                DB::table('password_resets')->insert([
                    'identity' => $seller['phone'],
                    'token' => $token,
                    'user_type'=>'seller',
                    'created_at' => now(),
                ]);

                SMSModule::sendCentralizedSMS($seller->phone, $token);
                return response()->json(['message' => 'otp sent successfully.'], 200);
            }
        }
        return response()->json(['errors' => [
            ['code' => 'not-found', 'message' => 'user not found!']
        ]], 404);
    }

    public function otp_verification_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $id = $request['identity'];
        $data = DB::table('password_resets')
            ->where('user_type','seller')
            ->where(['token' => $request['otp']])
            ->where('identity', 'like', "%{$id}%")
            ->first();

        if (isset($data)) {
            return response()->json(['message' => 'otp verified.'], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'not-found', 'message' => 'invalid OTP']
        ]], 404);
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
            ->where('user_type','seller')
            ->where('identity', 'like', "%{$request['identity']}%")
            ->where(['token' => $request['otp']])->first();

        if (isset($data)) {
            DB::table('sellers')->where('phone', 'like', "%{$data->identity}%")
                ->update([
                    'password' => bcrypt(str_replace(' ', '', $request['password']))
                ]);

            DB::table('password_resets')
                ->where('user_type','seller')
                ->where('identity', 'like', "%{$request['identity']}%")
                ->where(['token' => $request['otp']])->delete();

            return response()->json(['message' => 'Password changed successfully.'], 200);
        }
        return response()->json(['errors' => [
            ['code' => 'invalid', 'message' => 'Invalid token.']
        ]], 400);
    }
}
