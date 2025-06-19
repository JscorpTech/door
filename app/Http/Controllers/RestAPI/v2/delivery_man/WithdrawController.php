<?php

namespace App\Http\Controllers\RestAPI\v2\delivery_man;

use App\Http\Controllers\Controller;
use App\Models\DeliverymanWallet;
use App\Models\WithdrawRequest;
use App\Traits\CommonTrait;
use App\Utils\Convert;
use App\Utils\Helpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WithdrawController extends Controller
{
    public function sendWithdrawRequest(Request $request): JsonResponse
    {
        $deliveryMan = $request->delivery_man;
        $parentId = $request->delivery_man->seller_id;

        $withdrawalBalance = CommonTrait::delivery_man_withdrawable_balance($deliveryMan['id']);
        if ($withdrawalBalance < Convert::usd($request['amount'])) {
            return response()->json(['message' => translate('withdraw_request_amount_can_not_be_more_than_withdrawable_balance')], 403);
        }

        $wallet = DeliverymanWallet::where('delivery_man_id', $deliveryMan['id'])->first();
        if ($request['amount'] > 1) {
            WithdrawRequest::insert([
                'delivery_man_id' => $deliveryMan['id'],
                ($parentId == 0) ? 'admin_id' : 'seller_id' => $parentId,
                'amount' => Convert::usd($request['amount']),
                'transaction_note' => $request['note'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $wallet->pending_withdraw += Convert::usd($request['amount']);
            $wallet->save();
            return response()->json(['message' => translate('Withdraw_request_sent_successfully!')], 200);
        }
        return response()->json(['message' => translate('Invalid_withdraw_request')], 403);
    }

    public function getWithdrawListByApproved(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'offset' => 'required',
            'limit' => 'required',
            'type' => 'required|in:withdrawn,pending',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }
        $delivery_man = $request['delivery_man'];
        $approved = $request->type == 'withdrawn' ? 1 : 0;

        $withdraw = WithdrawRequest::where(['delivery_man_id' => $delivery_man->id, 'approved' => $approved]);

        if (isset($request->start_date) && isset($request->end_date)) {
            $start_date = Carbon::parse($request['start_date'])->format('Y-m-d 00:00:00');
            $end_data = Carbon::parse($request['end_date'])->format('Y-m-d 23:59:59');
            $withdraw->whereBetween('created_at', [$start_date, $end_data]);
        }
        $withdraws = $withdraw->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $data['total_size'] = $withdraws->total();
        $data['limit'] = $request['limit'];
        $data['offset'] = $request['offset'];
        $data['withdraws'] = $withdraws->items();
        return response()->json($data, 200);
    }
}
