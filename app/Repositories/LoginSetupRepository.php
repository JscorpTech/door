<?php

namespace App\Repositories;

use App\Contracts\Repositories\LoginSetupRepositoryInterface;
use App\Models\LoginSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class LoginSetupRepository implements LoginSetupRepositoryInterface
{
    public function __construct(
        private readonly LoginSetup $loginSetup,
    )
    {
    }

    public function add(array $data): string|object
    {
        cacheRemoveByType(type: 'login_setups');
        return $this->loginSetup->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->loginSetup->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->loginSetup->with($relations)
                ->when(!empty($orderBy), function ($query) use ($orderBy) {
                    return $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
                });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->loginSetup
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
        $query = $this->loginSetup
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
        cacheRemoveByType(type: 'login_setups');
        return $this->loginSetup->where('id', $id)->update($data);
    }

    public function updateWhere(array $params, array $data): bool
    {
        cacheRemoveByType(type: 'login_setups');
        $this->loginSetup->where($params)->update($data);
        return true;
    }

    public function updateOrInsert(string $key, mixed $value): bool
    {
        cacheRemoveByType(type: 'login_setups');
        $this->loginSetup->updateOrInsert(['key' => $key], [
            'value' => $value,
            'updated_at' => now()
        ]);

        return true;
    }

    public function whereJsonContains(array $params, array $value): ?Model
    {
        return $this->loginSetup->where($params)->whereJsonContains('value', $value)->first();
    }

    public function delete(array $params): bool
    {
        cacheRemoveByType(type: 'login_setups');
        $this->loginSetup->where($params)->delete();
        return true;
    }
}
