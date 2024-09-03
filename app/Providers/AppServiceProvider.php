<?php

namespace App\Providers;

use App\Services\FFXIVService;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Telescope;
use UnitEnum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FFXIVService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Telescope::tag(function ($entry) {
            if ($entry->type === 'job') {
                return array_filter([
                    $entry->content['name'] ? 'job:'.$entry->content['name'] : '',
                    $entry->content['data']['itemID'] ? 'itemID:'.$entry->content['data']['itemID'] : '',
                    $entry->content['data']['server']['properties'] ? 'server:'.$entry->content['data']['server']['properties'] : '',
                ]);
            } elseif ($entry->type === 'log') {
                return collect($entry->content['context'])->map(function ($value, $key) {
                    if ($value instanceof UnitEnum) {
                        return $key.':'.$value->value;
                    } elseif (is_numeric($value) || is_string($value)) {
                        return $key.':'.$value;
                    } else {
                        return '';
                    }
                })->values()->filter()->toArray();
            }
        });
    }
}
