<?php

namespace App\Providers;

use App\Repository\DashboardRepository;
use App\Repository\UserRepository;
use App\Service\DashboardService;
use App\Service\LoginService;
use App\Service\MeasurementUnitService;
use App\Service\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepository::class, fn() => new UserRepository());
        $this->app->singleton(UserService::class, fn($app) => new UserService($app->make(UserRepository::class)));
        $this->app->singleton(LoginService::class, fn($app) => new LoginService($app->make(UserRepository::class)));
        $this->app->singleton(MeasurementUnitService::class, fn($app) => new MeasurementUnitService());
        $this->app->singleton(DashboardRepository::class, fn($app) => new DashboardRepository());
        $this->app->singleton(DashboardService::class, fn($app) => new DashboardService($app->make(DashboardRepository::class)));
    }

    public function boot(): void
    {
        if($this->app->hasDebugModeEnabled()) {
            \Illuminate\Support\Facades\DB::listen(function($q) {
                \Illuminate\Support\Facades\Log::info($q->sql);
            });
        }
    }
}
