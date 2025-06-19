<?php

namespace App\Http\Controllers\Admin\Promotion;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\FlashDealRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Enums\ViewPaths\Admin\ClearanceSale;
use App\Enums\ViewPaths\Admin\FeatureDeal;
use App\Http\Controllers\BaseController;
use App\Services\PrioritySetupService;
use App\Traits\InHouseTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ClearanceSalePrioritySetupController extends BaseController
{
    use InHouseTrait;

    public function __construct(
        private readonly BusinessSettingRepositoryInterface     $businessSettingRepo,
        private readonly StockClearanceSetupRepositoryInterface $stockClearanceSetupRepo,
        private readonly PrioritySetupService                   $prioritySetupService,
    )
    {
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView($request);
    }

    public function getView(Request $request): View
    {
        $stockClearancePriority = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'stock_clearance_product_list_priority'])?->value ?? '';
        $stockClearanceVendors = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'stock_clearance_vendor_priority'])?->value ?? '';
        $availableVendors = $this->stockClearanceSetupRepo->getListWhere(filters: ['is_active' => 1], relations: ['shop'], dataLimit: 'all')->pluck('shop');

        $availableVendors = $availableVendors->map(function($vendor) {
            if (is_null($vendor)) {
                $vendor = $this->getInHouseShopObject();
            }
            return $vendor;
        });

        return view(ClearanceSale::PRIORITY_SETUP[VIEW], [
            'stockClearancePriority' => json_decode($stockClearancePriority, true),
            'stockClearanceVendors' => json_decode($stockClearanceVendors, true) ?? [],
            'availableVendors' => $availableVendors,
        ]);
    }

    public function updateConfig(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(
            type: 'stock_clearance_vendor_priority',
            value: json_encode($request['vendor_priorities_id'] ?? []),
        );
        $this->businessSettingRepo->updateOrInsert(
            type: 'stock_clearance_product_list_priority',
            value: json_encode($this->prioritySetupService->updateStockClearanceProductPrioritySetupData(request: $request))
        );
        Toastr::success(translate('Priority_setup_updated_successfully'));
        return redirect()->back();
    }


}
