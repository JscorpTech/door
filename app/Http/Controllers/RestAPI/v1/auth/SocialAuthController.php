<?php

namespace App\Http\Controllers\RestAPI\v1\auth;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\LoginSetupRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\User;
use App\Utils\CartManager;
use App\Utils\Helpers;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface                 $customerRepo,
        private readonly PhoneOrEmailVerificationRepositoryInterface $phoneOrEmailVerificationRepo,
        private readonly LoginSetupRepositoryInterface               $loginSetupRepo
    )
    {
    }

    public function social_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'unique_id' => 'required',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $client = new Client();
        $token = $request['token'];
        $email = $request['email'];
        $unique_id = $request['unique_id'];

        try {
            if ($request['medium'] == 'google') {
                $res = $client->request('GET', 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $token);
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'facebook') {
                $res = $client->request('GET', 'https://graph.facebook.com/' . $unique_id . '?access_token=' . $token . '&&fields=name,email');
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'apple') {
                $apple_login = BusinessSetting::where(['type' => 'apple_login'])->first();
                if ($apple_login) {
                    $apple_login = json_decode($apple_login->value)[0];
                }
                $teamId = $apple_login->team_id;
                $keyId = $apple_login->key_id;
                $sub = $apple_login->client_id;
                $aud = 'https://appleid.apple.com';
                $iat = strtotime('now');
                $exp = strtotime('+60days');
                $keyContent = file_get_contents('storage/app/public/apple-login/' . $apple_login->service_file);

                $token = JWT::encode([
                    'iss' => $teamId,
                    'iat' => $iat,
                    'exp' => $exp,
                    'aud' => $aud,
                    'sub' => $sub,
                ], $keyContent, 'ES256', $keyId);
                $redirect_uri = $apple_login->redirect_url ?? 'www.example.com/apple-callback';
                $res = Http::asForm()->post('https://appleid.apple.com/auth/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $unique_id,
                    'redirect_uri' => $redirect_uri,
                    'client_id' => $sub,
                    'client_secret' => $token,
                ]);

                $claims = explode('.', $res['id_token'])[1];
                $data = json_decode(base64_decode($claims), true);
            }
        } catch (Exception $exception) {
            return response()->json(['error' => translate('wrong_credential')]);
        }

        if ($request['medium'] == 'apple' && isset($data['email'])) {
            $fast_name = strstr($data['email'], '@', true);
            $user = User::where('email', $data['email'])->first();
            if (isset($user) == false) {
                $user = User::create([
                    'f_name' => $fast_name,
                    'email' => $data['email'],
                    'phone' => '',
                    'password' => bcrypt($data['email']),
                    'is_active' => 1,
                    'login_medium' => $request['medium'],
                    'social_id' => $data['sub'],
                    'is_phone_verified' => 0,
                    'is_email_verified' => 1,
                    'referral_code' => Helpers::generate_referer_code(),
                    'temporary_token' => Str::random(40)
                ]);
            } else {
                $user->temporary_token = Str::random(40);
                $user->save();
            }
            if (!isset($user->phone)) {
                return response()->json([
                    'token_type' => 'update phone number',
                    'temporary_token' => $user->temporary_token]);
            }

            $token = self::login_process_passport($user, $user['email'], $data['email']);
            if ($token != null) {

                CartManager::cartListSessionToDatabase($request);
                return response()->json(['token' => $token]);
            }
            return response()->json(['error_message' => translate('customer_not_found_or_account_has_been_suspended')]);


        } elseif (strcmp($email, $data['email']) === 0) {
            $name = explode(' ', $data['name']);
            if (count($name) > 1) {
                $fast_name = implode(" ", array_slice($name, 0, -1));
                $last_name = end($name);
            } else {
                $fast_name = implode(" ", $name);
                $last_name = '';
            }
            $user = User::where('email', $email)->first();
            if (isset($user) == false) {
                $user = User::create([
                    'f_name' => $fast_name,
                    'l_name' => $last_name,
                    'email' => $email,
                    'phone' => '',
                    'password' => bcrypt($data['id']),
                    'is_active' => 1,
                    'login_medium' => $request['medium'],
                    'social_id' => $data['id'],
                    'is_phone_verified' => 0,
                    'is_email_verified' => 1,
                    'referral_code' => Helpers::generate_referer_code(),
                    'temporary_token' => Str::random(40)
                ]);
            } else {
                $user->temporary_token = Str::random(40);
                $user->save();
            }
            if (!isset($user->phone)) {
                return response()->json([
                    'token_type' => 'update phone number',
                    'temporary_token' => $user->temporary_token]);
            }

            $token = self::login_process_passport($user, $user['email'], $data['id']);
            if ($token != null) {

                CartManager::cartListSessionToDatabase($request);
                return response()->json(['token' => $token]);
            }
            return response()->json(['error_message' => translate('customer_not_found_or_account_has_been_suspended')]);
        }

        return response()->json(['error' => translate('email_does_not_match')]);
    }

    public static function login_process_passport($user, $email, $password)
    {
        $token = null;
        if (isset($user)) {
            auth()->login($user);
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
        }

        return $token;
    }

    public function update_phone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'temporary_token' => 'required',
            'phone' => 'required|min:11|max:14'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = User::where(['temporary_token' => $request->temporary_token])->first();
        $user->phone = $request->phone;
        $user->save();


        $phoneVerification = getLoginConfig(key: 'phone_verification');

        if ($phoneVerification == 1) {
            return response()->json([
                'token_type' => 'phone verification on',
                'temporary_token' => $request['temporary_token']
            ]);

        } else {
            return response()->json(['message' => translate('phone_number_updated_successfully')]);
        }
    }

    public function customerSocialLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'unique_id' => 'required',
            'email' => 'required_if:medium,google,facebook',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $client = new Client();
        $token = $request['token'];
        $email = $request['email'];
        $uniqueId = $request['unique_id'];

        try {
            if ($request['medium'] == 'google') {
                $res = $client->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $token);
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'facebook') {
                $res = $client->request('GET', 'https://graph.facebook.com/' . $uniqueId . '?access_token=' . $token . '&&fields=name,email');
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'apple') {
                $apple_login = getWebConfig(name: 'apple_login');
                $teamId = $apple_login['team_id'];
                $keyId = $apple_login['key_id'];
                $sub = $apple_login['client_id'];
                $aud = 'https://appleid.apple.com';
                $iat = strtotime('now');
                $exp = strtotime('+60days');
                $keyContent = file_get_contents('storage/app/public/apple-login/' . $apple_login['service_file']);
                $token = JWT::encode([
                    'iss' => $teamId,
                    'iat' => $iat,
                    'exp' => $exp,
                    'aud' => $aud,
                    'sub' => $sub,
                ], $keyContent, 'ES256', $keyId);

                $redirect_uri = $apple_login['redirect_url'] ?? 'www.example.com/apple-callback';

                $res = Http::asForm()->post('https://appleid.apple.com/auth/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $uniqueId,
                    'redirect_uri' => $redirect_uri,
                    'client_id' => $sub,
                    'client_secret' => $token,
                ]);

                $claims = explode('.', $res['id_token'])[1];
                $data = json_decode(base64_decode($claims), true);
            }
        } catch (Exception $exception) {
            $errors = [];
            $errors[] = ['code' => 'auth-001', 'message' => 'Invalid Token'];
            return response()->json([
                'errors' => $errors
            ], 401);
        }

        if (!isset($claims) && isset($data)) {
            if (strcmp($email, $data['email']) != 0) {
                if ($request['medium'] == 'apple' && (!isset($data['id']) && !isset($data['kid']))) {
                    return response()->json(['error' => translate('email_does_not_match')], 403);
                } else {
                    return response()->json(['error' => translate('email_does_not_match')], 403);
                }
            }
        }

        $existingUser = $this->customerRepo->getFirstWhere(params: ['email' => $data['email']]);
        $temporaryToken = Str::random(40);

        if (!$existingUser) {
            return response()->json(['temp_token' => $temporaryToken, 'status' => false, 'new_user' => 0]);
        }

        if (!$existingUser['is_active'] || (getLoginConfig(key: 'phone_verification') && !$existingUser['is_phone_verified'])) {
            return response()->json(['user' => $existingUser, 'status' => false]);
        }

        $this->customerRepo->updateWhere(params: ['email' => $data['email']], data: [
            'is_email_verified' => 1,
            'email_verified_at' => now()
        ]);
        $token = $existingUser->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token, 'status' => true]);
    }

    public function existingAccountCheck(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'user_response' => 'required|in:0,1',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = $this->customerRepo->getFirstWhere(params: ['email' => $request['email']]);

        $temporaryToken = Str::random(40);
        if (!$user) {
            return response()->json(['temp_token' => $temporaryToken, 'status' => false]);
        }

        if ($request['user_response'] == 1) {
            $this->customerRepo->updateWhere(params: ['id' => $user['id']], data: [
                'email_verified_at' => now(),
                'login_medium' => $request['medium'],
            ]);

            $token = $user->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token, 'status' => true]);
        }

        $this->customerRepo->updateWhere(params: ['id' => $user['id']], data: [
            'email' => null,
            'email_verified_at' => null,
        ]);

        return response()->json(['temp_token' => $temporaryToken, 'status' => false]);
    }

    public function registrationWithSocialMedia(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|min:6|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $isPhoneExist = $this->customerRepo->getFirstWhere(params: ['phone' => $request['phone']]);

        if ($isPhoneExist) {
            return response()->json(['errors' => [
                ['code' => 'email', 'message' => translate('This phone has already been used in another account!')]
            ]], 403);
        }
        $temporaryToken = Str::random(40);

        $user = $this->customerRepo->add(data: [
            'name' => $request['name'],
            'f_name' => $request['name'],
            'l_name' => '',
            'email' => $request['email'],
            'phone' => $request['phone'],
            'password' => bcrypt(rand(11111111, 99999999)),
            'temporary_token' => $temporaryToken,
            'email_verified_at' => now(),
            'referral_code' => Helpers::generate_referer_code(),
            'login_medium' => 'social',
        ]);

        $phoneVerificationStatus = getLoginConfig(key: 'phone_verification') ?? 0;
        if ($phoneVerificationStatus) {
            return response()->json(['temp_token' => $temporaryToken, 'status' => false]);
        }

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token]);
    }
}
