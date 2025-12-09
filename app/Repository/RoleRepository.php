<?php

namespace App\Repository;

use App\Models\Role;

class RoleRepository
{
    public function getAll() {
        return Role::all();
    }
}
