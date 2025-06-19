<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(
        private readonly User $user,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->user->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->user->with($relations)->where($params)->first();
    }

    public function getByIdentity(array $filters = []): ?Model
    {
        return $this->user
            ->when(isset($filters['phone']) && $filters['phone'], function ($query) use ($filters) {
                return $query->where(['phone' => $filters['phone']]);
            })
            ->when(isset($filters['email']) && $filters['email'], function ($query) use ($filters) {
                return $query->where(['email' => $filters['email']]);
            })
            ->when(isset($filters['identity']) && $filters['identity'], function ($query) use ($filters) {
                return $query->orWhere(function ($query) use ($filters) {
                    return $query->whereNotNull('email')->where(['email' => $filters['identity']]);
                });
            })
            ->when(isset($filters['identity']) && $filters['identity'], function ($query) use ($filters) {
                return $query->orWhere(function ($query) use ($filters) {
                    return $query->whereNotNull('phone')->where(['phone' => $filters['identity']]);
                });
            })->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->user->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->user->with($relations)
            ->when(empty($filters['withCount']), function ($query) use ($filters) {
                $filters = array_filter($filters, function ($key) {
                    return $key !== 'avoid_walking_customer';
                }, ARRAY_FILTER_USE_KEY);
                return $query->where($filters);
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->orWhere('f_name', 'like', "%$searchValue%")
                    ->orWhere('l_name', 'like', "%$searchValue%")
                    ->orWhere('phone', 'like', "%$searchValue%")
                    ->orWhere('email', 'like', "%$searchValue%");
            })
            ->when(isset($filters['withCount']), function ($query) use ($filters) {
                return $query->withCount($filters['withCount']);
            })
            ->when(isset($filters['avoid_walking_customer']) && $filters['avoid_walking_customer'] == 1, function ($query) use ($filters) {
                return $query->whereNot('email', 'walking@customer.com');
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends(['searchValue' => $searchValue]);
    }

    public function getListWhereBetween(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], string $whereBetween = null, array $whereBetweenFilters = [], int|string $takeItem = null, int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null, array|object $appends = []): Collection|LengthAwarePaginator
    {
        $query = $this->user
            ->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where(function($q) use ($searchValue) {
                    $q->orWhere('email', 'like', "%$searchValue%")
                        ->orWhere('name', 'like', "%$searchValue%");
                })->where('email', '!=', 'abc@gm.com');
            })
            ->when(isset($filters['order_date']) && !empty($filters['order_date']), function ($query) use ($filters) {
                $dates = explode(' - ', $filters['order_date']);
                $orderStartDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
                $orderEndDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();

                return $query->whereHas('orders', function ($query) use ($orderStartDate, $orderEndDate) {
                    $query->whereBetween('created_at', [$orderStartDate, $orderEndDate]);
                });
            })
            ->when(isset($filters['is_active']) && in_array($filters['is_active'], ['0', '1']), function ($query) use ($filters) {
                return $query->where('is_active', $filters['is_active']);
            })
            ->when(!empty($whereBetween) && !empty($whereBetweenFilters), function ($query) use ($whereBetween, $whereBetweenFilters) {
                $query->whereBetween($whereBetween, $whereBetweenFilters);
            })
            ->when(isset($filters['sort_by']) && in_array($filters['sort_by'], ['asc', 'desc']), function ($query) use ($filters) {
                return $query->orderBy('created_at', $filters['sort_by']);
            })
            ->when(isset($filters['sort_by']) && $filters['sort_by'] == 'order_amount', function ($query) use ($filters) {
                return $query->withSum('orders', 'order_amount')->orderBy('orders_sum_order_amount', 'desc');
            })
            ->when(isset($filters['avoid_walking_customer']) && $filters['avoid_walking_customer'] == 1, function ($query) use ($filters) {
                return $query->whereNot('email', 'walking@customer.com');
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

    public function getListWhereNotIn(array $ids = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        return $this->user->whereNotIn('id', $ids)->get();
    }

    public function update(string $id, array $data): bool
    {
        return $this->user->find($id)->update($data);
    }

    public function updateWhere(array $params, array $data): bool
    {
        $this->user->where($params)->update($data);
        return true;
    }

    public function updateOrCreate(array $params, array $data): mixed
    {
        return $this->user->updateOrCreate($params, $data);
    }

    public function delete(array $params): bool
    {
        $this->user->where($params)->delete();
        return true;
    }

    public function getCustomerNameList(object $request, int|string $dataLimit = DEFAULT_DATA_LIMIT): object
    {
        $searchValue = explode(' ', $request['searchValue']);
        return $this->user->where('id', '!=', 0)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where(function ($query) use ($searchValue) {
                    foreach ($searchValue as $value) {
                        $query->orWhere('f_name', 'like', "%$value%")
                            ->orWhere('l_name', 'like', "%$value%")
                            ->orWhere('phone', 'like', "%$value%");
                    }
                });
            })
            ->limit($dataLimit)
            ->get([DB::raw('id, IF(id <> "0", CONCAT(f_name, " ", COALESCE(l_name, ""), " (", phone ,")"), CONCAT(f_name, " ", COALESCE(l_name, ""))) as text')]);
    }

    public function deleteAuthAccessTokens(string|int $id): bool
    {
        DB::table('oauth_access_tokens')->where('user_id', $id)->delete();
        return true;
    }
}
