<?php

namespace App\Repository;

use App\Dto\User\UserCreateRequest;
use App\Dto\User\UserEditRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserRepository
{
    public function __construct()
    {
    }

    public function getUsers($page = 1, $perPage = 5)
    {
        return User::paginate($perPage, page: $page);
    }

    public function addUser(UserCreateRequest $user)
    {
        return User::create([
            'name' => $user->name,
            'username' => $user->username,
            'password' => $user->password,
            'role_id' => Role::where('name', $user->role)->firstOrFail()->id,
        ]);
    }

    /**
     * @return mixed|User|null
     */
    public function getUserById($id)
    {
        return User::find($id);
    }

    public function getByUsername(string $username)
    {
        return User::firstWhere('username', $username);
    }

    public function deleteById($id): bool
    {
        $target = $this->getUserById($id);
        if ($target == null) {
            return false;
        }
        return $target->delete();
    }

    public function editById($id, UserEditRequest $user)
    {
        $target = $this->getUserById($id);
        if ($target == null) {
            return null;
        }
        $target->name = $user->name;
        $target->username = $user->username;
        if (!empty($user->role)) {
            $role = Role::where('name', $user->role)->first();
            if ($role) {
                $target->role_id = $role->id;
            }
        }
        if (!empty($user->password)) {
            $target->password = $user->password;
        }
        if (!empty($user->profilePhotoPath)) {
            if ($target->profile_photo_path) {
                Storage::disk('public')->delete($target->profile_photo_path);
            }
            $target->profile_photo_path = $user->profilePhotoPath;
        }
        $target->save();
        return $target;
    }

}
