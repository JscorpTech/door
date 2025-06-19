<?php

namespace App\Repositories;

use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Models\RestockProduct;
use App\Models\Translation;
use App\Traits\ProductTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class RestockProductRepository implements RestockProductRepositoryInterface
{
    use ProductTrait;

    public function __construct(
        private readonly RestockProduct $restockProduct,
        private readonly Translation    $translation,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->restockProduct->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->restockProduct->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        // TODO: Implement getList() method.
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->restockProduct
            ->with($relations)
            ->withCount(['restockProductCustomers'])
            ->when(isset($filters['added_by']), function ($query) use ($filters) {
                $query->whereHas('product', function ($query) use ($filters) {
                    $query->when($this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) {
                        return $query->where(['added_by' => 'admin']);
                    })->when(!$this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) use ($filters) {
                        return $query->where(['added_by' => 'seller'])
                            ->when(isset($filters['seller_id']) && $filters['seller_id'] != 'all', function ($query) use ($filters) {
                                return $query->where(['user_id' => $filters['seller_id']]);
                            });
                    });
                });
            })
            ->when(isset($filters['customer_id']), function ($query) use ($filters) {
                $query->whereHas('restockProductCustomers', function ($query) use ($filters) {
                    return $query->where('customer_id', $filters['customer_id']);
                })->whereHas('product', function ($query) {
                    return $query->active();
                })->with('product', function ($query) {
                    return $query->active()->withCount(['reviews' => function ($query) {
                        return $query->active()->whereNull('delivery_man_id');
                    }]);
                });
            })
            ->when(isset($filters['product_id']), function ($query) use ($filters) {
                return $query->where(['product_id' => $filters['product_id']]);
            })->when(isset($filters['variant']) && !empty($filters['variant']), function ($query) use ($filters) {
                return $query->where(['variant' => $filters['variant']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function getListWhereBetween(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], string $whereBetween = null, array $whereBetweenFilters = [], int|string $takeItem = null, int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->restockProduct
            ->with($relations)
            ->withCount(['restockProductCustomers'])
            ->when(is_array($relations) && in_array('product', $relations), function ($query) use ($filters) {
                $query->whereHas('product', function ($query) use ($filters) {
                    return $query->withCount(['reviews' => function ($query) {
                        $query->active()->whereNull('delivery_man_id');
                    }]);
                });
            })
            ->when(isset($filters['added_by']), function ($query) use ($filters) {
                $query->whereHas('product', function ($query) use ($filters) {
                    $query->when($this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) {
                        return $query->where(['added_by' => 'admin']);
                    })->when(!$this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) use ($filters) {
                        return $query->where(['added_by' => 'seller'])
                            ->when(isset($filters['seller_id']) && $filters['seller_id'] != 'all', function ($query) use ($filters) {
                                return $query->where(['user_id' => $filters['seller_id']]);
                            });
                    })->withCount(['reviews' => function ($query) {
                        $query->active()->whereNull('delivery_man_id');
                    }]);
                });
            })
            ->when(isset($filters['brand_ids']) && !empty($filters['brand_ids']), function ($query) use ($filters) {
                $query->whereHas('product', function ($query) use ($filters) {
                    return $query->whereIn('brand_id', $filters['brand_ids']);
                });
            })
            ->when(!empty($searchValue), function ($query) use ($searchValue, $filters) {
                $query->whereHas('product', function ($query) use ($searchValue, $filters) {
                    $productIds = $this->translation->where('translationable_type', 'App\Models\Product')
                        ->where('key', 'name')
                        ->where('value', 'like', "%{$searchValue}%")
                        ->pluck('translationable_id');

                    return $query->where('name', 'like', "%{$searchValue}%")
                        ->orWhere(function ($query) use ($searchValue) {
                            $query->where('code', 'like', "%{$searchValue}%");
                        })
                        ->when(isset($filters['added_by']) && !$this->isAddedByInHouse($filters['added_by']), function ($query) use ($filters, $productIds) {
                            $query->whereIn('id', $productIds)
                                ->where(['added_by' => 'seller'])
                                ->when(isset($filters['seller_id']), function ($query) use ($filters) {
                                    return $query->where(['user_id' => $filters['seller_id']]);
                                });
                        })
                        ->when(isset($filters['added_by']) && $this->isAddedByInHouse($filters['added_by']), function ($query) use ($filters, $productIds) {
                            $query->orWhereIn('id', $productIds)->where(['added_by' => 'admin']);
                        });
                });
            })->when(!empty($whereBetween) && !empty($whereBetweenFilters), function ($query) use ($whereBetween, $whereBetweenFilters) {
                $query->whereBetween($whereBetween, $whereBetweenFilters);
            })->when(isset($filters['brand_id']) && $filters['brand_id'] != 'all', function ($query) use ($filters) {
                $query->whereHas('product', function ($query) use ($filters) {
                    return $query->where('brand_id', $filters['brand_id']);
                });
            })->when(isset($filters['category_id']) && $filters['category_id'] != 'all', function ($query) use ($filters) {
                $query->whereHas('product', function ($query) use ($filters) {
                    return $query->where('category_id', $filters['category_id']);
                });
            })->when(isset($filters['sub_category_id']) && $filters['sub_category_id'] != 'all', function ($query) use ($filters) {
                $query->whereHas('product', function ($query) use ($filters) {
                    return $query->where('sub_category_id', $filters['sub_category_id']);
                });
            })->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends(['searchValue' => $searchValue]);
    }

    public function update(string $id, array $data): bool
    {
        return $this->restockProduct->where('id', $id)->update($data);
    }

    public function updateByParams(array $params, array $data): bool
    {
        return $this->restockProduct->where($params)->update($data);
    }

    public function updateOrCreate(array $params, array $value): mixed
    {
        return $this->restockProduct->updateOrCreate($params, $value);
    }

    public function delete(array $params): bool
    {
        return $this->restockProduct->where($params)->delete();
    }

}
