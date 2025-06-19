<?php

namespace App\Http\Controllers\Admin\Customer;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ViewPaths\Admin\Customer;
use App\Enums\ExportFileNames\Admin\Customer as CustomerExport;
use App\Enums\WebConfigKey;
use App\Events\CustomerRegistrationEvent;
use App\Events\CustomerStatusUpdateEvent;
use App\Exports\CustomerListExport;
use App\Exports\CustomerOrderListExport;
use App\Exports\SubscriberListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CustomerRequest;
use App\Http\Requests\Admin\CustomerUpdateSettingsRequest;
use App\Repositories\ShippingAddressRepository;
use App\Services\CustomerService;
use App\Services\PasswordResetService;
use App\Services\ShippingAddressService;
use App\Traits\EmailTemplateTrait;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerController extends BaseController
{
    use PaginatorTrait, EmailTemplateTrait;

    public function __construct(
        private readonly CustomerRepositoryInterface        $customerRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,
        private readonly OrderRepositoryInterface           $orderRepo,
        private readonly SubscriptionRepositoryInterface    $subscriptionRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly RefundRequestRepositoryInterface   $refundRequestRepo,
        private readonly PasswordResetRepositoryInterface   $passwordResetRepo,
        private readonly PasswordResetService               $passwordResetService,
        private readonly ShippingAddressRepository          $shippingAddressRepo,
        private readonly ShippingAddressService             $shippingAddressService,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View|RedirectResponse
    {
        $filters = [
            'is_active' => $request['is_active'] ?? null,
            'order_date' => $request['order_date'],
            'sort_by' => $request['sort_by'] ?? null,
            'avoid_walking_customer' => 1,
        ];
        $takeItem = $request->get('choose_first');

        if (isset($request['order_date']) && !empty($request['order_date'])) {
            $dates = explode(' - ', $request['order_date']);
            if (count($dates) !== 2 || !checkDateFormatInMDY($dates[0]) || !checkDateFormatInMDY($dates[1])) {
                Toastr::error(translate('Invalid_date_range_format'));
                return back();
            }
        }

        $joiningStartDate = '';
        $joiningEndDate = '';
        if (isset($request['customer_joining_date']) && !empty($request['customer_joining_date'])) {
            $dates = explode(' - ', $request['customer_joining_date']);
            if (count($dates) !== 2 || !checkDateFormatInMDY($dates[0]) || !checkDateFormatInMDY($dates[1])) {
                Toastr::error(translate('Invalid_date_range_format'));
                return back();
            }
            $joiningStartDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $joiningEndDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }

        $customers = $this->customerRepo->getListWhereBetween(
            searchValue: $request['searchValue'],
            filters: $filters,
            relations: ['orders'],
            whereBetween: 'created_at',
            whereBetweenFilters: $joiningStartDate && $joiningEndDate ? [$joiningStartDate, $joiningEndDate] : [],
            takeItem: $takeItem,
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
            appends: $request->all(),
        );
        $totalCustomers = $this->customerRepo->getListWhereBetween(filters: ['avoid_walking_customer' => 1], dataLimit: 'all')->count();
        return view(Customer::LIST[VIEW], [
            'customers' => $customers,
            'totalCustomers' => $totalCustomers,
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $this->customerRepo->update(id: $request['id'], data: ['is_active' => $request->get('is_active', 0)]);
        $this->customerRepo->deleteAuthAccessTokens(id: $request['id']);
        $customer = $this->customerRepo->getFirstWhere(params: ['id' => $request['id']]);
        $data = [
            'userName' => $customer['f_name'],
            'userType' => 'customer',
            'templateName' => $customer['is_active'] ? 'account-unblock' : 'account-block',
            'subject' => $customer['is_active'] ? translate('Account_Unblocked') . ' !' : translate('Account_Blocked') . ' !',
            'title' => $customer['is_active'] ? translate('Account_Unblocked') . ' !' : translate('Account_Blocked') . ' !',
        ];
        event(new CustomerStatusUpdateEvent(email: $customer['email'], data: $data));
        return response()->json(['message' => translate('update_successfully')]);
    }

    public function getView(Request $request, $id): View|RedirectResponse
    {
        $customer = $this->customerRepo->getFirstWhere(params: ['id' => $id], relations: ['addresses']);
        if (isset($customer)) {
            $orders = $this->orderRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: ['customer_id' => $id, 'is_guest' => '0'], dataLimit: 'all');
            $orderStatusArray = [
                'total_order' => 0,
                'ongoing' => 0,
                'completed' => 0,
                'returned' => 0,
                'refunded' => count($customer->refundOrders),
                'canceled' => 0,
                'failed' => 0,
            ];
            $orders?->map(function ($order) use (&$orderStatusArray) {
                if (in_array($order->order_status, ['pending', 'confirmed', 'processing', 'out_for_delivery'])) {
                    $orderStatusArray['ongoing']++;
                } elseif ($order->order_status == 'delivered') {
                    $orderStatusArray['completed']++;
                } else {
                    $orderStatusArray[$order->order_status]++;
                }
                $orderStatusArray['total_order']++;
            });
            $orders = $this->orderRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: ['customer_id' => $id, 'is_guest' => '0'], dataLimit: getWebConfig('pagination_limit'));
            return view(Customer::VIEW[VIEW], compact('customer', 'orders', 'orderStatusArray'));
        }
        Toastr::error(translate('customer_Not_Found'));
        return back();
    }

    public function exportOrderList(Request $request, $id): BinaryFileResponse
    {
        $customer = $this->customerRepo->getFirstWhere(params: ['id' => $id]);
        $orders = $this->orderRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: ['customer_id' => $id, 'is_guest' => '0'], dataLimit: 'all');
        $data = [
            'customer' => $customer,
            'searchValue' => $request->get('searchValue'),
            'orders' => $orders
        ];
        return Excel::download(new CustomerOrderListExport($data), CustomerExport::CUSTOMER_ORDER_LIST);
    }

    /**
     * @param $id
     * @param CustomerService $customerService
     * @return RedirectResponse
     */
    public function delete($id, CustomerService $customerService): RedirectResponse
    {
        $customer = $this->customerRepo->getFirstWhere(params: ['id' => $id]);
        $customerService->deleteImage(data: $customer);
        $this->customerRepo->delete(params: ['id' => $id]);
        Toastr::success(translate('customer_deleted_successfully'));
        return back();
    }

    public function getSubscriberListView(Request $request): View|RedirectResponse
    {
        $orderBy = $request['sort_by'] ?? 'desc';
        $takeItem = $request->get('choose_first');
        $startDate = '';
        $endDate = '';
        if (isset($request['subscription_date']) && !empty($request['subscription_date'])) {
            $dates = explode(' - ', $request['subscription_date']);
            if (count($dates) !== 2 || !checkDateFormatInMDY($dates[0]) || !checkDateFormatInMDY($dates[1])) {
                Toastr::error(translate('Invalid_date_range_format'));
                return back();
            }
            $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }
        $subscriberList = $this->subscriptionRepo->getListWhereBetween(
            orderBy: ['created_at' => $orderBy],
            searchValue: $request['searchValue'],
            whereBetween: 'created_at',
            whereBetweenFilters: $startDate && $endDate ? [$startDate, $endDate] : [],
            takeItem: $takeItem,
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
            appends: $request->all(),
        );
        $totalSubscribers = $this->subscriptionRepo->getListWhere(dataLimit: 'all')->count();
        return view(Customer::SUBSCRIBER_LIST[VIEW], compact('subscriberList', 'totalSubscribers'));
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $filters = [
            'is_active' => $request['is_active'] ?? null,
            'order_date' => $request['order_date'],
            'sort_by' => $request['sort_by'] ?? null,
            'avoid_walking_customer' => 1,
        ];
        $takeItem = $request->get('choose_first');

        $orderStartDate = '';
        $orderEndDate = '';
        if (isset($request['order_date'])) {
            $dates = explode(' - ', $request['order_date']);
            $orderStartDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $orderEndDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }

        $joiningStartDate = '';
        $joiningEndDate = '';
        if (isset($request['customer_joining_date'])) {
            $dates = explode(' - ', $request['customer_joining_date']);
            $joiningStartDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $joiningEndDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }

        $customers = $this->customerRepo->getListWhereBetween(
            searchValue: $request['searchValue'],
            filters: $filters,
            relations: ['orders'],
            whereBetween: 'created_at',
            whereBetweenFilters: $joiningStartDate && $joiningEndDate ? [$joiningStartDate, $joiningEndDate] : [],
            takeItem: $takeItem,
            dataLimit: 'all',
            appends: $request->all(),
        );
        $status = $request->is_active ?? '';
        $sortBy = $request->sort_by ?? '';
        $chooseFirst = $request->choose_first ?? '';
        $data = [
            'customers' => $customers,
            'status' => $status,
            'sortBy' => $sortBy,
            'chooseFirst' => $chooseFirst,
            'searchValue' => $request->get('searchValue'),
            'orderStartDate' => $orderStartDate,
            'orderEndDate' => $orderEndDate,
            'joiningStartDate' => $joiningStartDate,
            'joiningEndDate' => $joiningEndDate,
        ];
        return Excel::download(new CustomerListExport($data), CustomerExport::EXPORT_XLSX);
    }

    public function exportSubscribersList(Request $request): BinaryFileResponse
    {
        $orderBy = $request->get('sort_by', 'desc');
        $takeItem = $request->get('choose_first');
        $startDate = '';
        $endDate = '';
        if (isset($request['subscription_date'])) {
            $dates = explode(' - ', $request['subscription_date']);
            $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }
        $subscriptionList = $this->subscriptionRepo->getListWhereBetween(
            orderBy: ['created_at' => $orderBy],
            searchValue: $request['searchValue'],
            whereBetween: 'created_at',
            whereBetweenFilters: $startDate && $endDate ? [$startDate, $endDate] : [],
            takeItem: $takeItem,
            dataLimit: 'all',
            appends: $request->all(),
        );
        $sortBy = $request->sort_by ?? '';
        $chooseFirst = $request->choose_first ?? '';
        $data = [
            'subscription' => $subscriptionList,
            'sortBy' => $sortBy,
            'chooseFirst' => $chooseFirst,
            'search' => $request['searchValue'],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        return Excel::download(new SubscriberListExport($data), CustomerExport::SUBSCRIBER_LIST_XLSX);
    }

    public function getCustomerSettingsView(): View
    {
        $wallet = $this->businessSettingRepo->getListWhere(filters: [['type', 'like', 'wallet_%']]);
        $loyaltyPoint = $this->businessSettingRepo->getListWhere(filters: [['type', 'like', 'loyalty_point_%']]);
        $refEarning = $this->businessSettingRepo->getListWhere(filters: [['type', 'like', 'ref_earning_%']]);

        $data = [];
        foreach ($wallet as $setting) {
            $data[$setting->type] = $setting->value;
        }
        foreach ($loyaltyPoint as $setting) {
            $data[$setting->type] = $setting->value;
        }
        foreach ($refEarning as $setting) {
            $data[$setting->type] = $setting->value;
        }
        return view(Customer::SETTINGS[VIEW], compact('data'));
    }

    public function update(CustomerUpdateSettingsRequest $request): View|RedirectResponse
    {
        if (env('APP_MODE') === 'demo') {
            Toastr::info(translate('update_option_is_disable_for_demo'));
            return back();
        }
        $this->businessSettingRepo->updateOrInsert(type: 'wallet_status', value: $request->get('customer_wallet', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'loyalty_point_status', value: $request->get('customer_loyalty_point', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'wallet_add_refund', value: $request->get('refund_to_wallet', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'loyalty_point_exchange_rate', value: $request->get('loyalty_point_exchange_rate', getWebConfig('loyalty_point_exchange_rate')));
        $this->businessSettingRepo->updateOrInsert(type: 'loyalty_point_item_purchase_point', value: $request->get('item_purchase_point', getWebConfig('loyalty_point_item_purchase_point')));
        $this->businessSettingRepo->updateOrInsert(type: 'loyalty_point_minimum_point', value: $request->get('minimun_transfer_point', getWebConfig('loyalty_point_minimum_point')));
        $this->businessSettingRepo->updateOrInsert(type: 'ref_earning_status', value: $request->get('ref_earning_status', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'ref_earning_exchange_rate', value: currencyConverter(amount: $request->get('ref_earning_exchange_rate', getWebConfig('ref_earning_exchange_rate'))));
        $this->businessSettingRepo->updateOrInsert(type: 'add_funds_to_wallet', value: $request->get('add_funds_to_wallet', 0));

        if ($request->has('minimum_add_fund_amount') && $request->has('maximum_add_fund_amount')) {
            if ($request['maximum_add_fund_amount'] > $request['minimum_add_fund_amount']) {
                $this->businessSettingRepo->updateOrInsert(type: 'minimum_add_fund_amount', value: currencyConverter(amount: $request->get('minimum_add_fund_amount', 1)));
                $this->businessSettingRepo->updateOrInsert(type: 'maximum_add_fund_amount', value: currencyConverter(amount: $request->get('maximum_add_fund_amount', 0)));
            } else {
                Toastr::error(translate('minimum_amount_cannot_be_greater_than_maximum_amount'));
                return back();
            }
        }

        Toastr::success(translate('customer_settings_updated_successfully'));
        return back();
    }

    public function getCustomerList(Request $request): JsonResponse
    {
        $allCustomer = ['id' => 'all', 'text' => 'All customer'];
        $customers = $this->customerRepo->getCustomerNameList(request: $request)->toArray();
        array_unshift($customers, $allCustomer);
        return response()->json($customers);
    }

    public function getCustomerListWithoutAllCustomerName(Request $request): JsonResponse
    {
        $customers = $this->customerRepo->getCustomerNameList(request: $request)->toArray();
        return response()->json($customers);
    }

    public function add(CustomerRequest $request, CustomerService $customerService): RedirectResponse
    {
        $token = Str::random(120);
        $this->passwordResetRepo->add($this->passwordResetService->getAddData(identity: $request['phone'], token: $token, userType: 'customer'));
        $this->customerRepo->add($customerService->getCustomerData(request: $request));
        $customer = $this->customerRepo->getFirstWhere(params: ['email' => $request['email']]);
        $this->shippingAddressRepo->add($this->shippingAddressService->getAddAddressData(request: $request, customerId: $customer['id'], addressType: 'home'));
        $resetRoute = route('customer.auth.recover-password');
        $data = [
            'userName' => $request['f_name'],
            'userType' => 'customer',
            'templateName' => 'registration-from-pos',
            'subject' => translate('Customer_Registration_Successfully_Completed'),
            'title' => translate('welcome_to') . ' ' . getWebConfig('company_name') . '!',
            'resetPassword' => $resetRoute,
            'message' => translate('thank_you_for_joining') . ' ' . getWebConfig('company_name') . '.' . translate('if_you_want_to_become_a_registered_customer_then_reset_your_password_below_by_using_this_phone') . ' ' . ($request['phone']) . '.' . translate('then_you’ll_be_able_to_explore_the_website_and_app_as_a_registered_customer') . '.',
        ];
        event(new CustomerRegistrationEvent(email: $request['email'], data: $data));
        Toastr::success(translate('customer_added_successfully'));
        return redirect()->back();
    }
}
