<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPointTransaction;
use App\Utils\CustomerManager;
use App\Utils\Helpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserLoyaltyController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $loyaltyPointStatus = getWebConfig(name: 'loyalty_point_status');
        if ($loyaltyPointStatus == 1) {
            $user = $request->user();

            $transactionTypes = json_decode($request['transaction_types'] ?? '', true) ?? [];
            if (request()->has('start_date') && request()->has('end_date') && !checkDateFormatInMDY($request['start_date']) && !checkDateFormatInMDY($request['end_date'])) {
                $startDate = Carbon::createFromFormat('m/d/Y h:i:s a', $request['start_date'])->format('Y-m-d') . ' 00:00:00';
                $endDate = Carbon::createFromFormat('m/d/Y h:i:s a', $request['end_date'])->format('Y-m-d') . ' 23:59:59';
            } else {
                $startDate = '';
                $endDate = '';
            }
            $loyaltyPointList = LoyaltyPointTransaction::where('user_id', $user->id)
                ->when($request->has('filter_by') && in_array($request['filter_by'], ['debit', 'credit']), function ($query) use ($request) {
                    $query->when($request['filter_by'] == 'debit', function ($query) {
                        $query->where('debit', '!=', 0);
                    })->when($request['filter_by'] == 'credit', function ($query) {
                        $query->where('debit', '=', 0);
                    });
                })
                ->when(!empty($startDate) && !empty($endDate), function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->when(!empty($transactionTypes) && !in_array('all', $transactionTypes), function ($query) use ($transactionTypes) {
                    return $query->whereIn('transaction_type', $transactionTypes);
                })
                ->latest()
                ->paginate($request['limit'], ['*'], 'page', $request['offset']);

            return response()->json([
                'limit' => (integer)$request['limit'],
                'offset' => (integer)$request['offset'],
                'total_loyalty_point' => $user->loyalty_point,
                'total_size' => $loyaltyPointList->total(),
                'loyalty_point_list' => $loyaltyPointList->items(),
                'filter_by' => $request['filter_by'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date'],
                'transaction_types' => (array)$transactionTypes,
            ], 200);
        } else {
            return response()->json(['message' => translate('access_denied!')], 422);
        }
    }

    public function loyalty_exchange_currency(Request $request): JsonResponse
    {
        $walletStatus = getWebConfig(name: 'wallet_status');
        $loyaltyPointStatus = getWebConfig(name: 'loyalty_point_status');

        if ($walletStatus != 1 || $loyaltyPointStatus != 1) {
            return response()->json([
                'message' => translate('transfer_loyalty_point_to_currency_is_not_possible_at_this_moment!')
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'point' => 'required|integer|min:1'
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $user = $request->user();
        if ($request['point'] < (int)getWebConfig(name: 'loyalty_point_minimum_point') || $request['point'] > $user->loyalty_point) {
            return response()->json([
                'message' => translate('insufficient_point!')
            ], 422);
        }

        $walletTransaction = CustomerManager::create_wallet_transaction($user->id, $request['point'], 'loyalty_point', 'point_to_wallet');
        CustomerManager::create_loyalty_point_transaction($user->id, $walletTransaction->transaction_id, $request['point'], 'point_to_wallet');

        try {
            Mail::to($user['email'])->send(new \App\Mail\AddFundToWallet($walletTransaction));
        } catch (\Exception $ex) {
        }

        return response()->json([
            'message' => translate('point_to_wallet_transfer_successfully!')
        ], 200);
    }
}
