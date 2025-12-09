<?php

namespace App\Service;

use App\Dto\User\UserCreateRequest;
use App\Dto\User\UserEditRequest;
use App\Models\User;
use App\Repository\UserRepository;
use Illuminate\Http\Request;

class UserService
{

    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function getUsers($page, $perPage)
    {
        return $this->userRepository->getUsers($page, $perPage);
    }

    public function addUsers(UserCreateRequest $user)
    {
        $this->userRepository->addUser($user);
    }

    public function isCanDelete(User|null $performer, User $user): bool
    {
        if (!$performer) {
            return false;
        }
        if ($performer->role?->name === 'admin') {
            return true;
        }
        return $performer->id === $user->id;
    }

    public function isCanAdd(User $performer): bool
    {
        return $performer != null | $performer?->role?->name == 'admin';
    }

    public function isCanEdit(User|null $performer, User $user): bool
    {
        if (!$performer) {
            return false;
        }
        if ($performer->role?->name === 'admin') {
            return true;
        }
        return $performer->id === $user->id;
    }

    public function getUserById(string $id)
    {
        return $this->userRepository->getUserById($id);
    }

    public function deleteById(string $id)
    {
        return $this->userRepository->deleteById($id);
    }

    public function editById(string $id, UserEditRequest $user)
    {
        return $this->userRepository->editById($id, $user);
    }
}
