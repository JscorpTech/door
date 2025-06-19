<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ProductCompare;
use App\Models\Wishlist;
use App\Models\User;
use App\Utils\CartManager;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public $company_name;

    public function __construct()
    {
        $this->middleware('guest:customer', ['except' => ['logout']]);
    }

    public function captcha(Request $request, $tmp)
    {

        $phrase = new PhraseBuilder(1, "1");
        $code = $phrase->build(3);
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(220, 210, 230);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        $builder->build($width = 100, $height = 40, $font = null);
        $phrase = $builder->getPhrase();

        if (Session::has($request->captcha_session_id)) {
            Session::forget($request->captcha_session_id);
        }
        Session::put($request->captcha_session_id, $phrase);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $builder->output();
    }


    public function submit(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'password' => 'required'
        ]);

        //recaptcha validation start
        $recaptcha = getWebConfig(name: 'recaptcha');
//        if (isset($recaptcha) && $recaptcha['status'] == 1) {
//            try {
//                $request->validate([
//                    'g-recaptcha-response' => [
//                        function ($attribute, $value, $fail) {
//                            $secret_key = getWebConfig(name: 'recaptcha')['secret_key'];
//                            $response = $value;
//                            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response;
//                            $response = \file_get_contents($url);
//                            $response = json_decode($response);
//                            if (!$response->success) {
//                                $fail(translate('ReCAPTCHA Failed'));
//                            }
//                        },
//                    ],
//                ]);
//            } catch (\Exception $exception) {
//            }
//        } else {
//            if (strtolower($request['default_recaptcha_id_customer_login']) != strtolower(Session('default_recaptcha_id_customer_login'))) {
//                if ($request->ajax()) {
//                    return response()->json([
//                        'status' => 'error',
//                        'message' => translate('Captcha_Failed.'),
//                        'redirect_url' => ''
//                    ]);
//                } else {
//                    Session::forget('default_recaptcha_id_customer_login');
//                    Toastr::error(translate('captcha_failed'));
//                    return back();
//                }
//            }
//        }
//        //recaptcha validation end

        $user = User::where(['phone' => $request->user_id])->orWhere(['email' => $request->user_id])->first();
        $remember = ($request['remember']) ? true : false;

        //login attempt check start
        $max_login_hit = getWebConfig(name: 'maximum_login_hit') ?? 5;
        $temp_block_time = getWebConfig(name: 'temporary_login_block_time') ?? 5; //seconds
        if (isset($user) == false) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('credentials_doesnt_match'),
                    'redirect_url' => ''
                ]);
            } else {
                Toastr::error(translate('credentials_doesnt_match'));
                return back()->withInput();
            }
        }
        //login attempt check end

        //phone or email verification check start
        $phone_verification = getLoginConfig(key:'phone_verification');
        $email_verification = getLoginConfig(key:'email_verification');
        if ($phone_verification && !$user->is_phone_verified) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('account_phone_not_verified'),
                    'redirect_url' => route('customer.auth.check-verification', ['identity' => base64_encode($user['phone']), 'type' => base64_encode('phone_verification')]),
                ]);
            }
            return redirect(route('customer.auth.check-verification', ['identity' => base64_encode($user['phone']), 'type' => base64_encode('phone_verification')]));
        }
        if ($email_verification && !$user->is_email_verified) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('account_email_not_verified'),
                    'redirect_url' => route('customer.auth.check-verification', ['identity' => base64_encode($user['email']), 'type' => base64_encode('email_verification')]),
                ]);
            }
            return redirect(route('customer.auth.check-verification', ['identity' => base64_encode($user['email']), 'type' => base64_encode('email_verification')]));
        }
        //phone or email verification check end

        if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $temp_block_time) {
            $time = $temp_block_time - Carbon::parse($user->temp_block_time)->DiffInSeconds();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans(),
                    'redirect_url' => ''
                ]);
            } else {
                Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                return back()->withInput();
            }
        }

        if (isset($user) && auth('customer')->attempt(['email' => $user['email'], 'password' => $request['password']], $remember)) {

            if (!$user->is_active) {
                auth()->guard('customer')->logout();
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => translate('your_account_is_suspended'),
                    ]);
                } else {
                    Toastr::error(translate('your_account_is_suspended'));
                    return back()->withInput();
                }
            }

            $wish_list = Wishlist::whereHas('wishlistProduct', function ($q) {
                return $q;
            })->where('customer_id', auth('customer')->user()->id)->pluck('product_id')->toArray();

            $compare_list = ProductCompare::where('user_id', auth('customer')->id())->pluck('product_id')->toArray();

            session()->forget('wish_list');
            session()->forget('compare_list');
            session()->put('wish_list', $wish_list);
            session()->put('compare_list', $compare_list);
            Toastr::success(translate('welcome_to') . ' ' . getWebConfig(name: 'company_name') . '!');
            CartManager::cartListSessionToDatabase();

            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->updated_at = now();
            $user->save();

            $redirect_url = "";
            $previous_url = url()->previous();

            if (
                strpos($previous_url, 'checkout-complete') !== false ||
                strpos($previous_url, 'offline-payment-checkout-complete') !== false ||
                strpos($previous_url, 'track-order') !== false
            ) {
                $redirect_url = route('home');
            }

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => translate('login_successful'),
                    'redirect_url' => $redirect_url,
                ]);
            } else {
                return back();
            }

        } else {

            //login attempt check start
            if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->diffInSeconds() <= $temp_block_time) {
                $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();

                $ajax_message = [
                    'status' => 'error',
                    'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans(),
                    'redirect_url' => ''
                ];
                Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());

            } elseif ($user->is_temp_blocked == 1 && Carbon::parse($user->temp_block_time)->diffInSeconds() >= $temp_block_time) {

                $user->login_hit_count = 0;
                $user->is_temp_blocked = 0;
                $user->temp_block_time = null;
                $user->updated_at = now();
                $user->save();

                $ajax_message = [
                    'status' => 'error',
                    'message' => translate('credentials_doesnt_match'),
                    'redirect_url' => ''
                ];
                Toastr::error(translate('credentials_doesnt_match'));

            } elseif ($user->login_hit_count >= $max_login_hit && $user->is_temp_blocked == 0) {
                $user->is_temp_blocked = 1;
                $user->temp_block_time = now();
                $user->updated_at = now();
                $user->save();

                $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();

                $ajax_message = [
                    'status' => 'error',
                    'message' => translate('too_many_attempts._please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans(),
                    'redirect_url' => ''
                ];
                Toastr::error(translate('too_many_attempts._please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
            } else {
                $ajax_message = [
                    'status' => 'error',
                    'message' => translate('credentials_doesnt_match'),
                    'redirect_url' => ''
                ];
                Toastr::error(translate('credentials_doesnt_match'));

                $user->login_hit_count += 1;
                $user->save();
            }
            //login attempt check end

            if ($request->ajax()) {
                return response()->json($ajax_message);
            } else {
                return back()->withInput();
            }
        }
    }

    public function logout(Request $request)
    {
        auth()->guard('customer')->logout();
        session()->forget('wish_list');
        session()->forget('customer_fcm_topic');
        Toastr::success(translate('come_back_soon') . '!');
        return redirect()->route('home');
    }

    public function getLoginModalView(Request $request): JsonResponse
    {
        return response()->json([
            'login_modal' => view(VIEW_FILE_NAMES['get_login_modal_data'])->render(),
            'register_modal' => view(VIEW_FILE_NAMES['get_register_modal_data'])->render(),
        ]);
    }


}
