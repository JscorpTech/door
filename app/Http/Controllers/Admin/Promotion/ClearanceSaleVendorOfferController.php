<?php

namespace App\Http\Controllers\Admin\Promotion;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Enums\ViewPaths\Admin\ClearanceSale;
use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ClearanceSaleVendorOfferController extends BaseController
{
    public function __construct(
        private readonly ShopRepositoryInterface                $shopRepo,
        private readonly StockClearanceSetupRepositoryInterface $stockClearanceSetupRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    )
    {
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {

        return $this->getView();
    }

    public function getView(): View
    {
        $clearanceConfig = $this->stockClearanceSetupRepo->getFirstWhere(params: ['setup_by' => 'vendor', 'user_id' => auth('seller')->id()]);
        $allVendorList = $this->stockClearanceSetupRepo->getListWhere(
            filters: [
                'setup_by' => 'vendor',
                'is_active' => 1,
                'show_in_homepage_once' => 0,
                'duration_start_date' => Carbon::now(),
                'duration_end_date' => Carbon::now(),
            ],
            relations: ['seller', 'products'],
            dataLimit: 'all'
        );

        $vendorList = $this->stockClearanceSetupRepo->getListWhere(
            filters: [
                'setup_by' => 'vendor',
                'show_in_homepage_once' => 1,
                'is_active' => 1,
                'duration_start_date' => Carbon::now(),
                'duration_end_date' => Carbon::now(),
            ],
            relations: ['seller', 'products'],
            dataLimit: 'all'
        );
        $allVendorList = $this->getProcessVendorList(vendors: $allVendorList);
        $vendorList = $this->getProcessVendorList(vendors: $vendorList);

        return view(ClearanceSale::VENDOR_OFFERS[VIEW], compact('vendorList', 'allVendorList', 'clearanceConfig'));
    }

    public function getProcessVendorList(object|array|null $vendors)
    {
        return $vendors->map(function ($vendor) {
            $productReviews = $vendor?->seller?->pluck('reviews')->collapse();
            $vendor->products_count = $vendor?->products?->count() ?? 0;
            if ($productReviews) {
                $vendor->average_rating = $productReviews->avg('rating') ?? 0;
                $vendor->review_count = $productReviews->count();
            }
            return $vendor;
        });
    }

    public function getSearchedVendorsView(Request $request): JsonResponse
    {
        $searchValue = $request['searchValue'] ?? null;
        $allVendorList = $this->stockClearanceSetupRepo->getListWhere(
            searchValue: $searchValue,
            filters: [
                'setup_by' => 'vendor',
                'is_active' => 1,
                'show_in_homepage_once' => 0,
                'duration_start_date' => Carbon::now(),
                'duration_end_date' => Carbon::now(),
            ],
            relations: ['products', 'shop' => function ($query) {
                return $query->with(['products' => function ($query) {
                    return $query->active()->with('reviews', function ($query) {
                        $query->active();
                    });
                }]);
            }],
            dataLimit: 'all'
        );
        $allVendorList = $this->getProcessVendorList(vendors: $allVendorList);
        return response()->json([
            'result' => view(ClearanceSale::VENDOR_SEARCH[VIEW], compact('allVendorList'))->render(),
        ]);
    }

    public function updateVendorOfferStatus(Request $request): JsonResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'stock_clearance_vendor_offer_in_homepage',
            value: $request->get('homepage-status', 0));
        return response()->json([
            'status' => true,
            'message' => translate('vendor_offer_status_updated_successfully')
        ]);
    }

    public function addClearanceVendorProduct(Request $request): JsonResponse
    {
        $status = true;
        $message = translate('vendor_added_successfully');

        $stockClearanceSetUp = $this->stockClearanceSetupRepo->getFirstWhere(params: ['shop_id' => $request['shopId']]);
        if($stockClearanceSetUp['show_in_homepage_once']) {
                $status = false;
                $message = translate('vendor_already_exists');
        }

        $dataArray = [
            'show_in_homepage' => 1,
            'show_in_homepage_once' => 1
        ];

        $this->stockClearanceSetupRepo->update(id: $stockClearanceSetUp['id'], data: $dataArray);

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function updateVendorStatus(Request $request): JsonResponse
    {
        $this->stockClearanceSetupRepo->update(id: $request['id'], data: ['show_in_homepage' => $request->get('status', 0)]);
        return response()->json([
            'status' => true,
            'message' => translate('vendor_status_successfully')
        ]);
    }

    public function deleteVendorOffer(Request $request): RedirectResponse
    {
        $this->stockClearanceSetupRepo->update(id: $request['id'], data: ['show_in_homepage_once' => 0]);
        Toastr::success(translate('vendor_deleted_successfully'));
        return back();
    }


}
