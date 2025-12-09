<?php

namespace App\Http\Controllers;

use App\Service\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {
    }

    public function getAll() {
        $roles = $this->roleService->getAll();
        return $roles;
    }
}
