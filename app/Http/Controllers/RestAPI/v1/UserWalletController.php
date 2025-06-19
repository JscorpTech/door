<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\AddFundBonusCategories;
use App\Models\WalletTransaction;
use App\Utils\Helpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserWalletController extends Controller
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

        $walletStatus = getWebConfig(name: 'wallet_status');

        if ($walletStatus == 1) {
            $user = $request->user();
            $totalWalletBalance = $user->wallet_balance;
            $types = json_decode($request->get('transaction_types', ''), true) ?? [];
            $transactionTypes = $this->getSelectTransactionTypes(types: $types);
            if (request()->has('start_date') && request()->has('end_date') && !checkDateFormatInMDY($request['start_date']) && !checkDateFormatInMDY($request['end_date'])) {
                $startDate = Carbon::createFromFormat('m/d/Y h:i:s a', $request['start_date'])->format('Y-m-d') . ' 00:00:00';
                $endDate = Carbon::createFromFormat('m/d/Y h:i:s a', $request['end_date'])->format('Y-m-d') . ' 23:59:59';
            } else {
                $startDate = '';
                $endDate = '';
            }

            $walletTransactionList = WalletTransaction::where(['user_id' => $user->id])
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
                ->when(!empty($transactionTypes) || in_array('added_via_payment_method', $types) || in_array('earned_by_referral', $types), function ($query) use ($transactionTypes, $types) {
                    $query->where(function ($subResult) use ($transactionTypes, $types) {
                        if (!empty($transactionTypes)) {
                            $subResult->whereIn('transaction_type', $transactionTypes);
                        }
                        if (in_array('added_via_payment_method', $types)) {
                            $subResult->orWhere('reference', 'add_funds_to_wallet');
                        }
                        if (in_array('earned_by_referral', $types)) {
                            $subResult->orWhere('reference', 'earned_by_referral');
                        }
                    });
                })
                ->latest()
                ->paginate($request['limit'], ['*'], 'page', $request['offset']);

            return response()->json([
                'limit' => (integer)$request['limit'],
                'offset' => (integer)$request['offset'],
                'total_wallet_balance' => $totalWalletBalance,
                'total_size' => $walletTransactionList->total(),
                'wallet_transaction_list' => $walletTransactionList->items(),
                'filter_by' => $request['filter_by'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date'],
                'transaction_types' => (array)$types,
            ], 200);
        } else {
            return response()->json(['message' => translate('access_denied!')], 422);
        }
    }

    public function bonus_list(Request $request): JsonResponse
    {
        $addFundBonusCategories = AddFundBonusCategories::active()
            ->whereDate('start_date_time', '<=', now())
            ->whereDate('end_date_time', '>=', now())
            ->get();
        return response()->json(['bonus_list' => $addFundBonusCategories], 200);
    }

    public function getSelectTransactionTypes($types): array
    {
        $typeMapping = [
            'order_refund' => 'order_refund',
            'order_place' => 'order_place',
            'loyalty_point' => 'loyalty_point',
            'add_fund' => 'add_fund',
            'add_fund_by_admin' => 'add_fund_by_admin',
        ];

        foreach ($typeMapping as $key => $value) {
            if (in_array($key, $types)) {
                $transactionTypes[] = $value;
            }
        }

        return $transactionTypes ?? [];
    }
}
