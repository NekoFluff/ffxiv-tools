<?php

namespace App\Providers;

use App\Http\Clients\Universalis\UniversalisClient;
use App\Http\Clients\Universalis\UniversalisClientInterface;
use App\Http\Clients\XIV\XIVClient;
use App\Http\Clients\XIV\XIVClientInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UniversalisClientInterface::class, UniversalisClient::class);
        $this->app->bind(XIVClientInterface::class, XIVClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
