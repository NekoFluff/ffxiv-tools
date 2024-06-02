<?php

namespace App\Providers;

use App\Http\Clients\Universalis\MockUniversalisClient;
use App\Http\Clients\Universalis\UniversalisClient;
use App\Http\Clients\Universalis\UniversalisClientInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class UniversalisClientProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [UniversalisClientInterface::class];
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('testing')) {
            $this->app->bind(UniversalisClientInterface::class, MockUniversalisClient::class);

            return;
        }

        $this->app->bind(UniversalisClientInterface::class, UniversalisClient::class);
    }
}
