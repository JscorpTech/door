<?php

namespace App\Repositories;

use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Models\Color;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ColorRepository implements ColorRepositoryInterface
{
    public function __construct(private Color $color)
    {
    }


    public function add(array $data): string|object
    {
        return true;
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->color->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->color->when($relations, function ($query) use ($relations) {
            return $query->with($relations);
        })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(
        array      $orderBy = [],
        string     $searchValue = null,
        array      $filters = [],
        array      $relations = [],
        int|string $dataLimit = DEFAULT_DATA_LIMIT,
        int        $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->color
            ->when($searchValue, function ($query) use ($searchValue) {
                return $query->where('name', 'like', "%$searchValue%");
            })
            ->when($filters && $filters['code'], function ($query) use ($filters) {
                return $query->where(['code' => $filters['code']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return true;
    }

    public function delete(array $params): bool
    {
        return true;
    }
}
