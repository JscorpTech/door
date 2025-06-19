<?php

namespace App\Contracts\Repositories;

interface PhoneOrEmailVerificationRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array $params
     * @param array $value
     * @return mixed
     */
    public function updateOrCreate(array $params, array $value): mixed;
}
