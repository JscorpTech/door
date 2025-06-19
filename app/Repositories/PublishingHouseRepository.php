<?php

namespace App\Repositories;

use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Models\PublishingHouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class PublishingHouseRepository implements PublishingHouseRepositoryInterface
{
    public function __construct(
        private readonly PublishingHouse $publishingHouse,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->publishingHouse->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->publishingHouse->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->publishingHouse->with($relations)
                ->when(!empty($orderBy), function ($query) use ($orderBy) {
                    return $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
                });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->publishingHouse->when($searchValue, function ($query) use($searchValue){
                $query->Where('name', 'like', "%$searchValue%")->orWhere('id', $searchValue);
            })
            ->when(isset($filters['name']), function ($query) use($filters) {
                return $query->where(['name' => $filters['name']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);

    }

    public function update(string $id, array $data): bool
    {
        return $this->publishingHouse->find($id)->update($data);
    }

    public function updateOrCreate(array $params, array $value): mixed
    {
        return $this->publishingHouse->updateOrCreate($params, $value);
    }

    public function delete(array $params): bool
    {
        $this->publishingHouse->where($params)->delete();
        return true;
    }

}
