<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;


class BlockDemoUser
{
    public function handle(Request $request, Closure $next)
    {
        if (config('app.demo_mode') && $request->user()?->email === config('app.demo_email')) {
            $request->session()->flash('error', 'This action is disabled for the demo account.');

            $target = $request->routeIs('billing.*') ? 'billing.index' : 'profile.edit';

            return Inertia::location(route($target));
        }

        return $next($request);
    }

}

