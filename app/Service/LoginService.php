<?php

namespace App\Service;

use App\Dto\LoginDto;
use App\Models\User;
use App\Repository\UserRepository;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function __construct(
        private UserRepository $userRepository
    ){}

    public function login(LoginDto $login): User|bool
    {
        $user = $this->userRepository->getByUsername($login->username);
        if($user == null) {
            return false;
        }
        if(!Hash::check($login->password, $user->password)) {
            return false;
        }
        return $user;
    }
}
