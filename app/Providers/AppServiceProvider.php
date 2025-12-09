<?php

namespace App\Providers;

use App\Repository\UserRepository;
use App\Service\LoginService;
use App\Service\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepository::class, fn() => new UserRepository());
        $this->app->singleton(UserService::class, fn($app) => new UserService($app->make(UserRepository::class)));
        $this->app->singleton(LoginService::class, fn($app) => new LoginService($app->make(UserRepository::class)));
    }

    public function boot(): void
    {
        //
    }
}
