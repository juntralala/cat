<?php

namespace App\Http\Controllers\Auth;

use App\Dto\LoginDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Service\LoginService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{

    public function __construct(
        private LoginService $loginService
    ) {
    }

    public function page()
    {
        return Inertia::render("auth/Login");
    }

    public function login(LoginRequest $request)
    {
        $safe = $request->safe();
        $user = $this->loginService->login(new LoginDto($safe->username, $safe->password));
        if (!$user) {
            return back()->withErrors([
                'username' => 'Username atau Password Salah',
                'password' => 'Username atau Password Salah',
            ]);
        }
        $request->session()->regenerate();
        Auth::login($user);
        return redirect()->route('home');
    }
}
