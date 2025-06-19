<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\LoyaltyPointTransactionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LoyaltyExchangeCurrencyRequest;
use App\Mail\AddFundToWallet;
use App\Models\LoyaltyPointTransaction;
use App\Traits\CustomerTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserLoyaltyController extends Controller
{
    use CustomerTrait;

    public function __construct(private readonly LoyaltyPointTransactionRepositoryInterface $loyaltyPointTransactionRepo)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $loyaltyPointStatus = getWebConfig(name: 'loyalty_point_status');
        if ($loyaltyPointStatus == 1) {
            $walletStatus = getWebConfig(name: 'wallet_status');
            $totalLoyaltyPoint = auth('customer')->user()->loyalty_point;
            $loyaltyPointMinimumPoint = getWebConfig(name: 'loyalty_point_minimum_point');
            $loyaltyPointExchangeRate = getWebConfig(name: 'loyalty_point_exchange_rate');
            $transactionTypes = $this->getSelectTransactionTypes(types: $request->get('types', []));
            $loyaltyPointList = $this->getLoyaltyPointTransactionList(request: $request, types: $transactionTypes);
            $filterCount = count($transactionTypes) + (int) !empty($request['transaction_range']) + (int) !empty($request['filter_by']);

            return view(VIEW_FILE_NAMES['user_loyalty'], [
                'totalLoyaltyPoint' => $totalLoyaltyPoint,
                'loyaltyPointMinimumPoint' => $loyaltyPointMinimumPoint,
                'loyaltyPointExchangeRate' => $loyaltyPointExchangeRate,
                'loyaltyPointList' => $loyaltyPointList,
                'loyaltyPointStatus' => $loyaltyPointStatus,
                'walletStatus' => $walletStatus,
                'transactionTypes' => $transactionTypes,
                'filterCount' => $filterCount,
                'filterBy' => $request['filter_by'] ?? '',
                'transactionRange' => $request['transaction_range'] ?? '',
            ]);
        } else {
            Toastr::warning(translate('access_denied'));
            return redirect()->route('home');
        }
    }

    private function getLoyaltyPointTransactionList(object|array $request, array $types)
    {
        $startDate = '';
        $endDate = '';
        if (isset($request['transaction_range']) && !empty($request['transaction_range'])) {
            $dates = explode(' - ', $request['transaction_range']);
            if (count($dates) !== 2 || !checkDateFormatInMDY($dates[0]) || !checkDateFormatInMDY($dates[1])) {
                Toastr::error(translate('Invalid_date_range_format'));
                return back();
            }
            $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d') . ' 00:00:00';
            $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d') . ' 23:59:59';
        }
        return LoyaltyPointTransaction::where('user_id', auth('customer')->id())
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
            ->when(!empty($types) && !in_array('all', $types), function ($query) use ($types) {
                return $query->whereIn('transaction_type', $types);
            })
            ->latest()
            ->paginate(10)->appends(request()->query());
    }

    public function getSelectTransactionTypes($types): array
    {
        $typeMapping = [
            'all' => 'all',
            'order_place' => 'order_place',
            'point_to_wallet' => 'point_to_wallet',
            'refund_order' => 'refund_order',
        ];

        foreach ($typeMapping as $key => $value) {
            if (in_array($key, $types)) {
                $transactionTypes[] = $value;
            }
        }

        return $transactionTypes ?? [];
    }

    public function getLoyaltyExchangeCurrency(LoyaltyExchangeCurrencyRequest $request): RedirectResponse
    {
        if (getWebConfig(name: 'wallet_status') != 1 || getWebConfig(name: 'loyalty_point_status') != 1) {
            Toastr::warning(translate('transfer_loyalty_point_to_currency_is_not_possible_at_this_moment!'));
            return redirect()->route('home');
        }

        $user = auth('customer')->user();
        if ($request['point'] < (int)getWebConfig(name: 'loyalty_point_minimum_point') || $request['point'] > $user['loyalty_point']) {
            Toastr::warning(translate('exchange_requirements_not_matched'));
            return back();
        }

        $walletTransaction = $this->createWalletTransaction(user_id: $user['id'], amount: $request['point'], transaction_type: 'loyalty_point', reference: 'point_to_wallet');
        $this->loyaltyPointTransactionRepo->addLoyaltyPointTransaction(userId: $user['id'], reference: $walletTransaction['transaction_id'], amount: $request['point'], transactionType: 'point_to_wallet');

        try {
            Mail::to($user['email'])->send(new AddFundToWallet($walletTransaction));
        } catch (Exception $ex) {
        }

        Toastr::success(translate('point_to_wallet_transfer_successfully'));
        return back();
    }

    public function getLoyaltyCurrencyAmount(Request $request): JsonResponse
    {
        return response()->json(webCurrencyConverter(amount: $request['amount']));
    }
}
