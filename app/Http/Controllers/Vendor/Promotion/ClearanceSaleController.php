<?php

namespace App\Http\Controllers\Vendor\Promotion;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Enums\ViewPaths\Vendor\ClearanceSale;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\ClearanceSaleSetupRequest;
use App\Services\ClearanceSaleService;
use App\Services\ProductService;
use App\Services\StockClearanceProductService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ClearanceSaleController extends BaseController
{
    public function __construct(
        private readonly StockClearanceSetupRepositoryInterface   $stockClearanceSetupRepo,
        private readonly StockClearanceProductRepositoryInterface $stockClearanceProductRepo,
        private readonly ProductRepositoryInterface               $productRepo,
        private readonly ClearanceSaleService                     $clearanceSaleService,
        private readonly StockClearanceProductService             $stockClearanceProductService,
        private readonly ProductService                           $productService,
    )
    {
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView($request);
    }

    public function getView(Request $request): View
    {
        $searchValue = $request['searchValue'] ?? null;
        $clearanceProductIds = $this->stockClearanceProductRepo->getListWhere(filters: ['added_by' => 'vendor', 'user_id' => auth('seller')->id()])->pluck('product_id')->toArray();
        $products = $this->productRepo->getListWithScope(
            orderBy: ['id' => 'desc'],
            scope: "active",
            filters: [
                'added_by' => 'seller',
                'seller_id' => auth('seller')->id(),
                'status' => 1
            ],
            whereNotIn: ['id' => $clearanceProductIds],
            dataLimit: 'all'
        );
        $clearanceConfig = $this->stockClearanceSetupRepo->getFirstWhere(params: ['setup_by' => 'vendor', 'user_id' => auth('seller')->id()]);
        $stockClearanceProduct = $this->stockClearanceProductRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            filters: ['added_by' => 'vendor', 'user_id' => auth('seller')->id()],
            relations: ['product']
        );
        return view(ClearanceSale::LIST[VIEW], ['clearanceConfig' => $clearanceConfig, 'products' => $products, 'stockClearanceProduct' => $stockClearanceProduct]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $clearanceConfig = $this->stockClearanceSetupRepo->getFirstWhere(params: ['setup_by' => 'vendor', 'user_id' => auth('seller')->id()]);
        if ($clearanceConfig) {
            $this->stockClearanceSetupRepo->updateByParams(
                params: ['setup_by' => 'vendor', 'user_id' => auth('seller')->id()],
                data: ['is_active' => $request->get('status', 0)]
            );
            return response()->json([
                'status' => 1,
                'message' => translate('Status_updated_successfully'),
            ]);
        }
        return response()->json([
            'status' => 0,
            'message' => translate('Please_setup_the_configuration_first'),
        ]);
    }

    public function updateClearanceConfig(ClearanceSaleSetupRequest $request): RedirectResponse
    {
        $data = $this->clearanceSaleService->getConfigData(request: $request, setupBy: 'vendor', vendorId: auth('seller')->id(), shopId: auth('seller')->user()?->shop?->id);
        $config = $this->stockClearanceSetupRepo->updateOrCreate(params: ['setup_by' => 'vendor', 'user_id' => auth('seller')->id()], value: $data);

        if ($config['discount_type'] == 'flat') {
            $this->stockClearanceProductRepo->updateByParams(
                params: ['setup_id' => $config['id']],
                data: ['discount_type' => 'percentage', 'discount_amount' => $request['discount_amount']]
            );
        }

        Toastr::success(translate('Setup_updated_successfully'));
        return back();
    }

    public function getSearchedProductsView(Request $request): JsonResponse
    {
        $searchValue = $request['searchValue'] ?? null;
        $clearanceProductIds = $this->stockClearanceProductRepo->getListWhere(filters: ['added_by' => 'vendor', 'user_id' => auth('seller')->id()])->pluck('product_id')->toArray();
        $products = $this->productRepo->getListWithScope(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            scope: "active",
            filters: [
                'added_by' => 'seller',
                'seller_id' => auth('seller')->id(),
            ],
            whereNotIn: ['id' => $clearanceProductIds],
            dataLimit: 'all'
        );
        return response()->json([
            'count' => $products->count(),
            'result' => view(ClearanceSale::SEARCH[VIEW], compact('products'))->render(),
        ]);
    }

    public function getMultipleProductDetailsView(Request $request): JsonResponse
    {
        $selectedProducts = $this->productRepo->getListWhere(
            filters: [
                'productIds' => $request['productIds'],
            ],
            dataLimit: 'all'
        );
        $clearanceConfig = $this->stockClearanceSetupRepo->getFirstWhere(params: ['setup_by' => 'vendor', 'user_id' => auth('seller')->id()]);
        return response()->json([
            'result' => view(ClearanceSale::MULTIPLE_PRODUCT_DETAILS[VIEW], compact('selectedProducts', 'clearanceConfig'))->render(),
        ]);
    }

    public function addClearanceProduct(Request $request): jsonResponse
    {
        $condition = [];
        $clearanceProductLists = $this->productRepo->getListWhere(
            filters: [
                'productIds' => $request['productIds'],
            ],
            relations: ['shop'],
            dataLimit: 'all'
        );
        $clearanceConfig = $this->stockClearanceSetupRepo->getFirstWhere(params: ['setup_by' => 'vendor', 'user_id' => auth('seller')->id()]);
        foreach ($clearanceProductLists as $key => $clearanceProductList) {
            $stockClearanceProduct = $this->stockClearanceProductRepo->getFirstWhere(params: ['product_id' => $clearanceProductList->id]);
            if (!$stockClearanceProduct) {
                if ($clearanceConfig) {
                    $condition = $this->stockClearanceProductService->checkAddConditions(request: $request, product: $clearanceProductList, config: $clearanceConfig);
                    if (!$condition['status']) {
                        return response()->json([
                            'status' => $condition['status'],
                            'message' => $condition['message']
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => translate('please_setup_the_configuration_first')
                    ], 200);
                }
            }
        }

        foreach ($clearanceProductLists as $key => $clearanceProductList) {
            if ($clearanceConfig) {
                $stockClearanceProduct = $this->stockClearanceProductRepo->getFirstWhere(params: ['product_id' => $clearanceProductList->id]);
                $condition = $this->stockClearanceProductService->checkAddConditions(request: $request, product: $clearanceProductList, config: $clearanceConfig);
                if (!$stockClearanceProduct && $condition['status']) {
                    $dataArray = [
                        'added_by' => 'vendor',
                        'user_id' => auth('seller')->id(),
                        'shop_id' => $clearanceConfig['shop_id'],
                        'is_active' => 1,
                        'setup_id' => $clearanceConfig['id'],
                        'product_id' => $clearanceProductList->id,
                        'discount_type' => $clearanceConfig->discount_type == 'flat' ? 'percentage' : $request['discount_type'][$clearanceProductList->id],
                        'discount_amount' => $clearanceConfig->discount_type == 'flat' ? $clearanceConfig->discount_amount : ($request['discount_type'][$clearanceProductList->id] == 'percentage' ? $request['discount_amount'][$clearanceProductList->id] : currencyConverter(amount: $request['discount_amount'][$clearanceProductList->id])),
                    ];
                    $this->stockClearanceProductRepo->add(data: $dataArray);
                }
            }
        }
        return response()->json([
            'status' => $condition['status'],
            'message' => $condition['message'],
            'redirect_url' => route('vendor.clearance-sale.index')
        ], 200);
    }

    public function updateDiscountAmount(Request $request)
    {
        $clearanceConfig = $this->stockClearanceSetupRepo->getFirstWhere(params: ['setup_by' => 'admin']);
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request->get('product_id')]);
        $condition = $this->stockClearanceProductService->checkConditions(request: $request, product: $product, config: $clearanceConfig);

        if ($condition['status']) {
            $dataArray = [
                'discount_amount' => $request['discount_type'] == 'percentage' ? $request['discount_amount'] : currencyConverter(amount: $request['discount_amount']),
                'discount_type' => $request['discount_type'],
            ];
            $this->stockClearanceProductRepo->updateByParams(params: ['product_id' => $request['product_id']], data: $dataArray);
        }
        return response()->json([
            'status' => $condition['status'],
            'message' => $condition['message']
        ]);
    }

    public function updateProductStatus(Request $request): JsonResponse
    {
        $stockClearanceProduct = $this->stockClearanceProductRepo->getFirstWhere(params: ['product_id' => $request['product_id']], relations: ['setup']);
        if ($request['status'] == 1 && !$this->productService->validateStockClearanceProductDiscount(stockClearanceProduct: $stockClearanceProduct)) {
            return response()->json([
                'status' => 0,
                'message' => translate('Your_products_unit_price_is_lower_then_offer_price'),
            ]);
        }

        $this->stockClearanceProductRepo->updateByParams(
            params: ['product_id' => $request['product_id']],
            data: ['is_active' => $request->get('status', 0)]
        );

        return response()->json([
            'status' => 1,
            'message' => translate('product_status_updated_successfully'),
        ]);
    }

    public function deleteClearanceProduct(string|int $productId): RedirectResponse
    {
        $this->stockClearanceProductRepo->delete(params: ['product_id' => $productId]);
        Toastr::success(translate('stock_clearance_product_removed_successfully'));
        return back();
    }

    public function deleteClearanceAllProduct(): RedirectResponse
    {
        $this->stockClearanceProductRepo->delete(params: ['added_by' => 'vendor', 'user_id' => auth('seller')->id()]);
        Toastr::success(translate('all_stock_clearance_products_removed_successfully'));
        return back();
    }
}
