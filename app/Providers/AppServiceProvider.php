<?php

namespace App\Providers;

use App\Contracts\ProductStockChecker;
use App\Contracts\WorkOrderReadinessChecker;
use App\Services\PendingWorkOrderReadinessChecker;
use App\Services\ProductStockAvailabilityService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WorkOrderReadinessChecker::class, PendingWorkOrderReadinessChecker::class);
        $this->app->bind(ProductStockChecker::class, ProductStockAvailabilityService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
