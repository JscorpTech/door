<?php

namespace App\Repositories;

use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Models\StockClearanceSetup;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class StockClearanceSetupRepository implements StockClearanceSetupRepositoryInterface
{
    public function __construct(
        private readonly StockClearanceSetup $stockClearanceSetup,
    )
    {
    }

    public function add(array $data): string|object
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceSetup->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->stockClearanceSetup->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        // TODO: Implement getList() method.
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->stockClearanceSetup->with($relations)
            ->when($searchValue, function ($query) use($searchValue){
                $query->whereHas('shop', function ($query) use($searchValue){
                    return $query->where('name', 'like', "%$searchValue%");
                });
            })
            ->when(isset($filters['setup_by']), function ($query) use($filters){
                return $query->where('setup_by', $filters['setup_by']);
            })
            ->when(isset($filters['show_in_homepage_once']), function ($query) use($filters){
                return $query->where('show_in_homepage_once', $filters['show_in_homepage_once']);
            })
            ->when(isset($filters['is_active']), function ($query) use($filters){
                return $query->where('is_active', $filters['is_active']);
            })
            ->when(isset($filters['duration_start_date']) && isset($filters['duration_end_date']), function ($query) use($filters){
                return $query->whereDate('duration_start_date', '<=', $filters['duration_start_date'])->whereDate('duration_end_date', '>=', $filters['duration_end_date']);
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
        return $this->stockClearanceSetup->where('id', $id)->update($data);
    }

    public function updateByParams(array $params, array $data): bool
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceSetup->where($params)->update($data);
    }

    public function updateOrCreate(array $params, array $value): mixed
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceSetup->updateOrCreate($params, $value);
    }

    public function delete(array $params): bool
    {
        cacheRemoveByType(type: 'business_settings');
        return $this->stockClearanceSetup->where($params)->delete();
    }

}
