<?php

namespace App\Repositories;

use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Models\PhoneOrEmailVerification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class PhoneOrEmailVerificationRepository implements PhoneOrEmailVerificationRepositoryInterface
{
    public function __construct(
        private readonly PhoneOrEmailVerification $phoneOrEmailVerification,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->phoneOrEmailVerification->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->phoneOrEmailVerification->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->phoneOrEmailVerification->with($relations)
                ->when(!empty($orderBy), function ($query) use ($orderBy) {
                    return $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
                });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->phoneOrEmailVerification
            ->when(isset($filters['phone_or_email']), function ($query) use($filters) {
                return $query->where(['phone_or_email' => $filters['phone_or_email']]);
            })
            ->when(isset($filters['token']), function ($query) use($filters) {
                return $query->where(['token' => $filters['token']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);

    }

    public function update(string $id, array $data): bool
    {
        return $this->phoneOrEmailVerification->find($id)->update($data);
    }

    public function updateOrCreate(array $params, array $value): mixed
    {
        return $this->phoneOrEmailVerification->updateOrCreate($params, $value);
    }

    public function delete(array $params): bool
    {
        $this->phoneOrEmailVerification->where($params)->delete();
        return true;
    }

}
