<?php

namespace App\Repositories;

use App\Contracts\Repositories\DigitalProductPublishingHouseRepositoryInterface;
use App\Models\DigitalProductPublishingHouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DigitalProductPublishingHouseRepository implements DigitalProductPublishingHouseRepositoryInterface
{
    public function __construct(
        private readonly DigitalProductPublishingHouse $digitalProductPublishingHouse,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->digitalProductPublishingHouse->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->digitalProductPublishingHouse->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->digitalProductPublishingHouse->with($relations)
                ->when(!empty($orderBy), function ($query) use ($orderBy) {
                    return $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
                });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->digitalProductPublishingHouse->when($searchValue, function ($query) use($searchValue){
                $query->Where('name', 'like', "%$searchValue%")->orWhere('id', $searchValue);
            })
            ->when(isset($filters['publishing_house_id']), function ($query) use($filters) {
                return $query->where(['publishing_house_id' => $filters['publishing_house_id']]);
            })
            ->when(isset($filters['product_id']), function ($query) use($filters) {
                return $query->where(['product_id' => $filters['product_id']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);

    }

    public function getListWhereNotIn(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], array $nullFields = [], array $whereNotIn = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->digitalProductPublishingHouse->where($filters)
            ->when(!empty($whereNotIn), function ($query) use ($whereNotIn) {
                foreach ($whereNotIn as $key => $whereNotInIndex) {
                    $query->whereNotIn($key, $whereNotInIndex);
                }
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function deleteWhereNotIn(string $searchValue = null, array $filters = [], array $nullFields = [], array $whereNotIn = []): mixed
    {
        return $this->digitalProductPublishingHouse->where($filters)
            ->when(!empty($whereNotIn), function ($query) use ($whereNotIn) {
                foreach ($whereNotIn as $key => $whereNotInIndex) {
                    $query->whereNotIn($key, $whereNotInIndex);
                }
            })->delete();
    }

    public function update(string $id, array $data): bool
    {
        return $this->digitalProductPublishingHouse->find($id)->update($data);
    }

    public function updateOrCreate(array $params, array $value): mixed
    {
        return $this->digitalProductPublishingHouse->updateOrCreate($params, $value);
    }

    public function delete(array $params): bool
    {
        $this->digitalProductPublishingHouse->where($params)->delete();
        return true;
    }

}
