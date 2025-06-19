<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Traits\Processor;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaypalPaymentController extends Controller
{
    use Processor;

    private mixed $config_values;
    private string $base_url;

    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('paypal', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }

        if ($config) {
            $this->base_url = ($config->mode == 'test') ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        }
        $this->payment = $payment;
    }

    public function token(): bool|string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_USERPWD, $this->config_values->client_id . ':' . $this->config_values->client_secret);

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $accessToken = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $accessToken;
    }

    /**
     * Responds with a welcome message with instructions
     *
     */
    public function payment(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
        } else {
            $business_name = "my_business";
        }

        $accessToken = json_decode($this->token(), true);

        if (isset($accessToken['access_token'])) {
            $accessToken = $accessToken['access_token'];
            $payment_data = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $data->id,
                        'amount' => [
                            'currency_code' => $data->currency_code ?? 'USD',
                            'value' => number_format($data->payment_amount, 2, '.', '')
                        ],
                        'description' => 'payment ID :' . $data->id
                    ]
                ],
                'application_context' => [
                    'return_url' => route('paypal.success', ['payment_id' => $data->id]),
                    'cancel_url' => route('paypal.cancel', ['payment_id' => $data->id])
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v2/checkout/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));

            $headers = [
                'Content-Type: application/json',
                "Authorization: Bearer $accessToken",
                "Paypal-Request-Id: " . Str::uuid()
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
        } else {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        };
        $response = json_decode($response);

        try {
            if (isset($response->links)) {
                $links = $response->links;
                return Redirect::away($links[1]->href);
            }
        } catch (\Exception $exception) {
        }

        return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
    }

    /**
     * Responds with a welcome message with instructions
     */
    public function cancel(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->first();
        return $this->payment_response($data, 'cancel');
    }

    /**
     * Responds with a welcome message with instructions
     */
    public function success(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {

        $accessToken = json_decode($this->token(), true);
        $accessToken = $accessToken['access_token'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->base_url . "/v2/checkout/orders/{$request->token}/capture");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = "Authorization: Bearer  $accessToken";
        $headers[] = 'Paypal-Request-Id:' . Str::uuid();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $response = json_decode($result);

        if ($response->status === 'COMPLETED') {
            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'paypal',
                'is_paid' => 1,
                'transaction_id' => $response->id,
            ]);

            $data = $this->payment::where(['id' => $request['payment_id']])->first();

            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }

            return $this->payment_response($data, 'success');
        }
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }
}
