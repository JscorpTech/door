<?php

namespace App\Repositories;

use App\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function __construct(
        private readonly Subscription $subscription,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->subscription->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->subscription->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->subscription->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->subscription->where($filters)->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->orWhere('email', 'like', "%$searchValue%");
            })->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends(['searchValue' => $searchValue]);
    }

    public function getListWhereBetween(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], string $whereBetween = null, array $whereBetweenFilters = [], int|string $takeItem = null, int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null, array|object $appends = []): Collection|LengthAwarePaginator
    {
        $query = $this->subscription->where($filters)
            ->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->orWhere('email', 'like', "%$searchValue%");
            })
            ->when(!empty($whereBetween) && !empty($whereBetweenFilters), function ($query) use ($whereBetween, $whereBetweenFilters) {
                $query->whereBetween($whereBetween, $whereBetweenFilters);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        if (!empty($takeItem) && $dataLimit == 'all') {
            return $query->get()->slice(0, $takeItem)->values();
        } else if (!empty($takeItem) && $dataLimit != 'all') {
            $allResults = $query->get();
            $allResults = $allResults->slice(0, $takeItem);
            $page = request('page') ?? 1;
            $perPage = $dataLimit;
            $paginator = new LengthAwarePaginator(
                items: $allResults->forPage($page, $perPage)->values(),
                total: $allResults->count(),
                perPage: $perPage,
                currentPage: $page,
                options: ['path' => request()->url(), 'query' => request()->query()]);
            return $paginator->appends($appends);
        }
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($appends);
    }

    public function update(string $id, array $data): bool
    {
        return $this->subscription->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->subscription->where($params)->delete();
        return true;
    }
}
