<?php

namespace App\Service;

use App\Http\Resources\RoleResource;
use App\Repository\RoleRepository;

class RoleService
{
    public function __construct(
        private RoleRepository $roleRepository
    )
    {
    }

    public function getAll() {
        return RoleResource::collection($this->roleRepository->getAll());
    }
}
