<?php

namespace App\Repositories;

use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Models\StockClearanceProduct;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class StockClearanceProductRepository implements StockClearanceProductRepositoryInterface
{
    public function __construct(
        private readonly StockClearanceProduct $stockClearanceProduct,
        private readonly Translation           $translation,
    )
    {
    }

    public function add(array $data): string|object
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceProduct->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->stockClearanceProduct->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        // TODO: Implement getList() method.
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->stockClearanceProduct->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $product_ids = $this->translation->where('translationable_type', 'App\Models\Product')
                    ->where('key', 'name')
                    ->where('value', 'like', "%{$searchValue}%")
                    ->pluck('translationable_id');
                return $query->whereHas('product', function ($query) use ($searchValue, $product_ids) {
                    $query->where('name', 'like', "%{$searchValue}%")
                        ->orWhereIn('id', $product_ids);
                });
            })
            ->when(is_array($relations) && in_array('product', $relations), function ($query) use ($relations) {
                return $query->whereHas('product');
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            })
            ->when(isset($filters['added_by']), function ($query) use ($filters) {
                return $query->where(['added_by' => $filters['added_by']]);
            })
            ->when(isset($filters['user_id']), function ($query) use ($filters) {
                return $query->where(['user_id' => $filters['user_id']]);
            })
            ->when(isset($filters['theme']), function ($query) use ($filters) {
                return $query->where('theme', $filters['theme']);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceProduct->where('id', $id)->update($data);
    }

    public function updateByParams(array $params, array $data): bool
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceProduct->where($params)->update($data);
    }

    public function updateOrCreate(array $params, array $value): mixed
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceProduct->updateOrCreate($params, $value);
    }

    public function delete(array $params): bool
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceProduct->where($params)->delete();
    }

}
