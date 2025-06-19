<?php

namespace App\Http\Controllers\RestAPI\v1\auth;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\LoginSetup;
use App\Models\PhoneOrEmailVerification;
use App\Models\User;
use App\Utils\CartManager;
use App\Utils\Helpers;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PassportAuthController extends Controller
{
    public function __construct(
        private readonly User                     $user,
        private readonly BusinessSetting          $businessSetting,
        private readonly PhoneOrEmailVerification $phoneVerification,
        private readonly LoginSetup               $loginSetup
    )
    {
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|min:8',
        ], [
            'f_name.required' => 'The first name field is required.',
            'l_name.required' => 'The last name field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        if ($request->referral_code) {
            $refer_user = User::where(['referral_code' => $request->referral_code])->first();
        }

        $temporary_token = Str::random(40);
        $user = User::create([
            'name' => $request['f_name'] . ' ' . $request['l_name'],
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'is_active' => 1,
            'password' => bcrypt($request['password']),
            'temporary_token' => $temporary_token,
            'referral_code' => Helpers::generate_referer_code(),
            'referred_by' => (isset($refer_user) && $refer_user) ? $refer_user->id : null,
        ]);

        $phoneVerification = getLoginConfig(key: 'phone_verification');
        $emailVerification = getLoginConfig(key: 'email_verification');
        if ($phoneVerification && !$user->is_phone_verified) {
            return response()->json(['temporary_token' => $temporary_token], 200);
        }
        if ($emailVerification && !$user->is_email_verified) {
            return response()->json(['temporary_token' => $temporary_token], 200);
        }

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token], 200);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required',
            'password' => 'required|min:6',
            'type' => 'required|in:phone,email'
        ]);

        $userId = $request['email_or_phone'];
        $type = $request['type'];

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        if (filter_var($userId, FILTER_VALIDATE_EMAIL)) {
            $medium = 'email';
        } else {
            $count = strlen(preg_replace("/[^\d]/", "", $userId));
            if ($count >= 9 && $count <= 15) {
                $medium = 'phone';
            } else {
                $errors = [];
                $errors[] = ['code' => 'email', 'message' => translate('credentials_doesnt_match')];
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
        }

        $data = [
            $medium => $userId,
            'password' => $request['password']
        ];
        $user = User::where([$medium => $userId])->first();

        $maxLoginHit = getWebConfig(name: 'maximum_login_hit') ?? 5;
        $tempBlockTime = getWebConfig(name: 'temporary_login_block_time') ?? 300; // seconds

        if (isset($user)) {
            if (isset($user->temp_block_time) && \Illuminate\Support\Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

                $errors = [];
                $errors[] = ['code' => 'login_block_time',
                    'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                ];
                return response()->json(['errors' => $errors], 403);
            }

            if (auth()->attempt($data)) {
                $temporaryToken = Str::random(40);

                $emailVerification = getLoginConfig(key: 'email_verification') ?? 0;
                $phoneVerification = getLoginConfig(key: 'phone_verification') ?? 0;

                if ($type == 'phone' && $phoneVerification && !$user->is_phone_verified) {
                    return response()->json(['temporary_token' => $temporaryToken, 'status' => false], 200);
                }
                if ($type == 'email' && $emailVerification && $user->email_verified_at == null) {
                    return response()->json(['temporary_token' => $temporaryToken, 'status' => false], 200);
                }

                $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;

                $user->login_hit_count = 0;
                $user->is_temp_blocked = 0;
                $user->temp_block_time = null;
                $user->updated_at = now();
                $user->save();

                return response()->json(['token' => $token, 'status' => true], 200);
            } else {
                if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                    $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

                    $errors = [];
                    $errors[] = [
                        'code' => 'login_block_time',
                        'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ];
                    return response()->json([
                        'errors' => $errors
                    ], 403);
                }

                if ($user->is_temp_blocked == 1 && Carbon::parse($user->temp_block_time)->DiffInSeconds() >= $tempBlockTime) {

                    $user->login_hit_count = 0;
                    $user->is_temp_blocked = 0;
                    $user->temp_block_time = null;
                    $user->updated_at = now();
                    $user->save();
                }

                if ($user->login_hit_count >= $maxLoginHit && $user->is_temp_blocked == 0) {
                    $user->is_temp_blocked = 1;
                    $user->temp_block_time = now();
                    $user->updated_at = now();
                    $user->save();

                    $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

                    $errors = [];
                    $errors[] = [
                        'code' => 'login_temp_blocked',
                        'message' => translate('Too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ];
                    return response()->json([
                        'errors' => $errors
                    ], 403);
                }
            }

            $user->login_hit_count += 1;
            $user->temp_block_time = null;
            $user->updated_at = now();
            $user->save();
        }

        $errors = [];
        $errors[] = ['code' => 'auth-001', 'message' => 'Invalid credentials.'];
        return response()->json(['errors' => $errors], 401);
    }

    public function logout(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->token()->revoke();
            return response()->json(['message' => translate('logged_out_successfully')], 200);
        }
        return response()->json(['message' => translate('logged_out_fail')], 403);
    }
}
