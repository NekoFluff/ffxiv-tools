<?php

namespace App\Providers;

use App\Http\Clients\XIV\MockXIVClient;
use App\Http\Clients\XIV\XIVClient;
use App\Http\Clients\XIV\XIVClientInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class XIVClientProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [XIVClientInterface::class];
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('testing')) {
            $this->app->bind(XIVClientInterface::class, MockXIVClient::class);

            return;
        }

        $this->app->bind(XIVClientInterface::class, XIVClient::class);
    }
}
