<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class BlockDemoUser
{

    public function handle(Request $request, Closure $next)
    {
        if (config('app.demo_mode') && $request->user()?->email === 'test@example.com') {
            return back()->with('error', 'This action is disabled for the demo account.');
        }

        return $next($request);
    }

}

