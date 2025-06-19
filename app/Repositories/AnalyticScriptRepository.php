<?php

namespace App\Repositories;

use App\Contracts\Repositories\AnalyticScriptRepositoryInterface;
use App\Models\AnalyticScript;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AnalyticScriptRepository implements AnalyticScriptRepositoryInterface
{
    public function __construct(
        private readonly AnalyticScript $analyticScript,
    )
    {
    }

    public function add(array $data): string|object
    {
        cacheRemoveByType(type: 'business_settings');
        cacheRemoveByType(type: 'analytic_script');
        return $this->analyticScript->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->analyticScript->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->analyticScript->with($relations)
                ->when(!empty($orderBy), function ($query) use ($orderBy) {
                    return $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
                });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->analyticScript
            ->with($relations)
            ->when($searchValue, function ($query) use($searchValue){
                $query->where('key', 'like', "%{$searchValue}%");
            })
            ->when(isset($filters['id']) , function ($query) use ($filters){
                return $query->where(['id' => $filters['id']]);
            })
            ->when(isset($filters['key']) , function ($query) use ($filters){
                return $query->where(['key' => $filters['key']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(key($orderBy),current($orderBy));
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function getListWhereIn(array $orderBy = [], string $searchValue = null, array $filters = [], array $whereInFilters = [], array $relations = [], array $nullFields = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->analyticScript
            ->with($relations)->where($filters)
            ->when($searchValue, function ($query) use($searchValue){
                return $query->where('key', 'like', "%$searchValue%");
            })
            ->when(isset($filters['id']) , function ($query) use ($filters){
                return $query->where(['id' => $filters['id']]);
            })
            ->when(isset($filters['key']) , function ($query) use ($filters){
                return $query->where(['key' => $filters['key']]);
            })
            ->when(!empty($whereInFilters), function ($query) use ($whereInFilters) {
                foreach ($whereInFilters as $key => $filterIndex){
                    $query->whereIn($key , $filterIndex);
                }
            })
            ->when(!empty($nullFields), function ($query) use ($nullFields) {
                return $query->whereNull($nullFields);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }



    public function update(string $id, array $data): bool
    {
        cacheRemoveByType(type: 'business_settings');
        cacheRemoveByType(type: 'analytic_script');
        return $this->analyticScript->where('id', $id)->update($data);
    }

    public function updateWhere(array $params, array $data): bool
    {
        cacheRemoveByType(type: 'business_settings');
        cacheRemoveByType(type: 'analytic_script');
        $this->analyticScript->where($params)->update($data);
        return true;
    }

    public function updateOrInsert(array $params, array $data): bool
    {
        cacheRemoveByType(type: 'business_settings');
        cacheRemoveByType(type: 'analytic_script');
        $this->analyticScript->updateOrInsert($params, $data);
        return true;
    }

    public function whereJsonContains(array $params, array $value): ?Model
    {
        return $this->analyticScript->where($params)->whereJsonContains('value', $value)->first();
    }

    public function delete(array $params): bool
    {
        cacheRemoveByType(type: 'business_settings');
        cacheRemoveByType(type: 'analytic_script');
        $this->analyticScript->where($params)->delete();
        return true;
    }
}
