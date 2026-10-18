<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        Gate::define('pro-user', function (User $user) {
            return $user->subscribed('default');
        });

        // Limit check for not subscribed users
        Gate::define('create-transaction', function (User $user) {
            if ($user->subscribed('default')) {
                return true; // Pro users have no limit
            }
            
            $monthlyCount = $user->transactions()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            return $monthlyCount < 15;
        });
    }
}
