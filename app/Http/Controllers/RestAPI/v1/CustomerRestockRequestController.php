<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Traits\CommonTrait;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerRestockRequestController extends Controller
{
    use CommonTrait;

    public function __construct(
        private readonly RestockProductRepositoryInterface         $restockProductRepo,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
    )
    {
    }

    public function restockRequestsList(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);

        $requestList = $this->restockProductRepo->getListWhere(
            orderBy: ['updated_at' => 'desc'],
            filters: ['customer_id' => $user->id],
            relations: ['product', 'product.rating'],
            dataLimit: $request['limit'] ?? 'all',
            offset: $request['offset']
        );

        $requestList->map(function ($data) {
            $data->product = Helpers::product_data_formatting($data->product, false);
            $data->fcm_topic = getRestockProductFCMTopic(restockRequest: $data);
            return $data;
        });

        return response()->json([
            'data' => $request->has('limit') ? $requestList->items() : $requestList,
            'total_size' => $request->has('limit') ? $requestList->total() : count($requestList),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
        ], 200);
    }

    public function deleteRestockRequests(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);

        if ($request['type'] == 'all') {
            $this->restockProductCustomerRepo->delete(params: ['customer_id' => $user->id]);
        } else if ($request['id']) {
            $this->restockProductCustomerRepo->delete(params: ['restock_product_id' => $request['id'], 'customer_id' => $user->id]);
        }

        $restockProducts = $this->restockProductRepo->getListWhere(relations:['restockProductCustomers'], dataLimit: 'all');
        $restockProducts->map(function ($restockProduct) {
            if($restockProduct->restockProductCustomers->count() === 0) {
                $this->restockProductRepo->delete(params: ['id' => $restockProduct['id']]);
            }
        });
        return response()->json(['message' => translate('Deleted_successfully')], 200);
    }
}
