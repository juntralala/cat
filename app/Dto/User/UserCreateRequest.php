<?php

namespace App\Dto\User;

class UserCreateRequest
{
    public function __construct(
        public string $name,
        public string $username,
        public string $password,
        public string $role,
    )
    {
    }
}
