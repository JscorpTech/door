<?php

namespace App\Repositories;

use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Models\RestockProductCustomer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class RestockProductCustomerRepository implements RestockProductCustomerRepositoryInterface
{
    public function __construct(
        private readonly RestockProductCustomer $restockProductCustomer,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->restockProductCustomer->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->restockProductCustomer->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        // TODO: Implement getList() method.
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->restockProductCustomer
            ->with($relations)
            ->when(isset($filters['restock_product_ids']), function ($query) use ($filters) {
                return $query->whereIn('restock_product_id', $filters['restock_product_ids']);
            })
            ->when(isset($filters['restock_product_id']), function ($query) use ($filters) {
                return $query->where(['restock_product_id' => $filters['restock_product_id']]);
            })->when(isset($filters['customer_id']), function ($query) use ($filters) {
                return $query->where(['customer_id' => $filters['customer_id']]);
            })->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->restockProductCustomer->where('id', $id)->update($data);
    }

    public function updateByParams(array $params, array $data): bool
    {
        return $this->restockProductCustomer->where($params)->update($data);
    }

    public function updateOrCreate(array $params, array $value): mixed
    {
        return $this->restockProductCustomer->updateOrCreate($params, $value);
    }

    public function delete(array $params): bool
    {
        return $this->restockProductCustomer->where($params)->delete();
    }

}
