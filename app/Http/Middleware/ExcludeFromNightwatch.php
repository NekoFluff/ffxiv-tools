<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Nightwatch\Facades\Nightwatch;

class ExcludeFromNightwatch
{
    protected array $paths = [
        'up',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->is($this->paths)) {
            Nightwatch::sample(0);
        }

        return $next($request);
    }
}
